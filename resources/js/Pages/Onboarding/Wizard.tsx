import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    ChevronRight, 
    ChevronLeft, 
    Database, 
    Mail, 
    Check, 
    Loader2,
    CheckCircle2,
    AlertCircle
} from 'lucide-react';
import GuestLayout from '@/Layouts/GuestLayout';
import { toast } from 'sonner';

interface Settings {
    google_sheet_id: string | null;
    smtp_host: string | null;
    smtp_port: string | null;
    smtp_username: string | null;
    smtp_encryption: string | null;
    from_address: string | null;
    from_name: string | null;
}

const STEPS = [
    { title: 'Welcome', desc: 'Overview' },
    { title: 'Google Sheet', desc: 'Data source' },
    { title: 'SMTP Settings', desc: 'Outbox' },
    { title: 'Confirm', desc: 'Finish' },
];

export default function Wizard({ settings }: { settings: Settings }) {
    const [step, setStep] = useState(0);
    const [isTestingSheet, setIsTestingSheet] = useState(false);
    const [isTestingSmtp, setIsTestingSmtp] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        google_sheet_id: settings.google_sheet_id || '',
        smtp_host: settings.smtp_host || '',
        smtp_port: settings.smtp_port || '587',
        smtp_username: settings.smtp_username || '',
        smtp_password: '',
        smtp_encryption: settings.smtp_encryption || 'tls',
        from_address: settings.from_address || '',
        from_name: settings.from_name || '',
    });

    const [sheetTestResult, setSheetTestResult] = useState<{ status: 'success' | 'error'; message: string } | null>(null);
    const [smtpTestResult, setSmtpTestResult] = useState<{ status: 'success' | 'error'; message: string } | null>(null);

    const testSheet = () => {
        if (!data.google_sheet_id.trim()) {
            toast.error('Please enter your Google Sheet ID first.');
            return;
        }
        setIsTestingSheet(true);
        setSheetTestResult(null);

        router.post(route('test.sheet'), {
            google_sheet_id: data.google_sheet_id,
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash: any = page.props.flash;
                if (flash?.success) {
                    setSheetTestResult({ status: 'success', message: flash.success });
                    toast.success(flash.success);
                } else if (flash?.error) {
                    setSheetTestResult({ status: 'error', message: flash.error });
                    toast.error(flash.error);
                }
            },
            onError: (errs) => {
                const errMsg = Object.values(errs)[0] as string || 'Failed to connect to Google Sheet';
                setSheetTestResult({ status: 'error', message: errMsg });
                toast.error(errMsg);
            },
            onFinish: () => setIsTestingSheet(false),
        });
    };

    const testSmtp = () => {
        if (!data.smtp_host.trim() || !data.smtp_username.trim() || !data.smtp_password.trim()) {
            toast.error('Please enter SMTP Host, Username, and App Password to test.');
            return;
        }
        setIsTestingSmtp(true);
        setSmtpTestResult(null);

        router.post(route('test.connection'), {
            smtp_host: data.smtp_host,
            smtp_port: data.smtp_port,
            smtp_username: data.smtp_username,
            smtp_password: data.smtp_password,
            smtp_encryption: data.smtp_encryption,
            from_address: data.from_address,
            from_name: data.from_name,
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash: any = page.props.flash;
                if (flash?.success) {
                    setSmtpTestResult({ status: 'success', message: flash.success });
                    toast.success(flash.success);
                } else if (flash?.error) {
                    setSmtpTestResult({ status: 'error', message: flash.error });
                    toast.error(flash.error);
                }
            },
            onError: (errs) => {
                const errMsg = Object.values(errs)[0] as string || 'SMTP connection test failed';
                setSmtpTestResult({ status: 'error', message: errMsg });
                toast.error(errMsg);
            },
            onFinish: () => setIsTestingSmtp(false),
        });
    };

    const nextStep = () => {
        if (step === 1 && !data.google_sheet_id.trim()) {
            toast.error('Please enter your Google Sheet ID before continuing.');
            return;
        }
        if (step === 2) {
            if (
                !data.smtp_host.trim() ||
                !data.smtp_port.toString().trim() ||
                !data.smtp_username.trim() ||
                !data.smtp_password.trim() ||
                !data.from_name.trim() ||
                !data.from_address.trim() ||
                !data.smtp_encryption.trim()
            ) {
                toast.error('Please fill out all required SMTP fields before continuing.');
                return;
            }
        }
        setStep(s => Math.min(s + 1, 3));
    };


    const prevStep = () => setStep(s => Math.max(s - 1, 0));

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('onboarding.complete'));
    };

    return (
        <GuestLayout title="Initial Setup" maxWidth="max-w-2xl" showCard={false}>
            <Head title="Initial Setup" />

            <div className="w-full space-y-6">
                {/* Brand header */}
                <div className="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800">
                    <div className="flex items-center gap-2">
                        <span className="text-base font-bold text-zinc-900 dark:text-zinc-50 tracking-tight">MailAuto</span>
                        <span className="text-zinc-300 dark:text-zinc-700">/</span>
                        <span className="text-sm text-zinc-500 font-medium">Initial Setup</span>
                    </div>
                    <span className="text-xs font-mono text-zinc-500">
                        Step {step + 1} of {STEPS.length}
                    </span>
                </div>

                {/* Minimal clean Stepper */}
                <div className="grid grid-cols-4 gap-2">
                    {STEPS.map((s, idx) => {
                        const isDone = idx < step;
                        const isCurrent = idx === step;

                        return (
                            <div key={idx} className="space-y-1.5">
                                <div 
                                    className={`h-1 w-full rounded-full transition-colors duration-200 ${
                                        isDone 
                                            ? 'bg-zinc-900 dark:bg-zinc-100' 
                                            : isCurrent 
                                            ? 'bg-indigo-600' 
                                            : 'bg-zinc-200 dark:bg-zinc-800'
                                    }`}
                                />
                                <div className="hidden sm:block">
                                    <p className={`text-xs font-medium leading-none ${isCurrent ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400'}`}>
                                        {s.title}
                                    </p>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Form Body */}
                <form onSubmit={step === 3 ? submit : (e) => { e.preventDefault(); nextStep(); }}>
                    {step === 0 && (
                        <Card className="border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                            <CardHeader className="space-y-1 pb-4">
                                <CardTitle className="text-xl font-semibold text-zinc-900 dark:text-zinc-50">
                                    Set up your mail engine
                                </CardTitle>
                                <CardDescription className="text-sm text-zinc-500 dark:text-zinc-400">
                                    Configure your data source and SMTP connection to start automated dispatching.
                                </CardDescription>
                            </CardHeader>

                            <CardContent className="space-y-4 pt-2">
                                <div className="space-y-3 rounded-lg border border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-950/40 p-4 text-sm text-zinc-600 dark:text-zinc-300">
                                    <p className="font-medium text-zinc-900 dark:text-zinc-100">Before you begin, ensure you have:</p>
                                    <ul className="list-disc pl-5 space-y-1.5 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                                        <li>Your Google Sheet shared with editor permissions for your service account.</li>
                                        <li>Your SMTP credentials (e.g. Google App Password if using Gmail).</li>
                                    </ul>
                                </div>
                            </CardContent>

                            <CardFooter className="flex justify-end pt-4 pb-5 px-6 border-t border-zinc-100 dark:border-zinc-800">
                                <Button type="button" onClick={nextStep} className="gap-2">
                                    Continue
                                    <ChevronRight className="h-4 w-4" />
                                </Button>
                            </CardFooter>
                        </Card>
                    )}

                    {step === 1 && (
                        <Card className="border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                            <CardHeader className="space-y-1 pb-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-xl font-semibold text-zinc-900 dark:text-zinc-50">
                                            Google Sheet
                                        </CardTitle>
                                        <CardDescription className="text-sm text-zinc-500">
                                            Connect the spreadsheet holding candidate and log records.
                                        </CardDescription>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={testSheet}
                                        disabled={isTestingSheet || !data.google_sheet_id.trim()}
                                        className="h-8 gap-1.5 text-xs"
                                    >
                                        {isTestingSheet ? (
                                            <>
                                                <Loader2 className="w-3.5 h-3.5 animate-spin" />
                                                Testing...
                                            </>
                                        ) : (
                                            'Test Connection'
                                        )}
                                    </Button>
                                </div>
                            </CardHeader>

                            <CardContent className="space-y-4 pt-2">
                                <div className="space-y-2">
                                    <Label htmlFor="google_sheet_id" className="text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                        Spreadsheet ID or URL
                                    </Label>
                                    <Input
                                        id="google_sheet_id"
                                        value={data.google_sheet_id}
                                        onChange={e => {
                                            const val = e.target.value;
                                            const match = val.match(/spreadsheets\/d\/([a-zA-Z0-9-_]+)/);
                                            setData('google_sheet_id', match ? match[1] : val);
                                        }}
                                        placeholder="1o2DHXBKneuAcmxlZS-7egyy3SyGFSLZnHebnUrFqjzI"
                                        className="font-mono text-sm"
                                        required
                                        autoFocus
                                    />
                                    <div className="rounded-md bg-zinc-50 dark:bg-zinc-800/60 p-3.5 border border-zinc-200 dark:border-zinc-700 text-xs space-y-3">
                                        <div className="space-y-1.5">
                                            <p className="font-medium text-zinc-800 dark:text-zinc-200">
                                                1. Share with Service Account:
                                            </p>
                                            <p className="text-zinc-500 text-[11px]">
                                                Add this email as an <strong className="font-medium text-zinc-700 dark:text-zinc-300">Editor</strong> in Google Sheet sharing settings:
                                            </p>
                                            <div className="flex items-center gap-2 select-all font-mono text-[11px] bg-white dark:bg-zinc-900 px-2.5 py-1.5 rounded border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 break-all">
                                                mail-945@phonic-command-450213-h2.iam.gserviceaccount.com
                                            </div>
                                        </div>

                                        <div className="space-y-2 pt-1 border-t border-zinc-200 dark:border-zinc-700/60">
                                            <p className="font-medium text-zinc-800 dark:text-zinc-200">
                                                2. Required Sheet Format (Columns A, B, C):
                                            </p>
                                            <p className="text-zinc-500 text-[11px]">
                                                The first row must have these headers in order:
                                            </p>
                                            <div className="grid grid-cols-3 gap-1.5 text-center font-mono text-[11px]">
                                                <div className="bg-white dark:bg-zinc-900 py-1.5 px-2 rounded border border-zinc-200 dark:border-zinc-700">
                                                    <span className="text-zinc-400 block text-[9px] uppercase">Col A</span>
                                                    <span className="font-semibold text-zinc-800 dark:text-zinc-200">Company</span>
                                                </div>
                                                <div className="bg-white dark:bg-zinc-900 py-1.5 px-2 rounded border border-zinc-200 dark:border-zinc-700">
                                                    <span className="text-zinc-400 block text-[9px] uppercase">Col B</span>
                                                    <span className="font-semibold text-zinc-800 dark:text-zinc-200">Email</span>
                                                </div>
                                                <div className="bg-white dark:bg-zinc-900 py-1.5 px-2 rounded border border-zinc-200 dark:border-zinc-700">
                                                    <span className="text-zinc-400 block text-[9px] uppercase">Col C</span>
                                                    <span className="font-semibold text-zinc-800 dark:text-zinc-200">Position</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {sheetTestResult && (
                                        <div
                                            className={`flex items-start gap-2.5 p-3 rounded-lg border text-xs ${
                                                sheetTestResult.status === 'success'
                                                    ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900 dark:bg-emerald-950/30 dark:border-emerald-800 dark:text-emerald-200'
                                                    : 'bg-red-50/80 border-red-200 text-red-900 dark:bg-red-950/30 dark:border-red-800 dark:text-red-200'
                                            }`}
                                        >
                                            {sheetTestResult.status === 'success' ? (
                                                <CheckCircle2 className="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                                            ) : (
                                                <AlertCircle className="h-4 w-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                                            )}
                                            <div className="flex-1 break-words leading-relaxed font-medium">
                                                {sheetTestResult.message}
                                            </div>
                                        </div>
                                    )}

                                    {errors.google_sheet_id && (
                                        <p className="text-red-500 text-xs">{errors.google_sheet_id}</p>
                                    )}
                                </div>
                            </CardContent>

                            <CardFooter className="flex justify-between pt-4 pb-5 px-6 border-t border-zinc-100 dark:border-zinc-800">
                                <Button type="button" variant="outline" onClick={prevStep} className="gap-2">
                                    <ChevronLeft className="h-4 w-4" />
                                    Back
                                </Button>
                                <Button type="button" onClick={nextStep} className="gap-2">
                                    Continue
                                    <ChevronRight className="h-4 w-4" />
                                </Button>
                            </CardFooter>
                        </Card>
                    )}

                    {step === 2 && (
                        <Card className="border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                            <CardHeader className="space-y-1 pb-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-xl font-semibold text-zinc-900 dark:text-zinc-50">
                                            SMTP Settings
                                        </CardTitle>
                                        <CardDescription className="text-sm text-zinc-500">
                                            Configure mail credentials to send automated messages.
                                        </CardDescription>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={testSmtp}
                                        disabled={isTestingSmtp || !data.smtp_host.trim() || !data.smtp_username.trim() || !data.smtp_password.trim()}
                                        className="h-8 gap-1.5 text-xs"
                                    >
                                        {isTestingSmtp ? (
                                            <>
                                                <Loader2 className="w-3.5 h-3.5 animate-spin" />
                                                Testing...
                                            </>
                                        ) : (
                                            'Test SMTP'
                                        )}
                                    </Button>
                                </div>
                            </CardHeader>

                            <CardContent className="space-y-4 pt-2">
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div className="space-y-1.5 sm:col-span-2">
                                        <Label className="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Host</Label>
                                        <Input
                                            required
                                            value={data.smtp_host}
                                            onChange={e => setData('smtp_host', e.target.value)}
                                            placeholder="smtp.gmail.com"
                                            className="text-sm"
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label className="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Port</Label>
                                        <Input
                                            required
                                            value={data.smtp_port}
                                            onChange={e => setData('smtp_port', e.target.value)}
                                            placeholder="587"
                                            className="text-sm"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div className="space-y-1.5">
                                        <Label className="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Username (Email)</Label>
                                        <Input
                                            required
                                            type="email"
                                            value={data.smtp_username}
                                            onChange={e => setData('smtp_username', e.target.value)}
                                            placeholder="you@gmail.com"
                                            className="text-sm"
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label className="text-xs font-semibold text-zinc-600 dark:text-zinc-300">App Password</Label>
                                        <Input
                                            required
                                            type="password"
                                            value={data.smtp_password}
                                            onChange={e => setData('smtp_password', e.target.value)}
                                            placeholder="App password"
                                            className="text-sm"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div className="space-y-1.5">
                                        <Label className="text-xs font-semibold text-zinc-600 dark:text-zinc-300">From Name</Label>
                                        <Input
                                            required
                                            value={data.from_name}
                                            onChange={e => setData('from_name', e.target.value)}
                                            placeholder="Your Name"
                                            className="text-sm"
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label className="text-xs font-semibold text-zinc-600 dark:text-zinc-300">From Address</Label>
                                        <Input
                                            required
                                            type="email"
                                            value={data.from_address}
                                            onChange={e => setData('from_address', e.target.value)}
                                            placeholder="you@domain.com"
                                            className="text-sm"
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label className="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Encryption</Label>
                                        <Input
                                            required
                                            value={data.smtp_encryption}
                                            onChange={e => setData('smtp_encryption', e.target.value)}
                                            placeholder="tls"
                                            className="text-sm"
                                        />
                                    </div>
                                </div>

                                {smtpTestResult && (
                                    <div
                                        className={`flex items-start gap-2.5 p-3 rounded-lg border text-xs ${
                                            smtpTestResult.status === 'success'
                                                ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900 dark:bg-emerald-950/30 dark:border-emerald-800 dark:text-emerald-200'
                                                : 'bg-red-50/80 border-red-200 text-red-900 dark:bg-red-950/30 dark:border-red-800 dark:text-red-200'
                                        }`}
                                    >
                                        {smtpTestResult.status === 'success' ? (
                                            <CheckCircle2 className="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                                        ) : (
                                            <AlertCircle className="h-4 w-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                                        )}
                                        <div className="flex-1 break-words leading-relaxed font-medium">
                                            {smtpTestResult.message}
                                        </div>
                                    </div>
                                )}
                            </CardContent>

                            <CardFooter className="flex justify-between pt-4 pb-5 px-6 border-t border-zinc-100 dark:border-zinc-800">
                                <Button type="button" variant="outline" onClick={prevStep} className="gap-2">
                                    <ChevronLeft className="h-4 w-4" />
                                    Back
                                </Button>
                                <Button type="button" onClick={nextStep} className="gap-2">
                                    Continue
                                    <ChevronRight className="h-4 w-4" />
                                </Button>
                            </CardFooter>
                        </Card>
                    )}

                    {step === 3 && (
                        <Card className="border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">
                            <CardHeader className="space-y-1 pb-4">
                                <CardTitle className="text-xl font-semibold text-zinc-900 dark:text-zinc-50">
                                    Review & Confirm
                                </CardTitle>
                                <CardDescription className="text-sm text-zinc-500">
                                    Review your parameters. These can be adjusted anytime under Configuration.
                                </CardDescription>
                            </CardHeader>

                            <CardContent className="space-y-3 pt-2">
                                <div className="rounded-lg border border-zinc-200 dark:border-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-800 text-sm">
                                    <div className="flex justify-between p-3">
                                        <span className="text-zinc-500">Google Sheet ID</span>
                                        <span className="font-mono text-xs truncate max-w-[240px] font-medium text-zinc-900 dark:text-zinc-100">
                                            {data.google_sheet_id || '—'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between p-3">
                                        <span className="text-zinc-500">SMTP Server</span>
                                        <span className="font-medium text-zinc-900 dark:text-zinc-100">
                                            {data.smtp_host}:{data.smtp_port} ({data.smtp_encryption})
                                        </span>
                                    </div>
                                    <div className="flex justify-between p-3">
                                        <span className="text-zinc-500">Sender</span>
                                        <span className="font-medium text-zinc-900 dark:text-zinc-100">
                                            {data.from_name} &lt;{data.from_address}&gt;
                                        </span>
                                    </div>
                                </div>
                            </CardContent>

                            <CardFooter className="flex justify-between pt-4 pb-5 px-6 border-t border-zinc-100 dark:border-zinc-800">
                                <Button type="button" variant="outline" onClick={prevStep} className="gap-2">
                                    <ChevronLeft className="h-4 w-4" />
                                    Back
                                </Button>
                                <Button type="submit" disabled={processing} className="gap-2">
                                    {processing ? (
                                        <>
                                            <Loader2 className="w-4 h-4 animate-spin" />
                                            Saving...
                                        </>
                                    ) : (
                                        <>
                                            Complete Setup
                                            <Check className="h-4 w-4" />
                                        </>
                                    )}
                                </Button>
                            </CardFooter>
                        </Card>
                    )}
                </form>
            </div>
        </GuestLayout>
    );
}


