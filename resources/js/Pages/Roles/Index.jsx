import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { useState } from 'react';

export default function Index({ roles, filters, auth }) {
    const { auth: { user } } = usePage().props;
    const can = (permission) => user?.permissions?.includes(permission) ?? false;

    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/roles', { search }, { preserveState: true });
    };

    const handleDelete = (role) => {
        if (confirm(`Are you sure you want to delete the "${role.name}" role?`)) {
            router.delete(`/roles/${role.id}`);
        }
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Roles" />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">Roles</h1>
                    <p className="page-subtitle">Manage user roles and permissions</p>
                </div>
                {can('create roles') && (
                    <Link href="/roles/create" className="btn btn-primary">
                        <i className="bi bi-plus me-2"></i>
                        Add Role
                    </Link>
                )}
            </div>

            <div className="admin-card">
                <div className="card-header">
                    <form onSubmit={handleSearch} className="d-flex gap-2">
                        <input
                            type="text"
                            className="form-control"
                            placeholder="Search roles..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            style={{ maxWidth: '300px' }}
                        />
                        <button type="submit" className="btn btn-outline-primary">
                            <i className="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Role Name</th>
                                    <th>Permissions</th>
                                    <th>Users</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {roles.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="4" className="text-center text-muted py-4">
                                            No roles found.
                                        </td>
                                    </tr>
                                ) : (
                                    roles.data.map((role) => (
                                        <tr key={role.id}>
                                            <td>
                                                <Link href={`/roles/${role.id}`} className="fw-medium text-decoration-none">
                                                    {role.name}
                                                </Link>
                                                {role.name === 'super-admin' && (
                                                    <span className="badge bg-danger ms-2">System</span>
                                                )}
                                            </td>
                                            <td>
                                                <span className="badge bg-secondary">
                                                    {role.permissions_count} permissions
                                                </span>
                                            </td>
                                            <td>
                                                <span className="badge bg-info">
                                                    {role.users_count} users
                                                </span>
                                            </td>
                                            <td className="text-end">
                                                <div className="btn-group btn-group-sm">
                                                    {can('view roles') && (
                                                        <Link href={`/roles/${role.id}`} className="btn btn-outline-primary">
                                                            View
                                                        </Link>
                                                    )}
                                                    {can('edit roles') && (
                                                        <Link href={`/roles/${role.id}/edit`} className="btn btn-outline-secondary">
                                                            Edit
                                                        </Link>
                                                    )}
                                                    {can('delete roles') && !['super-admin', 'admin', 'user'].includes(role.name) && (
                                                        <button
                                                            onClick={() => handleDelete(role)}
                                                            className="btn btn-outline-danger"
                                                        >
                                                            Delete
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
                {roles.last_page > 1 && (
                    <div className="card-footer d-flex justify-content-between align-items-center">
                        <small className="text-muted">
                            Showing {roles.from} to {roles.to} of {roles.total} roles
                        </small>
                        <nav>
                            <ul className="pagination pagination-sm mb-0">
                                {roles.links.map((link, index) => (
                                    <li key={index} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                                        {link.url ? (
                                            <Link
                                                href={link.url}
                                                className="page-link"
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : (
                                            <span className="page-link" dangerouslySetInnerHTML={{ __html: link.label }} />
                                        )}
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
