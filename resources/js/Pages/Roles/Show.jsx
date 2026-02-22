import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ role, groupedPermissions, auth }) {
    const { auth: { user } } = usePage().props;
    const can = (permission) => user?.permissions?.includes(permission) ?? false;

    const handleDelete = () => {
        if (confirm(`Are you sure you want to delete the "${role.name}" role?`)) {
            router.delete(`/roles/${role.id}`);
        }
    };

    const isSystemRole = ['super-admin', 'admin', 'user'].includes(role.name);

    return (
        <AdminLayout user={auth?.user}>
            <Head title={`Role: ${role.name}`} />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">{role.name}</h1>
                    <p className="page-subtitle">Role Details</p>
                </div>
                <div className="d-flex gap-2">
                    <Link href="/roles" className="btn btn-outline-secondary">
                        <i className="bi bi-arrow-left me-2"></i>
                        Back to List
                    </Link>
                    {can('edit roles') && (
                        <Link href={`/roles/${role.id}/edit`} className="btn btn-primary">
                            <i className="bi bi-pencil me-2"></i>
                            Edit
                        </Link>
                    )}
                    {can('delete roles') && !isSystemRole && (
                        <button onClick={handleDelete} className="btn btn-outline-danger">
                            <i className="bi bi-trash me-2"></i>
                            Delete
                        </button>
                    )}
                </div>
            </div>

            <div className="row">
                <div className="col-lg-4">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Role Information</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">Role Name</dt>
                                <dd className="mb-3">
                                    {role.name}
                                    {role.name === 'super-admin' && (
                                        <span className="badge bg-danger ms-2">System</span>
                                    )}
                                </dd>

                                <dt className="text-muted small">Users with this Role</dt>
                                <dd className="mb-3">
                                    <span className="badge bg-info">{role.users_count} users</span>
                                </dd>

                                <dt className="text-muted small">Total Permissions</dt>
                                <dd className="mb-3">
                                    <span className="badge bg-secondary">{role.permissions.length} permissions</span>
                                </dd>

                                <dt className="text-muted small">Created At</dt>
                                <dd className="mb-3">{new Date(role.created_at).toLocaleString()}</dd>

                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(role.updated_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div className="col-lg-8">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Permissions</h2>
                        </div>
                        <div className="card-body">
                            {Object.entries(groupedPermissions).map(([module, modulePermissions]) => {
                                const hasAny = modulePermissions.some(p => role.permissions.includes(p));
                                if (!hasAny) return null;

                                return (
                                    <div key={module} className="mb-4">
                                        <h6 className="fw-bold mb-2">{module}</h6>
                                        <div className="d-flex flex-wrap gap-2">
                                            {modulePermissions.map((permission) => (
                                                <span
                                                    key={permission}
                                                    className={`badge ${role.permissions.includes(permission) ? 'bg-success' : 'bg-light text-muted'}`}
                                                >
                                                    {permission}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}

                            {role.permissions.length === 0 && (
                                <p className="text-muted mb-0">No permissions assigned to this role.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
