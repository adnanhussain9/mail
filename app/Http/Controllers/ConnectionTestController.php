<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\MailSetting;
use Revolution\Google\Sheets\Facades\Sheets;

class ConnectionTestController extends Controller
{
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
                Config::set('mail.from.name', $settings->from_name ?? auth()->user()->name);
            }
        }
    }

    public function testSmtp(Request $request)
    {
        $settings = MailSetting::where('user_id', auth()->id())->first() ?? new MailSetting();
        
        // Fill from request if provided (e.g. testing during onboarding)
        if ($request->has('smtp_host')) {
            $settings->smtp_host = $request->input('smtp_host');
            $settings->smtp_port = $request->input('smtp_port', '587');
            $settings->smtp_username = $request->input('smtp_username');
            if ($request->filled('smtp_password')) {
                $settings->smtp_password = $request->input('smtp_password');
            }
            $settings->smtp_encryption = $request->input('smtp_encryption', 'tls');
            $settings->from_address = $request->input('from_address');
            $settings->from_name = $request->input('from_name');
        }

        if (!$settings->smtp_host || !$settings->smtp_username || !$settings->smtp_password) {
            return back()->with('error', 'Please fill out Host, Username, and Password to test SMTP.');
        }

        try {
            $this->setDynamicSmtpConfig($settings);

            Mail::raw('This is a test email to verify your SMTP configuration is working correctly.', function ($message) {
                $message->to(auth()->user()->email)
                        ->subject('SMTP Connection Test Successful');
            });

            return back()->with('success', 'Test email sent successfully! Please check your inbox.');
        } catch (\Exception $e) {
            return back()->with('error', 'SMTP Connection failed: ' . $e->getMessage());
        }
    }

    public function testSheet(Request $request)
    {
        $sheetId = trim((string)$request->input('google_sheet_id'));
        if (!$sheetId) {
            $settings = MailSetting::where('user_id', auth()->id())->first();
            $sheetId = trim((string)$settings?->google_sheet_id);
        }

        if (!$sheetId) {
            return back()->with('error', 'Please enter a Google Sheet ID to test.');
        }

        // If user pasted full Google Sheets URL, extract ID
        if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $sheetId, $matches)) {
            $sheetId = $matches[1];
        }

        try {
            $sheet = Sheets::spreadsheet($sheetId)->sheetList();
            
            return back()->with('success', 'Successfully connected to Google Sheet! Found sheets: ' . implode(', ', $sheet));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Sheets connection test failed: ' . $e->getMessage(), [
                'exception' => $e,
                'sheet_id' => $sheetId,
            ]);
            return back()->with('error', 'Google Sheets connection failed: ' . $e->getMessage());
        }
    }

}
