import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useState } from 'react';

export default function Users({ users }: any) {
    const [editingUser, setEditingUser] = useState<any>(null);
    const { data, setData, post, put, delete: destroy, reset, errors } = useForm({
        name: '',
        email: '',
        password: '',
        role: 'user',
        daily_email_limit: 100,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingUser) {
            put(route('admin.users.update', editingUser.id), {
                onSuccess: () => {
                    setEditingUser(null);
                    reset();
                }
            });
        } else {
            post(route('admin.users.store'), {
                onSuccess: () => {
                    reset();
                }
            });
        }
    };

    const handleEdit = (user: any) => {
        setEditingUser(user);
        setData({
            name: user.name,
            email: user.email,
            password: '',
            role: user.role,
            daily_email_limit: user.daily_email_limit,
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this user?')) {
            destroy(route('admin.users.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>}
        >
            <Head title="Manage Users" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <h3 className="text-lg font-medium">{editingUser ? 'Edit User' : 'Create New User'}</h3>
                        <form onSubmit={submit} className="mt-6 space-y-4 max-w-xl">
                            <div>
                                <Label>Name</Label>
                                <Input value={data.name} onChange={e => setData('name', e.target.value)} required />
                                {errors.name && <p className="text-red-500 text-xs">{errors.name}</p>}
                            </div>
                            <div>
                                <Label>Email</Label>
                                <Input type="email" value={data.email} onChange={e => setData('email', e.target.value)} required />
                                {errors.email && <p className="text-red-500 text-xs">{errors.email}</p>}
                            </div>
                            <div>
                                <Label>Password {editingUser && '(Leave blank to keep current)'}</Label>
                                <Input type="password" value={data.password} onChange={e => setData('password', e.target.value)} required={!editingUser} />
                                {errors.password && <p className="text-red-500 text-xs">{errors.password}</p>}
                            </div>
                            <div>
                                <Label>Role</Label>
                                <select className="border-gray-300 rounded-md w-full mt-1" value={data.role} onChange={e => setData('role', e.target.value)}>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div>
                                <Label>Daily Email Limit</Label>
                                <Input type="number" value={data.daily_email_limit} onChange={e => setData('daily_email_limit', Number(e.target.value))} required />
                            </div>
                            <div className="flex gap-2">
                                <Button type="submit">{editingUser ? 'Update User' : 'Create User'}</Button>
                                {editingUser && (
                                    <Button type="button" variant="outline" onClick={() => { setEditingUser(null); reset(); }}>Cancel</Button>
                                )}
                            </div>
                        </form>
                    </div>

                    <div className="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Limit</TableHead>
                                    <TableHead>Sent Today</TableHead>
                                    <TableHead>Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.data.map((user: any) => (
                                    <TableRow key={user.id}>
                                        <TableCell>{user.name}</TableCell>
                                        <TableCell>{user.email}</TableCell>
                                        <TableCell>{user.role}</TableCell>
                                        <TableCell>{user.daily_email_limit}</TableCell>
                                        <TableCell>{user.emails_sent_today}</TableCell>
                                        <TableCell className="space-x-2">
                                            <Button size="sm" variant="outline" onClick={() => handleEdit(user)}>Edit</Button>
                                            <Button size="sm" variant="destructive" onClick={() => handleDelete(user.id)}>Delete</Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
