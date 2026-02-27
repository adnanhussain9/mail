<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'body' => "Hello!\n\nI am interested in applying for the {position} position at {company}.\n\nBest regards,\n" . config('app.name') . "\nWeb Developer",
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
                $link = isset($row[3]) ? trim($row[3]) : null;

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
            'link' => 'nullable|string',
        ]);

        $spreadsheetId = config('services.google.sheet_id');
        $sheetName = config('services.google.sheet_name', 'Sheet1');

        if (!$spreadsheetId) {
            return back()->with('error', 'GOOGLE_SHEET_ID is not set in .env');
        }

        try {
            Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->append([[$request->company, $request->email, $request->position, $request->link ?? 'N/A']]);

            return back()->with('success', 'Entry added to sheet successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error adding to sheet: ' . $e->getMessage());
        }
    }

    public function viewSheet(): Response
    {
        $spreadsheetId = config('services.google.sheet_id');
        $sheetName = config('services.google.sheet_name', 'Sheet1');

        if (!$spreadsheetId) {
            abort(500, 'GOOGLE_SHEET_ID is not set in .env');
        }

        try {
            $rows = Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheetName)
                ->get();

            return Inertia::render('ViewSheet', [
                'rows' => $rows,
                'sheetName' => $sheetName,
            ]);
        } catch (\Exception $e) {
            abort(500, 'Error accessing Google Sheets: ' . $e->getMessage());
        }
    }
    public function generateEmailBody(Request $request)
    {
        $request->validate([
            'jd' => 'required|string',
        ]);

        $apiKey = config('services.google.gemini_api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'GEMINI_API_KEY is not configured.'], 400);
        }

        try {
            $prompt = "You are a professional software developer assistant. Generate a highly personalized and professional application email body based on the following Job Description (JD). 
            The email should be sent from " . config('app.name') . ".
            Use the following placeholders in the email: {company} for the company name and {position} for the job title. 
            Ensure the tone is professional, confident, and enthusiastic. 
            Do not include any other text beside the email body itself.
            Job Description:
            {$request->jd}";

            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                // Clean up any extra markdown or formatting if AI adds it
                $generatedText = trim($generatedText);

                return response()->json(['body' => $generatedText]);
            }

            return response()->json(['error' => 'AI Generation failed: ' . $response->body()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'AI Generation error: ' . $e->getMessage()], 500);
        }
    }
}
