import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ businesses, auth, flash, filters }) {
    const [search, setSearch] = useState(filters?.search || '');

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this business?')) {
            router.delete(`/businesses/${id}`);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/businesses', { search }, { preserveState: true, preserveScroll: true });
    };

    const clearSearch = () => {
        setSearch('');
        router.get('/businesses', {}, { preserveState: true, preserveScroll: true });
    };

    const statusColors = {
        active: 'status-active',
        inactive: 'status-inactive',
        pending: 'status-pending',
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Businesses" />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">Businesses</h1>
                    <p className="page-subtitle">Manage registered businesses</p>
                </div>
                <Link href="/businesses/create" className="btn btn-primary">
                    <i className="bi bi-plus-lg me-2"></i>
                    Add Business
                </Link>
            </div>

            {flash?.success && (
                <div className="alert alert-success alert-dismissible fade show" role="alert">
                    {flash.success}
                    <button type="button" className="btn-close" data-bs-dismiss="alert"></button>
                </div>
            )}

            <div className="admin-card mb-3">
                <div className="card-body">
                    <form onSubmit={handleSearch} className="d-flex gap-2">
                        <div className="input-group">
                            <span className="input-group-text">
                                <i className="bi bi-search"></i>
                            </span>
                            <input
                                type="text"
                                className="form-control"
                                placeholder="Search by name, email, phone, or industry..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                            {search && (
                                <button type="button" className="btn btn-outline-secondary" onClick={clearSearch}>
                                    <i className="bi bi-x-lg"></i>
                                </button>
                            )}
                        </div>
                        <button type="submit" className="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>

            <div className="admin-card">
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Industry</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {businesses.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="text-center text-muted py-4">
                                            No businesses found. Create your first business.
                                        </td>
                                    </tr>
                                ) : (
                                    businesses.data.map((business) => (
                                        <tr key={business.id}>
                                            <td>
                                                <Link href={`/businesses/${business.id}`} className="fw-medium text-decoration-none">
                                                    {business.name}
                                                </Link>
                                                {business.registration_number && (
                                                    <div className="small text-muted">{business.registration_number}</div>
                                                )}
                                            </td>
                                            <td>{business.email}</td>
                                            <td>{business.industry || '-'}</td>
                                            <td>
                                                <span className={`status-badge ${statusColors[business.status]}`}>
                                                    {business.status.charAt(0).toUpperCase() + business.status.slice(1)}
                                                </span>
                                            </td>
                                            <td className="text-muted">
                                                {new Date(business.created_at).toLocaleDateString()}
                                            </td>
                                            <td>
                                                <div className="d-flex gap-1">
                                                    <Link
                                                        href={`/businesses/${business.id}`}
                                                        className="btn btn-action btn-outline-secondary"
                                                        title="View"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>
                                                    <Link
                                                        href={`/businesses/${business.id}/edit`}
                                                        className="btn btn-action btn-outline-primary"
                                                        title="Edit"
                                                    >
                                                        <i className="bi bi-pencil"></i>
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(business.id)}
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

                {/* Pagination */}
                {businesses.last_page > 1 && (
                    <div className="card-footer">
                        <nav>
                            <ul className="pagination pagination-sm mb-0 justify-content-center">
                                {businesses.links.map((link, index) => (
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
