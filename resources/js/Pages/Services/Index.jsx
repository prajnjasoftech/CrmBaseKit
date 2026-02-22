import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ services, auth, flash, filters }) {
    const { auth: { user } } = usePage().props;
    const can = (permission) => user?.permissions?.includes(permission) ?? false;
    const [search, setSearch] = useState(filters?.search || '');

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this service?')) {
            router.delete(`/services/${id}`);
        }
    };

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/services', { search }, { preserveState: true, preserveScroll: true });
    };

    const clearSearch = () => {
        setSearch('');
        router.get('/services', {}, { preserveState: true, preserveScroll: true });
    };

    const statusColors = {
        active: 'status-active',
        inactive: 'status-inactive',
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Services" />

            <div className="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 className="page-title">Services</h1>
                    <p className="page-subtitle">Manage available services</p>
                </div>
                {can('create services') && (
                    <Link href="/services/create" className="btn btn-primary">
                        <i className="bi bi-plus-lg me-2"></i>
                        Add Service
                    </Link>
                )}
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
                                placeholder="Search by name or description..."
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
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {services.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="5" className="text-center text-muted py-4">
                                            No services found. Create your first service.
                                        </td>
                                    </tr>
                                ) : (
                                    services.data.map((service) => (
                                        <tr key={service.id}>
                                            <td>
                                                <Link href={`/services/${service.id}`} className="fw-medium text-decoration-none">
                                                    {service.name}
                                                </Link>
                                            </td>
                                            <td className="text-muted">
                                                {service.description ? (
                                                    service.description.length > 50
                                                        ? service.description.substring(0, 50) + '...'
                                                        : service.description
                                                ) : '-'}
                                            </td>
                                            <td>
                                                <span className={`status-badge ${statusColors[service.status]}`}>
                                                    {service.status.charAt(0).toUpperCase() + service.status.slice(1)}
                                                </span>
                                            </td>
                                            <td className="text-muted">
                                                {new Date(service.created_at).toLocaleDateString()}
                                            </td>
                                            <td>
                                                <div className="d-flex gap-1">
                                                    <Link
                                                        href={`/services/${service.id}`}
                                                        className="btn btn-action btn-outline-secondary"
                                                        title="View"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>
                                                    {can('edit services') && (
                                                        <Link
                                                            href={`/services/${service.id}/edit`}
                                                            className="btn btn-action btn-outline-primary"
                                                            title="Edit"
                                                        >
                                                            <i className="bi bi-pencil"></i>
                                                        </Link>
                                                    )}
                                                    {can('delete services') && (
                                                        <button
                                                            onClick={() => handleDelete(service.id)}
                                                            className="btn btn-action btn-outline-danger"
                                                            title="Delete"
                                                        >
                                                            <i className="bi bi-trash"></i>
                                                        </button>
                                                    )}
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
                {services.last_page > 1 && (
                    <div className="card-footer">
                        <nav>
                            <ul className="pagination pagination-sm mb-0 justify-content-center">
                                {services.links.map((link, index) => (
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
