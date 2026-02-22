import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ users, auth, filters }) {
    const { auth: { user } } = usePage().props;
    const can = (permission) => user?.permissions?.includes(permission) ?? false;
    const [search, setSearch] = useState(filters?.search || '');

    const handleDelete = (userId) => {
        if (confirm('Are you sure you want to delete this user?')) {
            router.delete(`/users/${userId}`);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/users', { search }, { preserveState: true, preserveScroll: true });
    };

    const clearSearch = () => {
        setSearch('');
        router.get('/users', {}, { preserveState: true, preserveScroll: true });
    };

    const roleColors = {
        'super-admin': 'bg-danger',
        'admin': 'bg-primary',
        'manager': 'bg-info',
        'sales': 'bg-success',
        'user': 'bg-secondary',
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Users" />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">Users</h1>
                    <p className="page-subtitle">Manage system users and their roles</p>
                </div>
{can('create users') && (
                    <Link href="/users/create" className="btn btn-primary">
                        <i className="bi bi-plus-lg me-2"></i>
                        Add User
                    </Link>
                )}
            </div>

            <div className="admin-card mb-3">
                <div className="card-body">
                    <form onSubmit={handleSearch} className="d-flex gap-2">
                        <div className="input-group">
                            <span className="input-group-text">
                                <i className="bi bi-search"></i>
                            </span>
                            <input
                                type="text"
                                className="form-control"
                                placeholder="Search by name or email..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                            {search && (
                                <button type="button" className="btn btn-outline-secondary" onClick={clearSearch}>
                                    <i className="bi bi-x-lg"></i>
                                </button>
                            )}
                        </div>
                        <button type="submit" className="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>

            <div className="admin-card">
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th className="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.data.map((userItem) => (
                                    <tr key={userItem.id}>
                                        <td>
                                            <Link href={`/users/${userItem.id}`} className="text-decoration-none">
                                                {userItem.name}
                                            </Link>
                                        </td>
                                        <td>{userItem.email}</td>
                                        <td>
                                            {userItem.roles && userItem.roles.length > 0 ? (
                                                userItem.roles.map((role) => (
                                                    <span
                                                        key={role.id}
                                                        className={`badge ${roleColors[role.name] || 'bg-secondary'} me-1`}
                                                    >
                                                        {role.name}
                                                    </span>
                                                ))
                                            ) : (
                                                <span className="badge bg-light text-muted">No role</span>
                                            )}
                                        </td>
                                        <td>{new Date(userItem.created_at).toLocaleDateString()}</td>
                                        <td className="text-end">
                                            <div className="btn-group btn-group-sm">
                                                <Link
                                                    href={`/users/${userItem.id}`}
                                                    className="btn btn-outline-secondary"
                                                >
                                                    <i className="bi bi-eye"></i>
                                                </Link>
                                                {can('edit users') && (
                                                    <Link
                                                        href={`/users/${userItem.id}/edit`}
                                                        className="btn btn-outline-secondary"
                                                    >
                                                        <i className="bi bi-pencil"></i>
                                                    </Link>
                                                )}
                                                {can('delete users') && (
                                                    <button
                                                        onClick={() => handleDelete(userItem.id)}
                                                        className="btn btn-outline-danger"
                                                    >
                                                        <i className="bi bi-trash"></i>
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}

                                {users.data.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="text-center py-4 text-muted">
                                            No users found. Create your first user.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {users.last_page > 1 && (
                    <div className="card-footer d-flex justify-content-between align-items-center">
                        <small className="text-muted">
                            Showing {users.from} to {users.to} of {users.total} users
                        </small>
                        <nav>
                            <ul className="pagination pagination-sm mb-0">
                                {users.links.map((link, index) => (
                                    <li
                                        key={index}
                                        className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}
                                    >
                                        <Link
                                            href={link.url || '#'}
                                            className="page-link"
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    </li>
                                ))}
                            </ul>
                        </nav>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
