import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ user, auth }) {
    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this user?')) {
            router.delete(`/users/${user.id}`);
        }
    };

    const roleColors = {
        'super-admin': 'bg-danger',
        'admin': 'bg-primary',
        'manager': 'bg-info',
        'sales': 'bg-success',
        'user': 'bg-secondary',
    };

    const isSelf = auth?.user?.id === user.id;

    return (
        <AdminLayout user={auth?.user}>
            <Head title={user.name} />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">{user.name}</h1>
                    <p className="page-subtitle">User Profile</p>
                </div>
                <div className="d-flex gap-2">
                    <Link href="/users" className="btn btn-outline-secondary">
                        <i className="bi bi-arrow-left me-2"></i>
                        Back to List
                    </Link>
                    <Link href={`/users/${user.id}/edit`} className="btn btn-primary">
                        <i className="bi bi-pencil me-2"></i>
                        Edit
                    </Link>
                    {!isSelf && (
                        <button onClick={handleDelete} className="btn btn-outline-danger">
                            <i className="bi bi-trash me-2"></i>
                            Delete
                        </button>
                    )}
                </div>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">User Information</h2>
                        </div>
                        <div className="card-body">
                            <div className="row g-4">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Full Name</label>
                                    <div className="fw-medium">{user.name}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Email Address</label>
                                    <div>
                                        <a href={`mailto:${user.email}`}>{user.email}</a>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Role</label>
                                    <div>
                                        {user.roles && user.roles.length > 0 ? (
                                            user.roles.map((role) => (
                                                <span
                                                    key={role.id}
                                                    className={`badge ${roleColors[role.name] || 'bg-secondary'}`}
                                                >
                                                    {role.name}
                                                </span>
                                            ))
                                        ) : (
                                            <span className="text-muted">No role assigned</span>
                                        )}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Email Verified</label>
                                    <div>
                                        {user.email_verified_at ? (
                                            <span className="badge bg-success">
                                                <i className="bi bi-check-circle me-1"></i>
                                                Verified
                                            </span>
                                        ) : (
                                            <span className="badge bg-warning text-dark">
                                                <i className="bi bi-exclamation-circle me-1"></i>
                                                Not Verified
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {isSelf && (
                        <div className="admin-card">
                            <div className="card-header">
                                <h2 className="card-title">Security</h2>
                            </div>
                            <div className="card-body">
                                <p className="text-muted mb-3">
                                    Keep your account secure by using a strong password.
                                </p>
                                <Link href={`/users/${user.id}/edit`} className="btn btn-outline-primary">
                                    <i className="bi bi-shield-lock me-2"></i>
                                    Change Password
                                </Link>
                            </div>
                        </div>
                    )}
                </div>

                <div className="col-lg-4">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Account Status</h2>
                        </div>
                        <div className="card-body text-center py-4">
                            <div className="avatar-placeholder mb-3">
                                <i className="bi bi-person-circle display-1 text-muted"></i>
                            </div>
                            <h5 className="mb-1">{user.name}</h5>
                            {user.roles && user.roles.length > 0 && (
                                <span className={`badge ${roleColors[user.roles[0].name] || 'bg-secondary'}`}>
                                    {user.roles[0].name}
                                </span>
                            )}
                        </div>
                    </div>

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Meta Information</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">User ID</dt>
                                <dd>{user.id}</dd>
                                <dt className="text-muted small">Created At</dt>
                                <dd>{new Date(user.created_at).toLocaleString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(user.updated_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
