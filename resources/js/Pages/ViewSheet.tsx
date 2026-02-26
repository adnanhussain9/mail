import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ExternalLink, FileSpreadsheet, RefreshCcw } from 'lucide-react';

export default function ViewSheet({ rows, sheetName }: { rows: string[][]; sheetName: string }) {
    // Separate header and data
    const header = rows.length > 0 ? rows[0] : [];
    const data = rows.length > 1 ? rows.slice(1) : [];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between px-2">
                    <div className="flex items-center gap-2 sm:gap-4">
                        <Button variant="ghost" size="icon" asChild className="shrink-0">
                            <Link href={route('dashboard')}>
                                <ChevronLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <h2 className="text-xl sm:text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2 truncate">
                            <FileSpreadsheet className="h-5 w-5 sm:h-6 sm:w-6 text-emerald-500 shrink-0" />
                            <span className="truncate">{sheetName}</span>
                        </h2>
                    </div>
                    <div className="flex items-center gap-2">
                        {import.meta.env.VITE_GOOGLE_SHEET_URL && (
                            <Button variant="outline" asChild className="gap-2 flex-1 sm:flex-none py-1 h-9">
                                <a href={import.meta.env.VITE_GOOGLE_SHEET_URL} target="_blank" rel="noopener noreferrer">
                                    <ExternalLink className="h-4 w-4" />
                                    <span className="hidden xs:inline">Open External</span>
                                    <span className="xs:hidden">External</span>
                                </a>
                            </Button>
                        )}
                        <Button variant="outline" onClick={() => window.location.reload()} className="gap-2 flex-1 sm:flex-none py-1 h-9">
                            <RefreshCcw className="h-4 w-4" />
                            <span className="hidden xs:inline">Refresh Data</span>
                            <span className="xs:hidden">Refresh</span>
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title={`View Sheet - ${sheetName}`} />

            <div className="py-4 sm:py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Card className="shadow-xl overflow-hidden border-zinc-200 dark:border-zinc-800">
                        <CardHeader className="bg-zinc-50 dark:bg-zinc-900 border-b py-3 px-4">
                            <CardTitle className="text-xs sm:text-sm font-medium text-zinc-500 flex items-center gap-2">
                                Raw Data
                                <span className="text-[10px] bg-zinc-200 dark:bg-zinc-800 px-2 py-0.5 rounded text-zinc-600 font-bold">
                                    {data.length} Rows
                                </span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-0 overflow-auto max-h-[60vh] sm:max-h-[70vh]">
                            {rows.length === 0 ? (
                                <div className="text-center py-16 sm:py-20 bg-zinc-50/50 px-4">
                                    <p className="text-zinc-500 font-medium text-sm">This sheet appears to be empty.</p>
                                </div>
                            ) : (
                                <div className="min-w-full inline-block align-middle">
                                    <div className="overflow-x-auto">
                                        <Table className="min-w-[600px]">
                                            <TableHeader className="bg-zinc-100 dark:bg-zinc-800 sticky top-0 z-10">
                                                <TableRow>
                                                    {header.map((col, i) => (
                                                        <TableHead key={i} className="font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider text-[10px] py-3">
                                                            {col}
                                                        </TableHead>
                                                    ))}
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {data.map((row, rowIndex) => (
                                                    <TableRow key={rowIndex} className="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                                        {header.map((_, colIndex) => (
                                                            <TableCell key={colIndex} className="py-3 px-4 text-xs sm:text-sm">
                                                                {row[colIndex] || <span className="text-zinc-300 italic text-[10px]">empty</span>}
                                                            </TableCell>
                                                        ))}
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
