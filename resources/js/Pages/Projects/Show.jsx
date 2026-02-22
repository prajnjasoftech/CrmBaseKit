import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ customer, project, statuses, auth }) {
    const { auth: { user } } = usePage().props;
    const can = (permission) => user?.permissions?.includes(permission) ?? false;

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this project?')) {
            router.delete(`/customers/${customer.id}/projects/${project.id}`);
        }
    };

    const statusColors = {
        pending: 'status-pending',
        in_progress: 'status-active',
        on_hold: 'status-inactive',
        completed: 'status-active',
        cancelled: 'status-inactive',
    };

    const formatCurrency = (amount) => {
        if (!amount) return '-';
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const formatDate = (date) => {
        if (!date) return '-';
        return new Date(date).toLocaleDateString();
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title={project.name} />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">{project.name}</h1>
                    <p className="page-subtitle">Project for {customer.name}</p>
                </div>
                <div className="d-flex gap-2">
                    <Link href={`/customers/${customer.id}`} className="btn btn-outline-secondary">
                        <i className="bi bi-arrow-left me-2"></i>
                        Back to Customer
                    </Link>
                    {can('edit projects') && (
                        <Link href={`/customers/${customer.id}/projects/${project.id}/edit`} className="btn btn-primary">
                            <i className="bi bi-pencil me-2"></i>
                            Edit
                        </Link>
                    )}
                    {can('delete projects') && (
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
                            <h2 className="card-title">Project Information</h2>
                        </div>
                        <div className="card-body">
                            <div className="row g-4">
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Project Name</label>
                                    <div className="fw-medium">{project.name}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Service</label>
                                    <div>
                                        {project.service ? (
                                            <Link href={`/services/${project.service.id}`}>
                                                {project.service.name}
                                            </Link>
                                        ) : '-'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Assigned To</label>
                                    <div>{project.assignee?.name || 'Unassigned'}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Budget</label>
                                    <div>{formatCurrency(project.budget)}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">Start Date</label>
                                    <div>{formatDate(project.start_date)}</div>
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label text-muted small mb-1">End Date</label>
                                    <div>{formatDate(project.end_date)}</div>
                                </div>
                                {project.description && (
                                    <div className="col-12">
                                        <label className="form-label text-muted small mb-1">Description</label>
                                        <div className="white-space-pre-wrap">{project.description}</div>
                                    </div>
                                )}
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
                            <span className={`status-badge ${statusColors[project.status] || ''}`}>
                                {statuses[project.status] || project.status}
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
                                <dd>{project.creator?.name || 'System'}</dd>
                                <dt className="text-muted small">Created At</dt>
                                <dd>{new Date(project.created_at).toLocaleString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(project.updated_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
