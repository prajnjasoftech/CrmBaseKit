import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../Layouts/AdminLayout';

export default function Dashboard({ auth }) {
    const stats = [
        { label: 'Total Leads', value: '2,345', change: '+12.5%', positive: true, icon: 'bi-funnel', color: 'primary' },
        { label: 'Customers', value: '1,234', change: '+8.2%', positive: true, icon: 'bi-people', color: 'success' },
        { label: 'Revenue', value: '$45,678', change: '+15.3%', positive: true, icon: 'bi-currency-dollar', color: 'warning' },
        { label: 'Pending Tasks', value: '28', change: '-5.1%', positive: false, icon: 'bi-list-check', color: 'danger' },
    ];

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Dashboard" />

            <div className="page-header">
                <h1 className="page-title">Dashboard</h1>
                <p className="page-subtitle">Welcome back! Here's what's happening with your business.</p>
            </div>

            {/* Stats Cards */}
            <div className="row g-4 mb-4">
                {stats.map((stat, index) => (
                    <div key={index} className="col-12 col-sm-6 col-xl-3">
                        <div className="stat-card">
                            <div className={`stat-icon bg-${stat.color}-light`}>
                                <i className={`bi ${stat.icon}`}></i>
                            </div>
                            <div className="stat-value">{stat.value}</div>
                            <div className="stat-label">{stat.label}</div>
                            <div className={`stat-change ${stat.positive ? 'positive' : 'negative'}`}>
                                <i className={`bi ${stat.positive ? 'bi-arrow-up' : 'bi-arrow-down'} me-1`}></i>
                                {stat.change} from last month
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Quick Actions */}
            <div className="row g-4">
                <div className="col-12 col-lg-8">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Recent Activity</h2>
                            <button className="btn btn-sm btn-outline-primary">View All</button>
                        </div>
                        <div className="card-body">
                            <p className="text-muted">
                                Your CRM Base Kit is ready! Start by adding your first lead or customer.
                            </p>
                            <div className="d-flex gap-2 mt-3">
                                <Link href="/leads/create" className="btn btn-primary">
                                    <i className="bi bi-plus-lg me-2"></i>
                                    Add Lead
                                </Link>
                                <Link href="/customers/create" className="btn btn-outline-secondary">
                                    <i className="bi bi-person-plus me-2"></i>
                                    Add Customer
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-12 col-lg-4">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Quick Links</h2>
                        </div>
                        <div className="card-body">
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
