<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Automation Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg: #f9fafb;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-card .label {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 24px;
            font-weight: 700;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: #fcfcfd;
            padding: 12px 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px 20px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
            background: #d1fae5;
            color: #065f46;
        }

        .empty-state {
            padding: 60px;
            text-align: center;
            color: var(--text-muted);
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn:hover {
            background: var(--primary-hover);
        }

        /* Basic Pagination Styles */
        nav {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        nav ul {
            display: flex;
            list-style: none;
            padding: 0;
        }

        nav li {
            margin: 0 4px;
        }

        nav a,
        nav span {
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            text-decoration: none;
            color: var(--primary);
            font-size: 14px;
        }

        nav .active span {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                text-align: left;
            }

            header .btn {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 20px;
            }

            .card {
                padding: 16px !important;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table th,
            table td {
                padding: 12px 10px;
                font-size: 13px;
                white-space: nowrap;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div>
                <h1>Mail Automation</h1>
                <p style="color: var(--text-muted); margin: 4px 0 0 0;">Tracking processed entries from your sheet.</p>
            </div>
            <div>
                <a href="#" class="btn" onclick="location.reload()">Refresh Dashboard</a>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total Sent</div>
                <div class="value">{{ $logs->total() }}</div>
            </div>
            <div class="stat-card">
                <div class="label">Frequency</div>
                <div class="value">1 Minute</div>
            </div>
            <div class="stat-card">
                <div class="label">Status</div>
                <div class="value" style="color: #10b981;">Active</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 32px; padding: 24px;">
            <h2 style="font-size: 18px; margin-bottom: 16px;">Customize Email Content</h2>
            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;">
                Use placeholders: <code>{email}</code>, <code>{company}</code>, <code>{position}</code>
            </p>

            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Email
                        Subject</label>
                    <input type="text" name="subject" value="{{ $settings->subject }}"
                        style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Email Body
                        (HTML supported)</label>
                    <textarea name="body" rows="6"
                        style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-family: inherit;">{{ $settings->body }}</textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Attachment
                        (PDF only)</label>
                    <input type="file" name="attachment" accept=".pdf"
                        style="width: 100%; padding: 8px; border: 1px dashed var(--border); border-radius: 6px; box-sizing: border-box;">
                    @if($settings->attachment_path)
                        <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">
                            Current file: <strong>{{ basename($settings->attachment_path) }}</strong>
                        </p>
                    @endif
                </div>

                <div
                    style="margin-bottom: 32px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; margin-bottom: 12px; display: flex; align-items: center;">
                        <span style="margin-right: 8px;">🎯</span> AI Job Hunter (Free)
                    </h3>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 4px;">Search
                            Keywords (e.g. Laravel Developer, Remote PHP)</label>
                        <input type="text" name="search_keywords" value="{{ $settings->search_keywords }}"
                            placeholder="Keywords..."
                            style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; font-size: 14px;">
                    </div>
                    <label style="display: flex; align-items: center; font-size: 14px; cursor: pointer;">
                        <input type="checkbox" name="is_auto_hunting" {{ $settings->is_auto_hunting ? 'checked' : '' }}
                            style="margin-right: 8px; width: 16px; height: 16px;">
                        Enable Automatic Job Hunting (Reddit/RSS)
                    </label>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">
                        When enabled, the app will periodically scan niche job boards for matches and send your mail.
                    </p>
                </div>

                <button type="submit" class="btn"
                    style="border: none; cursor: pointer; width: 100%; padding-top: 12px; padding-bottom: 12px;">Save
                    All
                    Configuration</button>
            </form>

            @if(session('success'))
                <div
                    style="margin-top: 16px; padding: 10px; background: #ecfdf5; color: #065f46; border-radius: 6px; font-size: 14px;">
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <div class="card">
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