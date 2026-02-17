import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ lead, statuses, sources, auth }) {
    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this lead?')) {
            router.delete(`/leads/${lead.id}`);
        }
    };

    const statusColors = {
        new: 'status-new',
        contacted: 'status-pending',
        qualified: 'status-qualified',
        proposal: 'status-proposal',
        negotiation: 'status-negotiation',
        won: 'status-active',
        lost: 'status-inactive',
    };

    const canConvert = lead.status === 'won' && !lead.customer;

    return (
        <AdminLayout user={auth?.user}>
            <Head title={lead.name} />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">{lead.name}</h1>
                    <p className="page-subtitle">Lead Details</p>
                </div>
                <div className="d-flex gap-2">
                    <Link href="/leads" className="btn btn-outline-secondary">
                        <i className="bi bi-arrow-left me-2"></i>
                        Back to List
                    </Link>
                    <Link href={`/leads/${lead.id}/edit`} className="btn btn-primary">
                        <i className="bi bi-pencil me-2"></i>
                        Edit
                    </Link>
                    {canConvert && (
                        <Link href={`/leads/${lead.id}/convert`} className="btn btn-success">
                            <i className="bi bi-person-check me-2"></i>
                            Convert to Customer
                        </Link>
                    )}
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
                            <h2 className="card-title">Lead Information</h2>
                        </div>
                        <div className="card-body">
                            <div className="row g-4">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Name</label>
                                    <div className="fw-medium">{lead.name}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Company</label>
                                    <div>{lead.company || '-'}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Email</label>
                                    <div>
                                        {lead.email ? (
                                            <a href={`mailto:${lead.email}`}>{lead.email}</a>
                                        ) : '-'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Phone</label>
                                    <div>
                                        {lead.phone ? (
                                            <a href={`tel:${lead.phone}`}>{lead.phone}</a>
                                        ) : '-'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Source</label>
                                    <div>
                                        <span className="badge bg-secondary">
                                            {sources[lead.source] || lead.source}
                                        </span>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Assigned To</label>
                                    <div>{lead.assignee?.name || 'Unassigned'}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Associated Business</label>
                                    <div>
                                        {lead.business ? (
                                            <Link href={`/businesses/${lead.business.id}`}>
                                                {lead.business.name}
                                            </Link>
                                        ) : '-'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {lead.notes && (
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Notes</h2>
                            </div>
                            <div className="card-body">
                                <p className="mb-0 white-space-pre-wrap">{lead.notes}</p>
                            </div>
                        </div>
                    )}

                    {lead.customer && (
                        <div className="admin-card">
                            <div className="card-header">
                                <h2 className="card-title">Converted Customer</h2>
                            </div>
                            <div className="card-body">
                                <div className="d-flex align-items-center justify-content-between">
                                    <div>
                                        <i className="bi bi-check-circle-fill text-success me-2"></i>
                                        This lead has been converted to a customer.
                                    </div>
                                    <Link href={`/customers/${lead.customer.id}`} className="btn btn-outline-primary btn-sm">
                                        View Customer
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
                            <span className={`status-badge ${statusColors[lead.status] || ''}`}>
                                {statuses[lead.status] || lead.status}
                            </span>
                        </div>
                    </div>

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Meta Information</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">Created At</dt>
                                <dd>{new Date(lead.created_at).toLocaleString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(lead.updated_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
