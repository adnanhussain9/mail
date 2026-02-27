<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\MailSetting;
use App\Models\MailLog;
use Revolution\Google\Sheets\Facades\Sheets;

class HuntJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:hunt';
    protected $description = 'Automatically finds jobs from free public sources (RSS, Search) based on your keywords.';

    protected $sheetEntries = [];

    public function handle()
    {
        $settings = MailSetting::first();
        if (!$settings || !$settings->is_auto_hunting || !$settings->search_keywords) {
            $this->info('Job hunting is disabled or keywords are missing.');
            return;
        }

        $keywords = explode(',', $settings->search_keywords);
        $keywords = array_map('trim', $keywords);

        $this->info('Starting job hunt for: ' . implode(', ', $keywords));

        $this->loadSheetEntries();

        // Static sources to check
        $staticSources = [
            'https://weworkremotely.com/categories/remote-programming-jobs.rss',
            'https://larajobs.com/feed',
            'https://remoteok.com/remote-jobs.rss',
            'https://workingnomads.com/jobs/feed',
            'https://himalayas.app/jobs/feed',
            'https://authenticjobs.com/feed',
        ];

        // Dynamic sources based on keywords (using Google News RSS to proxy Google/Indeed searches)
        $dynamicSources = [];
        foreach ($keywords as $keyword) {
            $encoded = urlencode($keyword);
            // Search Google News for recent job mentions
            $dynamicSources[] = "https://news.google.com/rss/search?q={$encoded}+jobs+after:1d";
            // Proxy search for Indeed via Google News
            $dynamicSources[] = "https://news.google.com/rss/search?q=site:indeed.com+{$encoded}+after:2d";
            // LinkedIn search proxy
            $dynamicSources[] = "https://news.google.com/rss/search?q=site:linkedin.com/jobs+{$encoded}+after:2d";
            // LinkedIn recruiter posts search (targets direct contact emails)
            $dynamicSources[] = "https://news.google.com/rss/search?q=site:linkedin.com/posts+{$encoded}+hiring+email+after:7d";
            // Hacker News 'Who is hiring' search
            $dynamicSources[] = "https://hnrss.github.io/search?q=%22Who+is+hiring%22+{$encoded}";
        }

        $allSources = array_merge($staticSources, $dynamicSources);

        foreach ($allSources as $source) {
            $this->info("Checking source: $source");
            try {
                $this->huntRSS($source, $keywords);
            } catch (\Exception $e) {
                $this->error("Error checking $source: " . $e->getMessage());
            }
        }

        $this->info('Hunting complete.');
    }



    protected function huntRSS($url, $keywords)
    {
        $response = Http::withoutVerifying()->timeout(15)->get($url);
        if (!$response->successful())
            return;

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body());
        if (!$xml)
            return;

        // Handle both standard RSS and Atom feeds
        if (isset($xml->channel->item)) {
            $items = $xml->channel->item;
        } elseif (isset($xml->entry)) {
            $items = $xml->entry;
        } else {
            $items = [];
        }

        foreach ($items as $item) {
            // Normalize Title and Description (Atom uses different tags than RSS)
            $title = (string) ($item->title ?? '');

            // Check common description/content tags
            $description = '';
            if (isset($item->description))
                $description = (string) $item->description;
            elseif (isset($item->content))
                $description = (string) $item->content;
            elseif (isset($item->summary))
                $description = (string) $item->summary;

            // Link normalization
            $link = '';
            if (isset($item->link)) {
                $linkAttribs = $item->link->attributes();
                $link = isset($linkAttribs['href']) ? (string) $linkAttribs['href'] : (string) $item->link;
            }
            if (!$link && isset($item->id))
                $link = (string) $item->id;

            $text = strip_tags($title . ' ' . $description);

            if ($this->matchesKeywords($text, $keywords)) {
                $this->info("Matched keyword in RSS/Atom: $title");
                $this->processFoundContent($title . ' ' . $description, $title, $link);
            }
        }
    }

    protected function matchesKeywords($text, $keywords)
    {
        foreach ($keywords as $keyword) {
            if (mb_stripos($text, $keyword) !== false)
                return true;
        }
        return false;
    }

    protected function processFoundContent($text, $title, $url = null)
    {
        // 1. Clean up common obfuscations like [at], (at), {at}
        $normalizedText = preg_replace(['/\[at\]/i', '/\(at\)/i', '/\{at\}/i', '/\s+at\s+/i'], '@', $text);
        $normalizedText = preg_replace(['/\[dot\]/i', '/\(dot\)/i', '/\{dot\}/i', '/\s+dot\s+/i'], '.', $normalizedText);

        // 2. Extract emails
        preg_match_all('/[a-z0-9\._%+-]+@[a-z0-9\.-]+\.[a-z]{2,}/i', $normalizedText, $matches);
        $emails = array_unique($matches[0] ?? []);

        // 3. If no email found in snippet AND we have a URL, try to fetch the URL content
        if (empty($emails) && $url && !str_contains($url, 'mailto:')) {
            $this->info("No email in snippet, checking URL: $url");
            try {
                $response = Http::withoutVerifying()
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                    ->timeout(10)
                    ->get($url);

                if ($response->successful()) {
                    $pageContent = $response->body();
                    // Clean up obfuscation in full page too
                    $pageContent = preg_replace(['/\[at\]/i', '/\(at\)/i', '/\{at\}/i'], '@', $pageContent);
                    $pageContent = preg_replace(['/\[dot\]/i', '/\(dot\)/i', '/\{dot\}/i'], '.', $pageContent);

                    preg_match_all('/[a-z0-9\._%+-]+@[a-z0-9\.-]+\.[a-z]{2,}/i', $pageContent, $urlMatches);
                    $emails = array_unique($urlMatches[0] ?? []);
                }
            } catch (\Exception $e) {
                $this->warn("Could not fetch $url: " . $e->getMessage());
            }
        }

        // 4. Check specifically for mailto: links in text if still empty
        if (empty($emails)) {
            preg_match_all('/mailto:([a-z0-9\._%+-]+@[a-z0-9\.-]+\.[a-z]{2,})/i', $text, $mailtoMatches);
            $emails = array_unique($mailtoMatches[1] ?? []);
        }

        if (empty($emails)) {
            $this->warn("No emails found for: $title (URL: $url)");
            return;
        }

        foreach ($emails as $email) {
            // Clean common false positives
            if (str_contains($email, 'example.com') || str_contains($email, 'domain.com')) {
                $this->warn("Skipping common false positive email: $email for $title");
                continue;
            }

            // Check if already in sheet or database
            $key = strtolower(trim($email) . '|' . trim($title));
            $inSheet = isset($this->sheetEntries[$key]);

            // Checking database as well for global uniqueness (already processed/sent)
            $inDatabase = MailLog::where('email', $email)
                ->where('position_name', $title)
                ->exists();

            if (!$inSheet && !$inDatabase) {
                $this->info("New job found! Adding to sheet: $email");

                try {
                    $spreadsheetId = config('services.google.sheet_id');
                    $sheetName = config('services.google.sheet_name', 'Sheet1');

                    if ($spreadsheetId) {
                        // Formatting: [Company, Email, Position, Job Link]
                        Sheets::spreadsheet($spreadsheetId)
                            ->sheet($sheetName)
                            ->append([['Auto Found', $email, $title, $url ?? 'N/A']]);

                        // Add to local cache to avoid duplicates in same run
                        $this->sheetEntries[$key] = true;
                        $this->info("Successfully added $email to sheet.");
                    } else {
                        $this->error("GOOGLE_SHEET_ID missing.");
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to add to sheet: " . $e->getMessage());
                }
            } else {
                $this->warn("Skipping $email - already exists in sheet or logs.");
            }
        }
    }

    protected function loadSheetEntries()
    {
        $spreadsheetId = config('services.google.sheet_id');
        $sheetName = config('services.google.sheet_name', 'Sheet1');

        if (!$spreadsheetId)
            return;

        try {
            $rows = Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->get();

            foreach ($rows as $row) {
                // Formatting: [Company, Email, Position, Job Link]
                $email = isset($row[1]) ? trim($row[1]) : '';
                $position = isset($row[2]) ? trim($row[2]) : '';
                if ($email) {
                    $key = strtolower($email . '|' . $position);
                    $this->sheetEntries[$key] = true;
                }
            }
            $this->info("Loaded " . count($this->sheetEntries) . " existing entries from sheet.");
        } catch (\Exception $e) {
            $this->warn("Could not load existing sheet entries: " . $e->getMessage());
        }
    }
}
