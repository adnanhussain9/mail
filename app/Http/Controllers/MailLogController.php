<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MailLog;
use App\Models\MailSetting;

use Illuminate\Support\Facades\Mail;
use App\Mail\DynamicJobMail;
use Revolution\Google\Sheets\Facades\Sheets;
use Inertia\Inertia;
use Inertia\Response;

class MailLogController extends Controller
{
    public function index(): Response
    {
        $logs = MailLog::latest()->paginate(20)->through(fn($log) => [
            'id' => $log->id,
            'email' => $log->email,
            'company_name' => $log->company_name,
            'position_name' => $log->position_name,
            'sent_at' => $log->sent_at->diffForHumans(),
        ]);

        $settings = MailSetting::first() ?? new MailSetting([
            'subject' => 'Application for {position} at {company}',
            'body' => "Hello!\n\nI am interested in applying for the {position} position at {company}.\n\nBest regards,\nSyed Adnan Hussain\nWeb Developer",
        ]);
        return Inertia::render('Dashboard', [
            'logs' => $logs,
            'settings' => $settings,
            'status' => session('success'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf|max:5120', // Max 5MB PDF
        ]);

        $settings = MailSetting::first() ?? new MailSetting();
        $data = $request->only(['subject', 'body', 'search_keywords']);
        $data['is_auto_hunting'] = $request->has('is_auto_hunting');

        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($settings->attachment_path && Storage::exists($settings->attachment_path)) {
                Storage::delete($settings->attachment_path);
            }

            $path = $request->file('attachment')->store('attachments');
            $data['attachment_path'] = $path;
        }

        $settings->fill($data)->save();

        return back()->with('success', 'Settings updated successfully!');
    }

    public function processSheet()
    {
        $spreadsheetId = config('services.google.sheet_id');
        $sheetName = config('services.google.sheet_name', 'Sheet1');

        if (!$spreadsheetId) {
            return back()->with('error', 'GOOGLE_SHEET_ID is not set in .env');
        }

        try {
            $rows = Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->get();

            if ($rows->isEmpty()) {
                return back()->with('success', 'Sheet is empty.');
            }

            // Assume first row is header: Email, Company, Position
            $rows->pull(0);
            $processedCount = 0;

            foreach ($rows as $row) {
                $company = isset($row[0]) ? trim($row[0]) : null;
                $email = isset($row[1]) ? trim($row[1]) : null;
                $position = isset($row[2]) ? trim($row[2]) : null;

                if (!$email || !$company || !$position) {
                    continue;
                }

                // Check if already sent (database check)
                $exists = MailLog::where([
                    'email' => $email,
                    'company_name' => $company,
                    'position_name' => $position,
                ])->exists();

                if (!$exists) {
                    try {
                        Mail::to($email)->send(new DynamicJobMail($email, $company, $position));

                        MailLog::create([
                            'email' => $email,
                            'company_name' => $company,
                            'position_name' => $position,
                            'sent_at' => now(),
                        ]);

                        $processedCount++;
                    } catch (\Exception $e) {
                        // Log error but continue
                    }
                }
            }

            return back()->with('success', "Processed sheets! Sent {$processedCount} new emails.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error accessing Google Sheets: ' . $e->getMessage());
        }
    }

    public function addToSheet(Request $request)
    {
        $request->validate([
            'company' => 'required|string',
            'email' => 'required|email',
            'position' => 'required|string',
        ]);

        $spreadsheetId = config('services.google.sheet_id');
        $sheetName = config('services.google.sheet_name', 'Sheet1');

        if (!$spreadsheetId) {
            return back()->with('error', 'GOOGLE_SHEET_ID is not set in .env');
        }

        try {
            Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->append([[$request->company, $request->email, $request->position]]);

            return back()->with('success', 'Entry added to sheet successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error adding to sheet: ' . $e->getMessage());
        }
    }
}
