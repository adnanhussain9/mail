# Mail Automation from Google Sheets

A Laravel-based tool to automatically send emails to recruiters/companies listed in a Google Sheet.

## Features

- Reads Google Sheet every 1 minute.
- Automatically sends personalized emails with placeholders for Company Name and Position.
- Prevents duplicate emails using a local SQLite database.
- Premium dashboard to track sent emails.

## Setup Instructions

### 1. Prerequisites

- PHP 8.2+
- Composer
- A Google Cloud Project with Google Sheets API enabled.

### 2. Google API Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Create a Project and enable **Google Sheets API**.
3. Create a **Service Account** and download the **JSON credentials**.
4. Save the JSON file as `storage/app/google-service-account.json`.

### 3. Google Sheet Setup

1. Create a Google Sheet.
2. Share the sheet with the Service Account email address (found in your JSON file).
3. The sheet should have the following columns starting from Row 2 (Row 1 is header):
    - **Column A**: Recipient Email
    - **Column B**: Company Name
    - **Column C**: Position Name

### 4. Application Configuration

Update your `.env` file:

```env
GOOGLE_SHEET_ID=your_spreadsheet_id_here
GOOGLE_SHEET_NAME=Sheet1
```

### 5. Start the Automation

Open three terminals:

**Terminal 1: Serve the Dashboard**

```bash
php artisan serve
```

**Terminal 2: Run the Scheduler**

```bash
php artisan schedule:work
```

**Terminal 3 (Optional): Check Logs**

```bash
tail -f storage/logs/laravel.log
```

## Customization

- **Email Content**: Edit `resources/views/emails/job_application.blade.php`.
- **Email Topic**: Edit `app/Mail/DynamicJobMail.php`.
- **Processing Logic**: Edit `app/Console/Commands/ProcessSheetMails.php`.

## Technical Details

- **Database**: SQLite (stored in `database/database.sqlite`).
- **Scheduling**: Laravel Scheduler runs `mail:process-sheet` every minute.
- **Deduplication**: Uses a unique constraint on `email`, `company_name`, and `position_name` in the `mail_logs` table.
