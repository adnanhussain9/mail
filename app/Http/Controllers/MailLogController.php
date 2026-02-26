<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MailLog;
use App\Models\MailSetting;

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
            'body' => "Hello!\n\nI am interested in applying for the {position} position at {company}.\n\nBest regards,\nAutomated System",
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
}
