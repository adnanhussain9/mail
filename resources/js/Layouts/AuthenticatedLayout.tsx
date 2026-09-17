import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState, useEffect } from 'react';
import { ShieldCheck, Settings, FileSpreadsheet, Play } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'sonner';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user: any = usePage().props.auth.user;
    const flash: any = usePage().props.flash;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash]);

    const processSheet = () => {
        router.post(route('process.sheet'), {}, {
            preserveScroll: true,
        });
    };

    return (
        <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950">
            <Toaster richColors position="top-right" />
            <nav className="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 sticky top-0 z-50">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href="/" className="text-xl font-bold tracking-tighter text-indigo-600 dark:text-indigo-400">
                                    MailAuto
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    href={route('dashboard')}
                                    active={route().current('dashboard')}
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    href={route('sheet.view')}
                                    active={route().current('sheet.view')}
                                >
                                    View Sheet
                                </NavLink>
                                <NavLink
                                    href={route('email.content')}
                                    active={route().current('email.content')}
                                >
                                    Email Template
                                </NavLink>
                                <NavLink
                                    href={route('configuration')}
                                    active={route().current('configuration')}
                                >
                                    Configuration
                                </NavLink>
                                {user?.role === 'admin' && (
                                    <NavLink
                                        href={route('admin.users.index')}
                                        active={route().current('admin.users.index')}
                                    >
                                        Admin Panel
                                    </NavLink>
                                )}
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center space-x-4">
                            {route().current('dashboard') && (
                                <Button size="sm" onClick={processSheet} className="gap-2 bg-indigo-600 hover:bg-indigo-700 font-bold shadow-md text-white">
                                    <Play className="h-4 w-4 fill-current" />
                                    Run Processor
                                </Button>
                            )}

                            <div className="relative ms-3 border-l pl-4 dark:border-zinc-800">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-zinc-900 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                {user.name}
                                                <svg
                                                    className="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link
                                            href={route('profile.edit')}
                                        >
                                            Profile
                                        </Dropdown.Link>
                                        <Dropdown.Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            {route().current('dashboard') && (
                                <Button size="sm" onClick={processSheet} className="mr-2 gap-1 bg-indigo-600 hover:bg-indigo-700 font-bold shadow-md px-2 text-white">
                                    <Play className="h-4 w-4 fill-current" />
                                    Run
                                </Button>
                            )}
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown(
                                        (previousState) => !previousState,
                                    )
                                }
                                className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-zinc-800 dark:hover:text-gray-400 dark:focus:bg-zinc-800 dark:focus:text-gray-400"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={
                                            !showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={
                                            showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    className={
                        (showingNavigationDropdown ? 'block' : 'hidden') +
                        ' sm:hidden'
                    }
                >
                    <div className="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            href={route('sheet.view')}
                            active={route().current('sheet.view')}
                        >
                            View Sheet
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            href={route('email.content')}
                            active={route().current('email.content')}
                        >
                            Email Template
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            href={route('configuration')}
                            active={route().current('configuration')}
                        >
                            Configuration
                        </ResponsiveNavLink>
                        {user?.role === 'admin' && (
                            <ResponsiveNavLink
                                href={route('admin.users.index')}
                                active={route().current('admin.users.index')}
                            >
                                Admin Panel
                            </ResponsiveNavLink>
                        )}
                    </div>

                    <div className="border-t border-gray-200 pb-1 pt-4 dark:border-zinc-800">
                        <div className="px-4">
                            <div className="text-base font-medium text-gray-800 dark:text-gray-200">
                                {user.name}
                            </div>
                            <div className="text-sm font-medium text-gray-500">
                                {user.email}
                            </div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route('logout')}
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white border-b border-zinc-200 shadow-sm dark:bg-zinc-900 dark:border-zinc-800 relative z-10">
                    <div className="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
