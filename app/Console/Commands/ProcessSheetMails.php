<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\MailLog;
use App\Models\MailSetting;
use App\Mail\DynamicJobMail;
use Revolution\Google\Sheets\Facades\Sheets;

class ProcessSheetMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:process-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read Google Sheet and send emails to new entries every minute.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $spreadsheetId = config('services.google.sheet_id');
        $sheetName = config('services.google.sheet_name', 'Sheet1');

        if (!$spreadsheetId) {
            $this->error('GOOGLE_SHEET_ID is not set in .env');
            return;
        }

        $this->info("Fetching data from sheet: {$sheetName}...");

        try {
            $rows = Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->get();

            if ($rows->isEmpty()) {
                $this->info('Sheet is empty.');
                return;
            }

            // Assume first row is header: Email, Company, Position
            $header = $rows->pull(0);
            $processedInThisBatch = collect();

            foreach ($rows as $row) {
                $company = isset($row[0]) ? trim($row[0]) : null;
                $email = isset($row[1]) ? trim($row[1]) : null;
                $position = isset($row[2]) ? trim($row[2]) : null;

                if (!$email || !$company || !$position) {
                    continue;
                }

                $entryKey = "{$email}|{$company}|{$position}";
                if ($processedInThisBatch->contains($entryKey)) {
                    continue;
                }

                // Check if already sent (database check)
                $exists = MailLog::where([
                    'email' => $email,
                    'company_name' => $company,
                    'position_name' => $position,
                ])->exists();

                if (!$exists) {
                    $this->info("Sending mail to {$email} for {$position} at {$company}...");

                    try {
                        Mail::to($email)->send(new DynamicJobMail($email, $company, $position));

                        MailLog::create([
                            'email' => $email,
                            'company_name' => $company,
                            'position_name' => $position,
                            'sent_at' => now(),
                        ]);

                        $processedInThisBatch->push($entryKey);
                        $this->info("Mail sent successfully.");
                    } catch (\Exception $e) {
                        $this->error("Failed to send mail to {$email}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Error accessing Google Sheets: " . $e->getMessage());
        }

        $this->info('Processing complete.');
    }
}
