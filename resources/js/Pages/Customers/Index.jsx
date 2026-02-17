import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ customers, statuses, auth, flash }) {
    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this customer?')) {
            router.delete(`/customers/${id}`);
        }
    };

    const statusColors = {
        active: 'status-active',
        inactive: 'status-inactive',
        churned: 'status-churned',
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Customers" />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">Customers</h1>
                    <p className="page-subtitle">Manage your customers</p>
                </div>
                <Link href="/customers/create" className="btn btn-primary">
                    <i className="bi bi-plus-lg me-2"></i>
                    Add Customer
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
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Source</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {customers.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" className="text-center text-muted py-4">
                                            No customers found. Create your first customer or convert a lead.
                                        </td>
                                    </tr>
                                ) : (
                                    customers.data.map((customer) => (
                                        <tr key={customer.id}>
                                            <td>
                                                <Link href={`/customers/${customer.id}`} className="fw-medium text-decoration-none">
                                                    {customer.name}
                                                </Link>
                                            </td>
                                            <td>
                                                {customer.email && <div className="small">{customer.email}</div>}
                                                {customer.phone && <div className="small text-muted">{customer.phone}</div>}
                                                {!customer.email && !customer.phone && '-'}
                                            </td>
                                            <td>{customer.company || '-'}</td>
                                            <td>
                                                <span className={`status-badge ${statusColors[customer.status] || ''}`}>
                                                    {statuses[customer.status] || customer.status}
                                                </span>
                                            </td>
                                            <td>{customer.assignee?.name || '-'}</td>
                                            <td>
                                                {customer.converted_from_lead_id ? (
                                                    <span className="badge bg-info">From Lead</span>
                                                ) : (
                                                    <span className="badge bg-secondary">Direct</span>
                                                )}
                                            </td>
                                            <td>
                                                <div className="d-flex gap-1">
                                                    <Link
                                                        href={`/customers/${customer.id}`}
                                                        className="btn btn-action btn-outline-secondary"
                                                        title="View"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>
                                                    <Link
                                                        href={`/customers/${customer.id}/edit`}
                                                        className="btn btn-action btn-outline-primary"
                                                        title="Edit"
                                                    >
                                                        <i className="bi bi-pencil"></i>
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(customer.id)}
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

                {customers.last_page > 1 && (
                    <div className="card-footer">
                        <nav>
                            <ul className="pagination pagination-sm mb-0 justify-content-center">
                                {customers.links.map((link, index) => (
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
