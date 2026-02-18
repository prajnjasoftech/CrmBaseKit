import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../Layouts/AdminLayout';

export default function Dashboard({ auth, upcomingFollowUps = [], overdueFollowUps = [], stats = {} }) {
    const statCards = [
        { label: 'Total Leads', value: stats.total_leads || 0, icon: 'bi-funnel', color: 'primary' },
        { label: 'Customers', value: stats.total_customers || 0, icon: 'bi-people', color: 'success' },
        { label: 'Pending Follow-ups', value: stats.pending_follow_ups || 0, icon: 'bi-calendar-check', color: 'warning' },
        { label: 'Overdue Follow-ups', value: stats.overdue_follow_ups || 0, icon: 'bi-exclamation-triangle', color: 'danger' },
    ];

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Dashboard" />

            <div className="page-header">
                <h1 className="page-title">Dashboard</h1>
                <p className="page-subtitle">Welcome back! Here's what's happening with your business.</p>
            </div>

            {/* Stats Cards */}
            <div className="row g-4 mb-4">
                {statCards.map((stat, index) => (
                    <div key={index} className="col-12 col-sm-6 col-xl-3">
                        <div className="stat-card">
                            <div className={`stat-icon bg-${stat.color}-light`}>
                                <i className={`bi ${stat.icon}`}></i>
                            </div>
                            <div className="stat-value">{stat.value.toLocaleString()}</div>
                            <div className="stat-label">{stat.label}</div>
                        </div>
                    </div>
                ))}
            </div>

            <div className="row g-4">
                {/* Overdue Follow-ups */}
                {overdueFollowUps.length > 0 && (
                    <div className="col-12">
                        <div className="admin-card border-danger">
                            <div className="card-header bg-danger text-white">
                                <h2 className="card-title mb-0 text-white">
                                    <i className="bi bi-exclamation-triangle me-2"></i>
                                    Overdue Follow-ups
                                </h2>
                            </div>
                            <div className="card-body p-0">
                                <div className="table-responsive">
                                    <table className="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Due Date</th>
                                                <th>Type</th>
                                                <th>Name</th>
                                                <th>Notes</th>
                                                <th className="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {overdueFollowUps.map((followUp) => (
                                                <tr key={followUp.id} className="table-danger">
                                                    <td>
                                                        <span className="text-danger fw-bold">
                                                            {formatDate(followUp.follow_up_date)}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span className={`badge ${followUp.parent_type === 'lead' ? 'bg-info' : 'bg-success'}`}>
                                                            {followUp.parent_type === 'lead' ? 'Lead' : 'Customer'}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <Link href={`/${followUp.parent_type}s/${followUp.parent_id}`}>
                                                            {followUp.parent_name}
                                                        </Link>
                                                    </td>
                                                    <td>
                                                        <div className="text-truncate" style={{ maxWidth: '200px' }}>
                                                            {followUp.notes || '-'}
                                                        </div>
                                                    </td>
                                                    <td className="text-end">
                                                        <Link
                                                            href={`/${followUp.parent_type}s/${followUp.parent_id}`}
                                                            className="btn btn-outline-primary btn-sm"
                                                        >
                                                            View
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Upcoming Follow-ups */}
                <div className="col-12 col-lg-8">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">
                                <i className="bi bi-calendar-event me-2"></i>
                                Upcoming Follow-ups (Next 7 Days)
                            </h2>
                        </div>
                        <div className="card-body p-0">
                            {upcomingFollowUps.length === 0 ? (
                                <div className="p-4 text-center text-muted">
                                    <i className="bi bi-calendar-check fs-1 d-block mb-3"></i>
                                    <p className="mb-0">No upcoming follow-ups scheduled.</p>
                                </div>
                            ) : (
                                <div className="table-responsive">
                                    <table className="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Due Date</th>
                                                <th>Type</th>
                                                <th>Name</th>
                                                <th>Notes</th>
                                                <th className="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {upcomingFollowUps.map((followUp) => (
                                                <tr key={followUp.id} className={followUp.is_today ? 'table-warning' : ''}>
                                                    <td>
                                                        <div className="d-flex align-items-center gap-2">
                                                            {formatDate(followUp.follow_up_date)}
                                                            {followUp.is_today && (
                                                                <span className="badge bg-warning text-dark">Today</span>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span className={`badge ${followUp.parent_type === 'lead' ? 'bg-info' : 'bg-success'}`}>
                                                            {followUp.parent_type === 'lead' ? 'Lead' : 'Customer'}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <Link href={`/${followUp.parent_type}s/${followUp.parent_id}`}>
                                                            {followUp.parent_name}
                                                        </Link>
                                                    </td>
                                                    <td>
                                                        <div className="text-truncate" style={{ maxWidth: '200px' }}>
                                                            {followUp.notes || '-'}
                                                        </div>
                                                    </td>
                                                    <td className="text-end">
                                                        <Link
                                                            href={`/${followUp.parent_type}s/${followUp.parent_id}`}
                                                            className="btn btn-outline-primary btn-sm"
                                                        >
                                                            View
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Quick Links */}
                <div className="col-12 col-lg-4">
                    <div className="admin-card mb-4">
                        <div className="card-header">
                            <h2 className="card-title">Quick Actions</h2>
                        </div>
                        <div className="card-body">
                            <div className="d-grid gap-2">
                                <Link href="/leads/create" className="btn btn-primary">
                                    <i className="bi bi-plus-lg me-2"></i>
                                    Add New Lead
                                </Link>
                                <Link href="/customers/create" className="btn btn-outline-primary">
                                    <i className="bi bi-person-plus me-2"></i>
                                    Add New Customer
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Quick Links</h2>
                        </div>
                        <div className="card-body p-0">
                            <div className="list-group list-group-flush">
                                <Link href="/leads" className="list-group-item list-group-item-action">
                                    <i className="bi bi-funnel me-2"></i>
                                    Manage Leads
                                </Link>
                                <Link href="/customers" className="list-group-item list-group-item-action">
                                    <i className="bi bi-people me-2"></i>
                                    View Customers
                                </Link>
                                <Link href="/users" className="list-group-item list-group-item-action">
                                    <i className="bi bi-person me-2"></i>
                                    Manage Users
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
