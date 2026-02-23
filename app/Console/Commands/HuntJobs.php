<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\MailSetting;
use App\Models\MailLog;
use App\Mail\DynamicJobMail;

class HuntJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:hunt';
    protected $description = 'Automatically finds jobs from free public sources (Reddit, RSS) based on your keywords.';

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

        // Sources to check
        $sources = [
            'https://www.reddit.com/r/laraveljobs/new.json',
            'https://www.reddit.com/r/phpjobs/new.json',
            'https://www.reddit.com/r/forhire/new.json',
            'https://weworkremotely.com/categories/remote-programming-jobs.rss',
        ];

        foreach ($sources as $source) {
            $this->info("Checking source: $source");
            try {
                if (str_contains($source, 'reddit.json')) {
                    $this->huntReddit($source, $keywords);
                } else {
                    $this->huntRSS($source, $keywords);
                }
            } catch (\Exception $e) {
                $this->error("Error checking $source: " . $e->getMessage());
            }
        }

        $this->info('Hunting complete.');
    }

    protected function huntReddit($url, $keywords)
    {
        // Reddit requires a custom User-Agent to avoid 429/403 errors
        $response = Http::withHeaders([
            'User-Agent' => 'JobHunterBot/1.0 (Laravel Application)'
        ])->get($url);

        if (!$response->successful()) {
            $this->error("Reddit fetch failed: " . $response->status());
            return;
        }

        $posts = $response->json('data.children') ?? [];
        foreach ($posts as $post) {
            $data = $post['data'];
            $text = ($data['title'] ?? '') . ' ' . ($data['selftext'] ?? '');

            if ($this->matchesKeywords($text, $keywords)) {
                $this->processFoundContent($text, $data['title'] ?? 'Job Posting');
            }
        }
    }

    protected function huntRSS($url, $keywords)
    {
        // Turn off internal errors to handle malformed RSS gracefully
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($url);
        if (!$xml)
            return;

        foreach ($xml->channel->item as $item) {
            $text = (string) $item->title . ' ' . (string) $item->description;
            if ($this->matchesKeywords($text, $keywords)) {
                $this->processFoundContent($text, (string) $item->title);
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

    protected function processFoundContent($text, $title)
    {
        // Extract email using regex
        preg_match_all('/[a-z0-9\._%+-]+@[a-z0-9\.-]+\.[a-z]{2,}/i', $text, $matches);
        $emails = array_unique($matches[0] ?? []);

        foreach ($emails as $email) {
            // Clean common false positives
            if (str_contains($email, 'example.com') || str_contains($email, 'domain.com'))
                continue;

            $exists = MailLog::where('email', $email)->exists();
            if (!$exists) {
                $this->info("New job found! Email: $email");

                // Add to the processing queue (using MailLog as our record)
                // We'll use the existing ProcessSheetMails or a new direct send
                try {
                    Mail::to($email)->send(
                        new DynamicJobMail($email, 'Auto-Identified', $title)
                    );

                    MailLog::create([
                        'email' => $email,
                        'company_name' => 'Auto Found',
                        'position_name' => $title,
                        'sent_at' => now(),
                    ]);
                    $this->info("Mail sent to $email");
                } catch (\Exception $e) {
                    $this->error("Failed to send: " . $e->getMessage());
                }
            }
        }
    }
}
