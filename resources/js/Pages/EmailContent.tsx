import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Head, useForm } from '@inertiajs/react';
import { CheckCircle2, FileText, RefreshCcw, Save, Search, Target, Wand2, X } from 'lucide-react';
import { toast } from 'sonner';

interface Settings {
    subject: string;
    body: string;
    attachment_path: string | null;
    search_keywords: string;
    is_auto_hunting: boolean;
}

export default function EmailContent({ settings }: { settings: Settings }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: 'POST',
        subject: settings.subject || '',
        body: settings.body || '',
        attachment: null as File | null,
        search_keywords: settings.search_keywords || '',
        is_auto_hunting: settings.is_auto_hunting || false,
    });

    const [jd, setJd] = useState('');
    const [isGenerating, setIsGenerating] = useState(false);
    const [showAIGenerator, setShowAIGenerator] = useState(false);

    const handleGenerateAI = async () => {
        if (!jd) return;
        setIsGenerating(true);
        try {
            const response = await axios.post(route('email.generate'), { jd });
            if (response.data.body) {
                setData('body', response.data.body);
            }
        } catch (error: any) {
            console.error('AI Generation failed', error);
            toast.error(error.response?.data?.error || 'AI Generation failed');
        } finally {
            setIsGenerating(false);
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('settings.update'), {
            forceFormData: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Email Content & AI</h2>}
        >
            <Head title="Email Content" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card className="shadow-lg border-zinc-200 dark:border-zinc-800">
                        <CardHeader className="border-b bg-zinc-50/50 dark:bg-zinc-900/50 py-4">
                            <div className="flex items-center gap-2">
                                <FileText className="h-5 w-5 text-indigo-500" />
                                <CardTitle className="text-lg">Email Template & Settings</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-6">
                            <form onSubmit={submit} className="space-y-8">
                                <div className="space-y-4">
                                    <h3 className="font-bold text-lg border-b pb-2">Email Content</h3>

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

                                    {!showAIGenerator ? (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setShowAIGenerator(true)}
                                            className="w-full border-dashed border-2 border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 h-12"
                                        >
                                            <Wand2 className="mr-2 h-4 w-4" />
                                            Use AI to generate email from Job Description
                                        </Button>
                                    ) : (
                                        <div className="space-y-4 border-2 border-dashed border-indigo-200 dark:border-indigo-900/50 p-4 rounded-xl bg-indigo-50/30 dark:bg-indigo-950/20 relative">
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="icon" 
                                                onClick={() => setShowAIGenerator(false)}
                                                className="absolute top-2 right-2 h-6 w-6 text-zinc-400 hover:text-zinc-600"
                                            >
                                                <X className="h-4 w-4" />
                                            </Button>
                                            <div className="flex items-center justify-between pt-2">
                                                <Label htmlFor="jd" className="text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-2">
                                                    <Wand2 className="h-4 w-4" />
                                                    AI Assistant: Paste Job Description
                                                </Label>
                                                <Button
                                                    type="button"
                                                    onClick={handleGenerateAI}
                                                    disabled={!jd || isGenerating}
                                                    variant="outline"
                                                    size="sm"
                                                    className="bg-indigo-600 text-white hover:bg-indigo-700 border-none transition-all duration-300"
                                                >
                                                    {isGenerating ? (
                                                        <RefreshCcw className="h-4 w-4 mr-2 animate-spin" />
                                                    ) : (
                                                        <Wand2 className="h-4 w-4 mr-2" />
                                                    )}
                                                    {isGenerating ? 'Generating...' : 'Generate with AI'}
                                                </Button>
                                            </div>
                                            <Textarea
                                                id="jd"
                                                rows={4}
                                                value={jd}
                                                onChange={e => setJd(e.target.value)}
                                                placeholder="Paste the job description here and I'll write the email for you..."
                                                className="bg-white dark:bg-zinc-950"
                                            />
                                            <p className="text-[10px] text-zinc-500 italic">This will overwrite your current email body with a professional alternative based on the JD.</p>
                                        </div>
                                    )}

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
                                                The system will automatically scan Reddit and RSS feeds for matching posts and add them to your Google Sheet for review.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <Button size="lg" className="w-full h-12 shadow-md shadow-indigo-200 dark:shadow-none bg-indigo-600 hover:bg-indigo-700 font-bold" disabled={processing}>
                                    Save Content & Settings
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
