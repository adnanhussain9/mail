<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailLogController extends Controller
{
    public function index()
    {
        $logs = \App\Models\MailLog::latest()->paginate(20);
        return view('dashboard', compact('logs'));
    }
}
