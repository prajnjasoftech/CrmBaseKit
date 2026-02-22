import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Edit({ role, permissions, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        name: role.name,
        permissions: role.permissions || [],
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/roles/${role.id}`);
    };

    const togglePermission = (permission) => {
        if (data.permissions.includes(permission)) {
            setData('permissions', data.permissions.filter(p => p !== permission));
        } else {
            setData('permissions', [...data.permissions, permission]);
        }
    };

    const toggleModule = (modulePermissions) => {
        const allSelected = modulePermissions.every(p => data.permissions.includes(p));
        if (allSelected) {
            setData('permissions', data.permissions.filter(p => !modulePermissions.includes(p)));
        } else {
            const newPermissions = [...new Set([...data.permissions, ...modulePermissions])];
            setData('permissions', newPermissions);
        }
    };

    const isSystemRole = role.name === 'super-admin';

    return (
        <AdminLayout user={auth?.user}>
            <Head title={`Edit Role: ${role.name}`} />

            <div className="page-header">
                <h1 className="page-title">Edit Role</h1>
                <p className="page-subtitle">Modify role permissions</p>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="row">
                    <div className="col-lg-4">
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Role Details</h2>
                            </div>
                            <div className="card-body">
                                <div className="mb-3">
                                    <label className="form-label">Role Name *</label>
                                    <input
                                        type="text"
                                        className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g., editor, viewer"
                                        disabled={isSystemRole}
                                    />
                                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    {isSystemRole && (
                                        <small className="text-muted">System role name cannot be changed.</small>
                                    )}
                                </div>

                                <div className="d-flex gap-2">
                                    <Link href="/roles" className="btn btn-outline-secondary">
                                        Cancel
                                    </Link>
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save Changes'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="admin-card">
                            <div className="card-header d-flex justify-content-between align-items-center">
                                <h2 className="card-title mb-0">Permissions</h2>
                                <small className="text-muted">{data.permissions.length} selected</small>
                            </div>
                            <div className="card-body">
                                {Object.entries(permissions).map(([module, modulePermissions]) => (
                                    <div key={module} className="mb-4">
                                        <div className="d-flex align-items-center mb-2">
                                            <input
                                                type="checkbox"
                                                className="form-check-input me-2"
                                                id={`module-${module}`}
                                                checked={modulePermissions.every(p => data.permissions.includes(p))}
                                                onChange={() => toggleModule(modulePermissions)}
                                            />
                                            <label htmlFor={`module-${module}`} className="form-label mb-0 fw-bold">
                                                {module}
                                            </label>
                                        </div>
                                        <div className="ms-4 row">
                                            {modulePermissions.map((permission) => (
                                                <div key={permission} className="col-md-6 col-lg-4">
                                                    <div className="form-check">
                                                        <input
                                                            type="checkbox"
                                                            className="form-check-input"
                                                            id={`perm-${permission}`}
                                                            checked={data.permissions.includes(permission)}
                                                            onChange={() => togglePermission(permission)}
                                                        />
                                                        <label className="form-check-label" htmlFor={`perm-${permission}`}>
                                                            {permission}
                                                        </label>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
