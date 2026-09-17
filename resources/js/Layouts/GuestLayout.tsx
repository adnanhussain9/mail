import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, usePage } from '@inertiajs/react';
import { PropsWithChildren, useEffect } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'sonner';

interface GuestProps {
    title?: string;
    maxWidth?: string;
    showCard?: boolean;
}

export default function Guest({
    children,
    title,
    maxWidth = 'sm:max-w-md',
    showCard = true,
}: PropsWithChildren<GuestProps>) {
    const flash: any = usePage().props.flash;

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    return (
        <div className="flex min-h-screen flex-col items-center bg-zinc-50/50 dark:bg-zinc-950 pt-6 sm:justify-center sm:py-12 px-4">
            <Toaster richColors position="top-right" />
            <Head title={title} />

            <div className={`w-full ${maxWidth}`}>
                {showCard ? (
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
                ) : (
                    children
                )}
            </div>
        </div>
    );
}

