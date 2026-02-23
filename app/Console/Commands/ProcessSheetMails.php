<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
            $rows = \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->get();

            if ($rows->isEmpty()) {
                $this->info('Sheet is empty.');
                return;
            }

            // Assume first row is header: Email, Company, Position
            $header = $rows->pull(0);

            foreach ($rows as $row) {
                $email = $row[0] ?? null;
                $company = $row[1] ?? null;
                $position = $row[2] ?? null;

                if (!$email || !$company || !$position) {
                    continue;
                }

                // Check if already sent
                $exists = \App\Models\MailLog::where([
                    'email' => $email,
                    'company_name' => $company,
                    'position_name' => $position,
                ])->exists();

                if (!$exists) {
                    $this->info("Sending mail to {$email} for {$position} at {$company}...");

                    try {
                        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\DynamicJobMail($company, $position));

                        \App\Models\MailLog::create([
                            'email' => $email,
                            'company_name' => $company,
                            'position_name' => $position,
                            'sent_at' => now(),
                        ]);

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
