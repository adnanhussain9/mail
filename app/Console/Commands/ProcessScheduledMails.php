<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MailSetting;
use App\Models\MailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Mail\DynamicJobMail;
use Revolution\Google\Sheets\Facades\Sheets;

class ProcessScheduledMails extends Command
{
    protected $signature = 'mail:process-scheduled';
    protected $description = 'Process mail sheets for all auto-hunting users';

    private function setDynamicSmtpConfig(MailSetting $settings)
    {
        if ($settings->smtp_host) {
            Config::set('mail.mailers.smtp.host', $settings->smtp_host);
            Config::set('mail.mailers.smtp.port', $settings->smtp_port);
            Config::set('mail.mailers.smtp.encryption', $settings->smtp_encryption);
            Config::set('mail.mailers.smtp.username', $settings->smtp_username);
            Config::set('mail.mailers.smtp.password', $settings->smtp_password);
            
            if ($settings->from_address) {
                Config::set('mail.from.address', $settings->from_address);
                Config::set('mail.from.name', $settings->from_name ?? $settings->user->name);
            }
        }
    }

    public function handle()
    {
        $this->info('Starting scheduled mail processor...');

        $users = User::whereHas('mailSettings', function ($query) {
            $query->where('is_auto_hunting', true)
                  ->whereNotNull('google_sheet_id');
        })->get();

        foreach ($users as $user) {
            if ($user->emails_sent_today >= $user->daily_email_limit) {
                $this->warn("User {$user->email} has reached daily limit. Skipping.");
                continue;
            }

            $settings = $user->mailSettings;
            if (!$settings) continue;

            $this->info("Processing sheet for user {$user->email}...");

            try {
                $rows = Sheets::spreadsheet($settings->google_sheet_id)
                    ->sheet(config('services.google.sheet_name', 'Sheet1'))
                    ->get();

                if ($rows->isEmpty()) {
                    continue;
                }

                $rows->pull(0); // Remove headers

                $this->setDynamicSmtpConfig($settings);
                $processedCount = 0;

                foreach ($rows as $row) {
                    if ($user->emails_sent_today >= $user->daily_email_limit) {
                        break;
                    }

                    $company = isset($row[0]) ? trim($row[0]) : null;
                    $email = isset($row[1]) ? trim($row[1]) : null;
                    $position = isset($row[2]) ? trim($row[2]) : null;

                    if (!$email || !$company || !$position) {
                        continue;
                    }

                    $exists = MailLog::where([
                        'user_id' => $user->id,
                        'email' => $email,
                        'company_name' => $company,
                        'position_name' => $position,
                    ])->exists();

                    if (!$exists) {
                        try {
                            Mail::to($email)->send(new DynamicJobMail($email, $company, $position, $settings));

                            MailLog::create([
                                'user_id' => $user->id,
                                'email' => $email,
                                'company_name' => $company,
                                'position_name' => $position,
                                'sent_at' => now(),
                            ]);

                            $user->increment('emails_sent_today');
                            $processedCount++;
                        } catch (\Exception $e) {
                            $this->error("Failed sending to {$email}: " . $e->getMessage());
                        }
                    }
                }
                
                $this->info("Sent {$processedCount} emails for {$user->email}.");
            } catch (\Exception $e) {
                $this->error("Error accessing sheet for {$user->email}: " . $e->getMessage());
            }
        }

        $this->info('Scheduled mail processor finished.');
    }
}
