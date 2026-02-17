import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ leads, statuses, sources, auth, flash }) {
    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this lead?')) {
            router.delete(`/leads/${id}`);
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

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Leads" />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">Leads</h1>
                    <p className="page-subtitle">Manage your sales leads</p>
                </div>
                <Link href="/leads/create" className="btn btn-primary">
                    <i className="bi bi-plus-lg me-2"></i>
                    Add Lead
                </Link>
            </div>

            {flash?.success && (
                <div className="alert alert-success alert-dismissible fade show" role="alert">
                    {flash.success}
                    <button type="button" className="btn-close" data-bs-dismiss="alert"></button>
                </div>
            )}

            <div className="admin-card">
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Company</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {leads.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" className="text-center text-muted py-4">
                                            No leads found. Create your first lead.
                                        </td>
                                    </tr>
                                ) : (
                                    leads.data.map((lead) => (
                                        <tr key={lead.id}>
                                            <td>
                                                <Link href={`/leads/${lead.id}`} className="fw-medium text-decoration-none">
                                                    {lead.name}
                                                </Link>
                                            </td>
                                            <td>
                                                {lead.email && <div className="small">{lead.email}</div>}
                                                {lead.phone && <div className="small text-muted">{lead.phone}</div>}
                                                {!lead.email && !lead.phone && '-'}
                                            </td>
                                            <td>{lead.company || '-'}</td>
                                            <td>
                                                <span className="badge bg-secondary">
                                                    {sources[lead.source] || lead.source}
                                                </span>
                                            </td>
                                            <td>
                                                <span className={`status-badge ${statusColors[lead.status] || ''}`}>
                                                    {statuses[lead.status] || lead.status}
                                                </span>
                                            </td>
                                            <td>{lead.assignee?.name || '-'}</td>
                                            <td>
                                                <div className="d-flex gap-1">
                                                    <Link
                                                        href={`/leads/${lead.id}`}
                                                        className="btn btn-action btn-outline-secondary"
                                                        title="View"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>
                                                    <Link
                                                        href={`/leads/${lead.id}/edit`}
                                                        className="btn btn-action btn-outline-primary"
                                                        title="Edit"
                                                    >
                                                        <i className="bi bi-pencil"></i>
                                                    </Link>
                                                    {lead.status === 'won' && !lead.customer && (
                                                        <Link
                                                            href={`/leads/${lead.id}/convert`}
                                                            className="btn btn-action btn-outline-success"
                                                            title="Convert to Customer"
                                                        >
                                                            <i className="bi bi-person-check"></i>
                                                        </Link>
                                                    )}
                                                    <button
                                                        onClick={() => handleDelete(lead.id)}
                                                        className="btn btn-action btn-outline-danger"
                                                        title="Delete"
                                                    >
                                                        <i className="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {leads.last_page > 1 && (
                    <div className="card-footer">
                        <nav>
                            <ul className="pagination pagination-sm mb-0 justify-content-center">
                                {leads.links.map((link, index) => (
                                    <li key={index} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                                        <Link
                                            href={link.url || '#'}
                                            className="page-link"
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
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
