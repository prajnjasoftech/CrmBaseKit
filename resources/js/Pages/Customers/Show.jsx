import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ customer, statuses, auth }) {
    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this customer?')) {
            router.delete(`/customers/${customer.id}`);
        }
    };

    const statusColors = {
        active: 'status-active',
        inactive: 'status-inactive',
        churned: 'status-churned',
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title={customer.name} />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">{customer.name}</h1>
                    <p className="page-subtitle">Customer Details</p>
                </div>
                <div className="d-flex gap-2">
                    <Link href="/customers" className="btn btn-outline-secondary">
                        <i className="bi bi-arrow-left me-2"></i>
                        Back to List
                    </Link>
                    <Link href={`/customers/${customer.id}/edit`} className="btn btn-primary">
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
                            <h2 className="card-title">Customer Information</h2>
                        </div>
                        <div className="card-body">
                            <div className="row g-4">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Name</label>
                                    <div className="fw-medium">{customer.name}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Company</label>
                                    <div>{customer.company || '-'}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Email</label>
                                    <div>
                                        {customer.email ? (
                                            <a href={`mailto:${customer.email}`}>{customer.email}</a>
                                        ) : '-'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Phone</label>
                                    <div>
                                        {customer.phone ? (
                                            <a href={`tel:${customer.phone}`}>{customer.phone}</a>
                                        ) : '-'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Assigned To</label>
                                    <div>{customer.assignee?.name || 'Unassigned'}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Associated Business</label>
                                    <div>
                                        {customer.business ? (
                                            <Link href={`/businesses/${customer.business.id}`}>
                                                {customer.business.name}
                                            </Link>
                                        ) : '-'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Address</h2>
                        </div>
                        <div className="card-body">
                            {customer.address || customer.city || customer.state || customer.postal_code || customer.country ? (
                                <address className="mb-0">
                                    {customer.address && <div>{customer.address}</div>}
                                    <div>
                                        {[customer.city, customer.state, customer.postal_code]
                                            .filter(Boolean)
                                            .join(', ')}
                                    </div>
                                    {customer.country && <div>{customer.country}</div>}
                                </address>
                            ) : (
                                <span className="text-muted">No address provided</span>
                            )}
                        </div>
                    </div>

                    {customer.notes && (
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Notes</h2>
                            </div>
                            <div className="card-body">
                                <p className="mb-0 white-space-pre-wrap">{customer.notes}</p>
                            </div>
                        </div>
                    )}

                    {customer.lead && (
                        <div className="admin-card">
                            <div className="card-header">
                                <h2 className="card-title">Original Lead</h2>
                            </div>
                            <div className="card-body">
                                <div className="d-flex align-items-center justify-content-between">
                                    <div>
                                        <i className="bi bi-arrow-right-circle text-info me-2"></i>
                                        This customer was converted from a lead.
                                    </div>
                                    <Link href={`/leads/${customer.lead.id}`} className="btn btn-outline-info btn-sm">
                                        View Original Lead
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="col-lg-4">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Status</h2>
                        </div>
                        <div className="card-body">
                            <span className={`status-badge ${statusColors[customer.status] || ''}`}>
                                {statuses[customer.status] || customer.status}
                            </span>
                        </div>
                    </div>

                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Source</h2>
                        </div>
                        <div className="card-body">
                            {customer.converted_from_lead_id ? (
                                <span className="badge bg-info">Converted from Lead</span>
                            ) : (
                                <span className="badge bg-secondary">Direct Customer</span>
                            )}
                        </div>
                    </div>

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Meta Information</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">Created At</dt>
                                <dd>{new Date(customer.created_at).toLocaleString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(customer.updated_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
