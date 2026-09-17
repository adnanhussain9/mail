<?php

namespace App\Http\Controllers;

use App\Models\MailSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function index()
    {
        $settings = MailSetting::where('user_id', auth()->id())->first() ?? new MailSetting();
        return Inertia::render('Onboarding/Wizard', [
            'settings' => $settings,
        ]);
    }

    public function complete(Request $request)
    {
        $data = $request->validate([
            'google_sheet_id' => 'required|string',
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|string',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_encryption' => 'required|string',
            'from_address' => 'required|string',
            'from_name' => 'required|string',
        ]);

        $settings = MailSetting::firstOrNew(['user_id' => auth()->id()]);
        $settings->fill($data);
        
        if (empty($settings->subject)) {
            $settings->subject = 'Application for {position} at {company}';
        }
        if (empty($settings->body)) {
            $settings->body = "Hello!\n\nI am interested in applying for the {position} position at {company}.\n\nBest regards,\n" . auth()->user()->name;
        }

        $settings->save();

        $user = auth()->user();
        $user->is_onboarded = true;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Onboarding completed successfully! Welcome to your dashboard.');
    }
}
