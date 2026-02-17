import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Create({ roles, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/users');
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Create User" />

            <div className="page-header">
                <h1 className="page-title">Create User</h1>
                <p className="page-subtitle">Add a new user to the system</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <form onSubmit={handleSubmit}>
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">User Information</h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">Full Name *</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Email Address *</label>
                                        <input
                                            type="email"
                                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                        />
                                        {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Password *</label>
                                        <input
                                            type="password"
                                            className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                        />
                                        {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                                        <div className="form-text">
                                            Min 8 characters with uppercase, lowercase, and numbers
                                        </div>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Confirm Password *</label>
                                        <input
                                            type="password"
                                            className={`form-control ${errors.password_confirmation ? 'is-invalid' : ''}`}
                                            value={data.password_confirmation}
                                            onChange={(e) => setData('password_confirmation', e.target.value)}
                                        />
                                        {errors.password_confirmation && (
                                            <div className="invalid-feedback">{errors.password_confirmation}</div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Role Assignment</h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">Role</label>
                                        <select
                                            className={`form-select ${errors.role ? 'is-invalid' : ''}`}
                                            value={data.role}
                                            onChange={(e) => setData('role', e.target.value)}
                                        >
                                            <option value="">Select a role</option>
                                            {roles.map((role) => (
                                                <option key={role.id} value={role.name}>
                                                    {role.name}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.role && <div className="invalid-feedback">{errors.role}</div>}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="d-flex justify-content-end gap-2">
                            <Link href="/users" className="btn btn-outline-secondary">
                                Cancel
                            </Link>
                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                {processing ? 'Creating...' : 'Create User'}
                            </button>
                        </div>
                    </form>
                </div>

                <div className="col-lg-4">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Role Permissions</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-danger">super-admin</dt>
                                <dd className="small text-muted">Full system access</dd>
                                <dt className="text-primary">admin</dt>
                                <dd className="small text-muted">Manage users, businesses, leads, customers, news</dd>
                                <dt className="text-info">manager</dt>
                                <dd className="small text-muted">View users, manage businesses, leads, customers</dd>
                                <dt className="text-success">sales</dt>
                                <dd className="small text-muted">Manage leads and customers</dd>
                                <dt className="text-secondary">user</dt>
                                <dd className="small text-muted mb-0">View-only access</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
