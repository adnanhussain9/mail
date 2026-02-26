import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { CheckCircle2, Clock, ExternalLink, FileSpreadsheet, Mail, Play, Plus, PlusCircle, RefreshCcw, Save, Search, Target } from 'lucide-react';

interface Log {
    id: number;
    email: string;
    company_name: string;
    position_name: string;
    sent_at: string;
}

interface PaginatedLogs {
    current_page: number;
    data: Log[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Settings {
    subject: string;
    body: string;
    attachment_path: string | null;
    search_keywords: string;
    is_auto_hunting: boolean;
}

export default function Dashboard({ logs, settings, status }: { logs: PaginatedLogs; settings: Settings; status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: 'POST',
        subject: settings.subject || '',
        body: settings.body || '',
        attachment: null as File | null,
        search_keywords: settings.search_keywords || '',
        is_auto_hunting: settings.is_auto_hunting || false,
    });

    const sheetForm = useForm({
        company: '',
        email: '',
        position: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('settings.update'), {
            forceFormData: true,
        });
    };

    const submitSheetData = (e: React.FormEvent) => {
        e.preventDefault();
        sheetForm.post(route('sheet.add'), {
            onSuccess: () => sheetForm.reset(),
        });
    };

    const processSheet = () => {
        router.post(route('process.sheet'), {}, {
            preserveScroll: true,
        });
    };

    const refreshDashboard = () => {
        router.get(route('dashboard'), {}, { preserveState: false, preserveScroll: false });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between px-2">
                    <h2 className="text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                        Mail Automated
                    </h2>
                    <div className="flex flex-wrap items-center gap-2">
                        <Button variant="outline" asChild className="gap-2 flex-1 sm:flex-none">
                            <Link href={route('sheet.view')}>
                                <FileSpreadsheet className="h-4 w-4 text-emerald-500" />
                                <span className="hidden xs:inline">View Sheet</span>
                                <span className="xs:hidden">Sheet</span>
                            </Link>
                        </Button>
                        {import.meta.env.VITE_GOOGLE_SHEET_URL && (
                            <Button variant="outline" size="icon" asChild title="Open in Google Sheets" className="hidden sm:inline-flex">
                                <a href={import.meta.env.VITE_GOOGLE_SHEET_URL} target="_blank" rel="noopener noreferrer">
                                    <ExternalLink className="h-4 w-4 text-zinc-400" />
                                </a>
                            </Button>
                        )}
                        <Button variant="outline" onClick={processSheet} className="gap-2 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800 flex-1 sm:flex-none">
                            <Play className="h-4 w-4 fill-current" />
                            Run <span className="hidden xs:inline">Processor</span>
                        </Button>
                        <Button variant="outline" onClick={refreshDashboard} className="gap-2 flex-1 sm:flex-none">
                            <RefreshCcw className="h-4 w-4" />
                            <span className="hidden xs:inline">Refresh</span>
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="py-4 sm:py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

                    {/* Stats Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <Card className="relative overflow-hidden">
                            <div className="absolute top-0 left-0 w-1 h-full bg-indigo-500 opacity-50" />
                            <CardHeader className="pb-2">
                                <CardDescription className="text-[10px] sm:text-xs font-bold uppercase tracking-wider">Total Processed</CardDescription>
                                <CardTitle className="text-2xl sm:text-3xl font-extrabold flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                    {logs.total}
                                    <Mail className="h-5 w-5 sm:h-6 sm:w-6 opacity-20" />
                                </CardTitle>
                            </CardHeader>
                        </Card>

                        <Card className="relative overflow-hidden">
                            <div className="absolute top-0 left-0 w-1 h-full bg-amber-500 opacity-50" />
                            <CardHeader className="pb-2">
                                <CardDescription className="text-[10px] sm:text-xs font-bold uppercase tracking-wider">Check Interval</CardDescription>
                                <CardTitle className="text-2xl sm:text-3xl font-extrabold flex items-center gap-2">
                                    1 min
                                    <Clock className="h-5 w-5 sm:h-6 sm:w-6 text-amber-500 opacity-20" />
                                </CardTitle>
                            </CardHeader>
                        </Card>

                        <Card className="relative overflow-hidden sm:col-span-2 lg:col-span-1">
                            <div className="absolute top-0 left-0 w-1 h-full bg-emerald-500 opacity-50" />
                            <CardHeader className="pb-2">
                                <CardDescription className="text-[10px] sm:text-xs font-bold uppercase tracking-wider">System Status</CardDescription>
                                <CardTitle className="text-2xl sm:text-3xl font-extrabold flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                                    Active
                                    <span className="relative flex h-3 w-3">
                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                    </span>
                                </CardTitle>
                            </CardHeader>
                        </Card>
                    </div>

                    {/* Quick Add Entry */}
                    <Card className="shadow-lg border-zinc-200 dark:border-zinc-800">
                        <CardHeader className="border-b bg-zinc-50/50 dark:bg-zinc-900/50 py-4">
                            <div className="flex items-center gap-2">
                                <PlusCircle className="h-5 w-5 text-indigo-500" />
                                <CardTitle className="text-lg">Quick Add Entry</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-6">
                            <form onSubmit={submitSheetData} className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                <div className="space-y-2">
                                    <Label htmlFor="q_company">Company</Label>
                                    <Input
                                        id="q_company"
                                        value={sheetForm.data.company}
                                        onChange={e => sheetForm.setData('company', e.target.value)}
                                        placeholder="Company Name"
                                    />
                                    {sheetForm.errors.company && <p className="text-xs text-red-500">{sheetForm.errors.company}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="q_email">Email</Label>
                                    <Input
                                        id="q_email"
                                        type="email"
                                        value={sheetForm.data.email}
                                        onChange={e => sheetForm.setData('email', e.target.value)}
                                        placeholder="m@example.com"
                                    />
                                    {sheetForm.errors.email && <p className="text-xs text-red-500">{sheetForm.errors.email}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="q_position">Position</Label>
                                    <Input
                                        id="q_position"
                                        value={sheetForm.data.position}
                                        onChange={e => sheetForm.setData('position', e.target.value)}
                                        placeholder="e.g. Web Developer"
                                    />
                                    {sheetForm.errors.position && <p className="text-xs text-red-500">{sheetForm.errors.position}</p>}
                                </div>
                                <Button className="w-full bg-indigo-600 hover:bg-indigo-700 font-bold" disabled={sheetForm.processing}>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Add to Sheet
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Configuration Form */}
                    <Card className="shadow-lg border-zinc-200 dark:border-zinc-800">
                        <CardHeader className="border-b bg-zinc-50/50 dark:bg-zinc-900/50 py-4">
                            <div className="flex items-center gap-2">
                                <Save className="h-5 w-5 text-indigo-500" />
                                <CardTitle className="text-lg">Configuration</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-6">
                            <form onSubmit={submit} className="space-y-6">
                                <div className="flex flex-wrap gap-2 text-[10px] sm:text-xs font-mono mb-4 text-zinc-500 bg-zinc-100 dark:bg-zinc-800 p-2 rounded-md">
                                    <span className="font-semibold">Dynamic Fields:</span>
                                    <code className="text-indigo-500">{'{email}'}</code>
                                    <code className="text-indigo-500">{'{company}'}</code>
                                    <code className="text-indigo-500">{'{position}'}</code>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="subject">Mail Subject</Label>
                                    <Input
                                        id="subject"
                                        value={data.subject}
                                        onChange={e => setData('subject', e.target.value)}
                                        placeholder="e.g. Applying for {position} at {company}"
                                    />
                                    {errors.subject && <p className="text-xs text-red-500 font-medium">{errors.subject}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="body">Email Body Content</Label>
                                    <Textarea
                                        id="body"
                                        rows={6}
                                        value={data.body}
                                        onChange={e => setData('body', e.target.value)}
                                        placeholder="Write your professional message here..."
                                    />
                                    {errors.body && <p className="text-xs text-red-500 font-medium">{errors.body}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="attachment">Resume / CV (PDF)</Label>
                                    <Input
                                        id="attachment"
                                        type="file"
                                        accept=".pdf"
                                        onChange={e => setData('attachment', e.target.files?.[0] || null)}
                                    />
                                    {settings.attachment_path && !data.attachment && (
                                        <div className="flex items-center gap-2 mt-2 text-emerald-600 font-semibold text-[10px] sm:text-xs">
                                            <CheckCircle2 className="h-4 w-4" />
                                            Linked: {settings.attachment_path.split('/').pop()}
                                        </div>
                                    )}
                                </div>

                                <div className="p-4 sm:p-6 rounded-xl border border-dashed border-indigo-200 dark:border-indigo-800 bg-indigo-50/30 dark:bg-indigo-950/20 space-y-4">
                                    <div className="flex items-center gap-2">
                                        <div className="bg-indigo-500 text-white p-1.5 rounded-lg">
                                            <Target className="h-4 w-4" />
                                        </div>
                                        <h3 className="font-bold text-sm">AI Job Hunter</h3>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="search_keywords">Search Interests (Keywords)</Label>
                                        <div className="relative">
                                            <Search className="absolute left-3 top-3 h-4 w-4 text-zinc-400" />
                                            <Input
                                                id="search_keywords"
                                                className="pl-9"
                                                value={data.search_keywords}
                                                onChange={e => setData('search_keywords', e.target.value)}
                                                placeholder="e.g. Laravel Developer, Remote PHP"
                                            />
                                        </div>
                                    </div>

                                    <div className="flex items-start space-x-3 pt-2">
                                        <Checkbox
                                            id="is_auto_hunting"
                                            checked={data.is_auto_hunting}
                                            onCheckedChange={checked => setData('is_auto_hunting', !!checked)}
                                        />
                                        <div className="grid gap-1.5 leading-none">
                                            <label htmlFor="is_auto_hunting" className="text-sm font-semibold leading-none cursor-pointer">
                                                Enable Autonomous Job Hunting
                                            </label>
                                            <p className="text-xs text-zinc-500">
                                                The system will automatically scan Reddit and RSS feeds for matching posts and send your application.
                                            </p>
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

                    {/* Logs Table */}
                    <Card className="shadow-lg">
                        <CardHeader className="border-b bg-zinc-50/50 dark:bg-zinc-900/50 py-4">
                            <div className="flex items-center gap-2">
                                <Search className="h-5 w-5 text-indigo-500" />
                                <CardTitle className="text-lg">Transmission Logs</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            {logs.data.length === 0 ? (
                                <div className="text-center py-12 px-4 space-y-3">
                                    <p className="text-zinc-500 italic">No mails sent yet. Start the scheduler to begin.</p>
                                    <code className="inline-block bg-zinc-100 dark:bg-zinc-800 px-3 py-1.5 rounded-md text-zinc-600 dark:text-zinc-400 font-mono text-[10px] sm:text-xs border border-zinc-200 dark:border-zinc-700 break-all">
                                        php artisan schedule:work
                                    </code>
                                </div>
                            ) : (
                                <div className="space-y-0">
                                    <div className="overflow-x-auto">
                                        <Table className="min-w-[600px] w-full">
                                            <TableHeader className="bg-zinc-50 dark:bg-zinc-900">
                                                <TableRow>
                                                    <TableHead className="font-bold text-zinc-500 uppercase tracking-wider text-[10px]">Recipient</TableHead>
                                                    <TableHead className="font-bold text-zinc-500 uppercase tracking-wider text-[10px]">Company</TableHead>
                                                    <TableHead className="font-bold text-zinc-500 uppercase tracking-wider text-[10px]">Position</TableHead>
                                                    <TableHead className="font-bold text-zinc-500 uppercase tracking-wider text-[10px]">Sent At</TableHead>
                                                    <TableHead className="font-bold text-zinc-500 uppercase tracking-wider text-[10px] text-right px-4">Status</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {logs.data.map((log) => (
                                                    <TableRow key={log.id}>
                                                        <TableCell className="font-bold py-4 text-xs sm:text-sm">{log.email}</TableCell>
                                                        <TableCell className="text-xs sm:text-sm">{log.company_name}</TableCell>
                                                        <TableCell className="text-xs sm:text-sm">{log.position_name}</TableCell>
                                                        <TableCell className="text-zinc-500 text-xs">{log.sent_at}</TableCell>
                                                        <TableCell className="text-right px-4">
                                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                                                                Sent
                                                            </span>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>

                                    {/* Simple Pagination */}
                                    {logs.links.length > 3 && (
                                        <div className="p-4 border-t bg-zinc-50/30 dark:bg-zinc-900/10 overflow-x-auto">
                                            <div className="flex gap-1 sm:gap-2 justify-center min-w-max">
                                                {logs.links.map((link, i) => (
                                                    <Button
                                                        key={i}
                                                        variant={link.active ? "default" : "outline"}
                                                        size="sm"
                                                        disabled={!link.url}
                                                        onClick={() => link.url && router.get(link.url)}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                        className={`font-semibold rounded-lg text-xs ${!link.url ? 'opacity-50' : ''}`}
                                                    />
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
