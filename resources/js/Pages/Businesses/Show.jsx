import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ business, auth }) {
    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this business?')) {
            router.delete(`/businesses/${business.id}`);
        }
    };

    const statusColors = {
        active: 'status-active',
        inactive: 'status-inactive',
        pending: 'status-pending',
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title={business.name} />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">{business.name}</h1>
                    <p className="page-subtitle">Business Details</p>
                </div>
                <div className="d-flex gap-2">
                    <Link href="/businesses" className="btn btn-outline-secondary">
                        <i className="bi bi-arrow-left me-2"></i>
                        Back to List
                    </Link>
                    <Link href={`/businesses/${business.id}/edit`} className="btn btn-primary">
                        <i className="bi bi-pencil me-2"></i>
                        Edit
                    </Link>
                    <button onClick={handleDelete} className="btn btn-outline-danger">
                        <i className="bi bi-trash me-2"></i>
                        Delete
                    </button>
                </div>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Basic Information</h2>
                        </div>
                        <div className="card-body">
                            <div className="row g-4">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Business Name</label>
                                    <div className="fw-medium">{business.name}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Registration Number</label>
                                    <div>{business.registration_number || '-'}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Email</label>
                                    <div>
                                        <a href={`mailto:${business.email}`}>{business.email}</a>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Phone</label>
                                    <div>
                                        {business.phone ? (
                                            <a href={`tel:${business.phone}`}>{business.phone}</a>
                                        ) : '-'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Website</label>
                                    <div>
                                        {business.website ? (
                                            <a href={business.website} target="_blank" rel="noopener noreferrer">
                                                {business.website}
                                                <i className="bi bi-box-arrow-up-right ms-1 small"></i>
                                            </a>
                                        ) : '-'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Industry</label>
                                    <div>{business.industry || '-'}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Address</h2>
                        </div>
                        <div className="card-body">
                            {business.address || business.city || business.state || business.postal_code || business.country ? (
                                <address className="mb-0">
                                    {business.address && <div>{business.address}</div>}
                                    <div>
                                        {[business.city, business.state, business.postal_code]
                                            .filter(Boolean)
                                            .join(', ')}
                                    </div>
                                    {business.country && <div>{business.country}</div>}
                                </address>
                            ) : (
                                <span className="text-muted">No address provided</span>
                            )}
                        </div>
                    </div>
                </div>

                <div className="col-lg-4">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Status</h2>
                        </div>
                        <div className="card-body">
                            <span className={`status-badge ${statusColors[business.status]}`}>
                                {business.status.charAt(0).toUpperCase() + business.status.slice(1)}
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
                                <dd>{business.creator?.name || 'System'}</dd>
                                <dt className="text-muted small">Created At</dt>
                                <dd>{new Date(business.created_at).toLocaleString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(business.updated_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
