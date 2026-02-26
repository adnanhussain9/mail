import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children, title }: PropsWithChildren<{ title?: string }>) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-zinc-50 pt-6 sm:justify-center sm:pt-0 dark:bg-zinc-950">
            <Head title={title} />

            <div className="w-full sm:max-w-md">
                <Card className="border-none shadow-xl sm:border sm:border-zinc-200 dark:sm:border-zinc-800">
                    {title && (
                        <CardHeader className="space-y-1 text-center">
                            <CardTitle className="text-2xl font-bold tracking-tight">{title}</CardTitle>
                        </CardHeader>
                    )}
                    <CardContent className="pt-6">
                        {children}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
