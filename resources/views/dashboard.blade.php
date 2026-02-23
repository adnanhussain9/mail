<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Automation Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --bg: #f8fafc;
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --glow: 0 10px 15px -3px rgba(99, 102, 241, 0.1), 0 4px 6px -2px rgba(99, 102, 241, 0.05);
        }

        * {
            box-sizing: border-box;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.05) 0px, transparent 50%);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .container {
            max-width: 1100px;
            margin: 60px auto;
            padding: 0 24px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 48px;
        }

        h1 {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin: 0;
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: var(--glow);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
            opacity: 0.5;
        }

        .stat-card .label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--glow);
            backdrop-filter: blur(10px);
            margin-bottom: 32px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-header {
            padding: 24px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-header h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .form-content {
            padding: 32px;
        }

        .input-group {
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .input-group input[type="text"],
        .input-group textarea,
        .input-group input[type="file"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            background: #fcfdfe;
            transition: all 0.2s;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: #fff;
        }

        .hunting-panel {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border);
            margin-bottom: 32px;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
            filter: brightness(1.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background: #f8fafc;
            padding: 16px 24px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.05em;
            text-align: left;
        }

        td {
            padding: 20px 24px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        tr:hover td {
            background: #fcfdfe;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            background: #ecfdf5;
            color: #065f46;
        }

        .placeholder-hint {
            display: inline-flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .placeholder-hint code {
            background: #eef2ff;
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            padding: 80px 24px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 16px;
        }

        /* Pagination Styling */
        nav {
            display: flex;
            justify-content: center;
            padding: 24px;
        }

        nav ul {
            display: flex;
            list-style: none;
            padding: 0;
            gap: 8px;
        }

        nav a,
        nav span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 14px;
            font-weight: 600;
            background: white;
        }

        nav .active span {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.2);
        }

        nav a:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #fcfdfe;
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .container {
                margin: 30px auto;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 24px;
            }

            header .btn {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .form-content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div>
                <h1>Mail Automated</h1>
                <p style="color: var(--text-muted); margin: 8px 0 0 0; font-size: 16px;">Intelligent job application &
                    monitoring system.</p>
            </div>
            <div>
                <a href="#" class="btn" onclick="location.reload()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2v6h-6" />
                        <path d="M3 12a9 9 0 0 1 15-6.7L21 8" />
                        <path d="M3 22v-6h6" />
                        <path d="M21 12a9 9 0 0 1-15 6.7L3 16" />
                    </svg>
                    Refresh Dashboard
                </a>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total Processed</div>
                <div class="value">
                    <span style="color: var(--primary)">{{ $logs->total() }}</span>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h9" />
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        <path d="M19 16v6" />
                        <path d="M16 19h6" />
                    </svg>
                </div>
            </div>
            <div class="stat-card">
                <div class="label">Check Interval</div>
                <div class="value">
                    <span>1 min</span>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
            </div>
            <div class="stat-card">
                <div class="label">System Status</div>
                <div class="value">
                    <span style="color: var(--success)">Active</span>
                    <div
                        style="width: 12px; height: 12px; background: var(--success); border-radius: 50%; box-shadow: 0 0 12px var(--success)">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <h2>Configuration & Automation</h2>
            </div>

            <div class="form-content">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="placeholder-hint">
                        <span>Dynamic Fields:</span>
                        <code>{email}</code>
                        <code>{company}</code>
                        <code>{position}</code>
                    </div>

                    <div class="input-group">
                        <label>Mail Subject</label>
                        <input type="text" name="subject" value="{{ $settings->subject }}"
                            placeholder="e.g. Applying for {position} at {company}">
                    </div>

                    <div class="input-group">
                        <label>Email Body Content</label>
                        <textarea name="body" rows="8"
                            placeholder="Write your professional message here...">{{ $settings->body }}</textarea>
                    </div>

                    <div class="input-group">
                        <label>Resume / CV (PDF)</label>
                        <div style="position: relative;">
                            <input type="file" name="attachment" accept=".pdf">
                        </div>
                        @if($settings->attachment_path)
                            <div
                                style="margin-top: 12px; display: flex; align-items: center; gap: 8px; color: var(--success); font-size: 13px; font-weight: 600;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                Linked: {{ basename($settings->attachment_path) }}
                            </div>
                        @endif
                    </div>

                    <div class="hunting-panel">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                            <div
                                style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                🎯</div>
                            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">AI Job Hunter</h3>
                        </div>

                        <div class="input-group">
                            <label>Search Interests (Keywords)</label>
                            <input type="text" name="search_keywords" value="{{ $settings->search_keywords }}"
                                placeholder="e.g. Laravel Developer, Remote PHP, Web Designer">
                        </div>

                        <label
                            style="display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="is_auto_hunting" {{ $settings->is_auto_hunting ? 'checked' : '' }}
                                style="width: 20px; height: 20px; border-radius: 6px; border: 2px solid var(--border); cursor: pointer;">
                            <span style="font-size: 14px; font-weight: 600;">Enable Autonomous Job Hunting</span>
                        </label>
                        <p style="font-size: 13px; color: var(--text-muted); margin: 12px 0 0 32px;">
                            The system will automatically scan Reddit and RSS feeds for matching posts and send your
                            application.
                        </p>
                    </div>

                    <button type="submit" class="btn" style="width: 100%; height: 56px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Deploy Configuration
                    </button>

                    @if(session('success'))
                        <div
                            style="margin-top: 24px; padding: 16px; background: #ecfdf5; color: #065f46; border-radius: 12px; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card">
            <div class="section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    <path d="M8 9h8" />
                    <path d="M8 13h6" />
                </svg>
                <h2>Transmission Logs</h2>
            </div>
            @if($logs->isEmpty())
                <div class="empty-state">
                    <p>No mails sent yet. Start the scheduler to begin.</p>
                    <code
                        style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">php artisan schedule:work</code>
                </div>
            @else
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Recipient</th>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Sent At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td><strong>{{ $log->email }}</strong></td>
                                    <td>{{ $log->company_name }}</td>
                                    <td>{{ $log->position_name }}</td>
                                    <td style="color: var(--text-muted);">{{ $log->sent_at->diffForHumans() }}</td>
                                    <td><span class="status-badge">Sent</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding: 20px;">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</body>

</html>