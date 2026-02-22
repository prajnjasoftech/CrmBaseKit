import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ service, auth }) {
    const { auth: { user } } = usePage().props;
    const can = (permission) => user?.permissions?.includes(permission) ?? false;

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this service?')) {
            router.delete(`/services/${service.id}`);
        }
    };

    const statusColors = {
        active: 'status-active',
        inactive: 'status-inactive',
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title={service.name} />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">{service.name}</h1>
                    <p className="page-subtitle">Service Details</p>
                </div>
                <div className="d-flex gap-2">
                    <Link href="/services" className="btn btn-outline-secondary">
                        <i className="bi bi-arrow-left me-2"></i>
                        Back to List
                    </Link>
                    {can('edit services') && (
                        <Link href={`/services/${service.id}/edit`} className="btn btn-primary">
                            <i className="bi bi-pencil me-2"></i>
                            Edit
                        </Link>
                    )}
                    {can('delete services') && (
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
                            <h2 className="card-title">Service Information</h2>
                        </div>
                        <div className="card-body">
                            <div className="row g-4">
                                <div className="col-12">
                                    <label className="form-label text-muted small mb-1">Service Name</label>
                                    <div className="fw-medium">{service.name}</div>
                                </div>
                                <div className="col-12">
                                    <label className="form-label text-muted small mb-1">Description</label>
                                    <div className="white-space-pre-wrap">
                                        {service.description || <span className="text-muted">No description provided</span>}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-lg-4">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Status</h2>
                        </div>
                        <div className="card-body">
                            <span className={`status-badge ${statusColors[service.status]}`}>
                                {service.status.charAt(0).toUpperCase() + service.status.slice(1)}
                            </span>
                        </div>
                    </div>

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Meta Information</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">Created By</dt>
                                <dd>{service.creator?.name || 'System'}</dd>
                                <dt className="text-muted small">Created At</dt>
                                <dd>{new Date(service.created_at).toLocaleString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(service.updated_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
