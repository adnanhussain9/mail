import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, router, useForm } from '@inertiajs/react';
import { Database, Mail, Save, CheckCircle2 } from 'lucide-react';

interface Settings {
    subject: string;
    body: string;
    attachment_path: string | null;
    search_keywords: string;
    is_auto_hunting: boolean;
    google_sheet_id: string | null;
    smtp_host: string | null;
    smtp_port: string | null;
    smtp_username: string | null;
    smtp_encryption: string | null;
    from_address: string | null;
    from_name: string | null;
}

export default function Configuration({ settings, status }: { settings: Settings; status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: 'POST',
        google_sheet_id: settings.google_sheet_id || '',
        smtp_host: settings.smtp_host || '',
        smtp_port: settings.smtp_port || '587',
        smtp_username: settings.smtp_username || '',
        smtp_password: '',
        smtp_encryption: settings.smtp_encryption || 'tls',
        from_address: settings.from_address || '',
        from_name: settings.from_name || '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('settings.update'), {
            forceFormData: true,
        });
    };

    const testSmtp = () => {
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
        });
    };

    const testSheet = () => {
        router.post(route('test.sheet'), {
            google_sheet_id: data.google_sheet_id,
        }, {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Mail & System Configuration</h2>}
        >
            <Head title="Configuration" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card className="shadow-lg border-zinc-200 dark:border-zinc-800">
                        <CardHeader className="border-b bg-zinc-50/50 dark:bg-zinc-900/50 py-4">
                            <div className="flex items-center gap-2">
                                <Save className="h-5 w-5 text-indigo-500" />
                                <CardTitle className="text-lg">System Configuration</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-6">
                            <form onSubmit={submit} className="space-y-8">

                                {/* Integrations Section */}
                                <div className="space-y-4">
                                    <h3 className="font-bold text-lg border-b pb-2">Integrations</h3>

                                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        {/* Google Sheets */}
                                        <div className="space-y-4 p-4 border rounded-xl bg-zinc-50 dark:bg-zinc-900/30">
                                            <div className="flex items-center justify-between">
                                                <h4 className="font-semibold flex items-center gap-2">
                                                    <Database className="h-4 w-4 text-emerald-500" />
                                                    Google Sheet
                                                </h4>
                                                <Button type="button" variant="outline" size="sm" onClick={testSheet}>
                                                    Test Connection
                                                </Button>
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="google_sheet_id">Spreadsheet ID</Label>
                                                <Input
                                                    id="google_sheet_id"
                                                    value={data.google_sheet_id}
                                                    onChange={e => setData('google_sheet_id', e.target.value)}
                                                    placeholder="e.g. 1BxiMVs0XRYFgCE..."
                                                />
                                                <div className="rounded-md bg-zinc-100 dark:bg-zinc-800/60 p-2.5 text-xs space-y-2 text-zinc-600 dark:text-zinc-300">
                                                    <p className="font-medium text-[11px] text-zinc-800 dark:text-zinc-200">
                                                        Required Sheet Headers (Row 1):
                                                    </p>
                                                    <div className="grid grid-cols-3 gap-1 text-center font-mono text-[10px]">
                                                        <div className="bg-white dark:bg-zinc-900 py-1 px-1.5 rounded border">
                                                            <span className="text-zinc-400 block text-[8px] uppercase">Col A</span>
                                                            <span className="font-semibold">Company</span>
                                                        </div>
                                                        <div className="bg-white dark:bg-zinc-900 py-1 px-1.5 rounded border">
                                                            <span className="text-zinc-400 block text-[8px] uppercase">Col B</span>
                                                            <span className="font-semibold">Email</span>
                                                        </div>
                                                        <div className="bg-white dark:bg-zinc-900 py-1 px-1.5 rounded border">
                                                            <span className="text-zinc-400 block text-[8px] uppercase">Col C</span>
                                                            <span className="font-semibold">Position</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p className="text-[10px] text-zinc-500">Must be shared with editor permissions to the service account.</p>
                                            </div>
                                        </div>

                                        {/* SMTP */}
                                        <div className="space-y-4 p-4 border rounded-xl bg-zinc-50 dark:bg-zinc-900/30">
                                            <div className="flex items-center justify-between">
                                                <h4 className="font-semibold flex items-center gap-2">
                                                    <Mail className="h-4 w-4 text-indigo-500" />
                                                    SMTP Configuration
                                                </h4>
                                                <Button type="button" variant="outline" size="sm" onClick={testSmtp}>
                                                    Test SMTP
                                                </Button>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <div className="space-y-2">
                                                    <Label>Host</Label>
                                                    <Input value={data.smtp_host} onChange={e => setData('smtp_host', e.target.value)} placeholder="smtp.gmail.com" />
                                                </div>
                                                <div className="space-y-2">
                                                    <Label>Port</Label>
                                                    <Input value={data.smtp_port} onChange={e => setData('smtp_port', e.target.value)} placeholder="587" />
                                                </div>
                                                <div className="space-y-2">
                                                    <Label>Username</Label>
                                                    <Input value={data.smtp_username} onChange={e => setData('smtp_username', e.target.value)} placeholder="you@gmail.com" />
                                                </div>
                                                <div className="space-y-2">
                                                    <Label>App Password</Label>
                                                    <Input type="password" value={data.smtp_password} onChange={e => setData('smtp_password', e.target.value)} placeholder={settings.smtp_host ? "********" : "Required"} />
                                                </div>
                                                <div className="space-y-2">
                                                    <Label>Encryption</Label>
                                                    <Input value={data.smtp_encryption} onChange={e => setData('smtp_encryption', e.target.value)} placeholder="tls" />
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <div className="space-y-2">
                                                    <Label>From Address</Label>
                                                    <Input value={data.from_address} onChange={e => setData('from_address', e.target.value)} placeholder="you@domain.com" />
                                                </div>
                                                <div className="space-y-2">
                                                    <Label>From Name</Label>
                                                    <Input value={data.from_name} onChange={e => setData('from_name', e.target.value)} placeholder="Your Name" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <Button size="lg" className="w-full h-12 shadow-md shadow-indigo-200 dark:shadow-none bg-indigo-600 hover:bg-indigo-700 font-bold" disabled={processing}>
                                    Deploy Configuration
                                </Button>

                                {status && (
                                    <div className="p-4 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-lg flex items-center gap-2 text-sm font-bold border border-emerald-100 dark:border-emerald-900/50">
                                        <CheckCircle2 className="h-4 w-4" />
                                        {status}
                                    </div>
                                )}
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
