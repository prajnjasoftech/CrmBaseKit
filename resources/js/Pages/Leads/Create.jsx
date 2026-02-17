import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Create({ statuses, sources, users, businesses, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        phone: '',
        company: '',
        source: 'other',
        status: 'new',
        notes: '',
        assigned_to: '',
        business_id: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/leads');
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Create Lead" />

            <div className="page-header">
                <h1 className="page-title">Create Lead</h1>
                <p className="page-subtitle">Add a new lead to the pipeline</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <form onSubmit={handleSubmit}>
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Lead Information</h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">Name *</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Company</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.company ? 'is-invalid' : ''}`}
                                            value={data.company}
                                            onChange={(e) => setData('company', e.target.value)}
                                        />
                                        {errors.company && <div className="invalid-feedback">{errors.company}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Email</label>
                                        <input
                                            type="email"
                                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                        />
                                        {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Phone</label>
                                        <input
                                            type="tel"
                                            className={`form-control ${errors.phone ? 'is-invalid' : ''}`}
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                        />
                                        {errors.phone && <div className="invalid-feedback">{errors.phone}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Source *</label>
                                        <select
                                            className={`form-select ${errors.source ? 'is-invalid' : ''}`}
                                            value={data.source}
                                            onChange={(e) => setData('source', e.target.value)}
                                        >
                                            {Object.entries(sources).map(([value, label]) => (
                                                <option key={value} value={value}>{label}</option>
                                            ))}
                                        </select>
                                        {errors.source && <div className="invalid-feedback">{errors.source}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Assigned To</label>
                                        <select
                                            className={`form-select ${errors.assigned_to ? 'is-invalid' : ''}`}
                                            value={data.assigned_to}
                                            onChange={(e) => setData('assigned_to', e.target.value)}
                                        >
                                            <option value="">Unassigned</option>
                                            {users.map((user) => (
                                                <option key={user.id} value={user.id}>{user.name}</option>
                                            ))}
                                        </select>
                                        {errors.assigned_to && <div className="invalid-feedback">{errors.assigned_to}</div>}
                                    </div>

                                    <div className="col-12">
                                        <label className="form-label">Associated Business</label>
                                        <select
                                            className={`form-select ${errors.business_id ? 'is-invalid' : ''}`}
                                            value={data.business_id}
                                            onChange={(e) => setData('business_id', e.target.value)}
                                        >
                                            <option value="">None</option>
                                            {businesses.map((business) => (
                                                <option key={business.id} value={business.id}>{business.name}</option>
                                            ))}
                                        </select>
                                        {errors.business_id && <div className="invalid-feedback">{errors.business_id}</div>}
                                    </div>

                                    <div className="col-12">
                                        <label className="form-label">Notes</label>
                                        <textarea
                                            className={`form-control ${errors.notes ? 'is-invalid' : ''}`}
                                            rows="4"
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            placeholder="Add any relevant notes about this lead..."
                                        />
                                        {errors.notes && <div className="invalid-feedback">{errors.notes}</div>}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="d-flex justify-content-end gap-2">
                            <Link href="/leads" className="btn btn-outline-secondary">
                                Cancel
                            </Link>
                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Lead'}
                            </button>
                        </div>
                    </form>
                </div>

                <div className="col-lg-4">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Status</h2>
                        </div>
                        <div className="card-body">
                            {Object.entries(statuses).map(([value, label]) => (
                                <div className="form-check mb-2" key={value}>
                                    <input
                                        type="radio"
                                        className="form-check-input"
                                        id={`status-${value}`}
                                        checked={data.status === value}
                                        onChange={() => setData('status', value)}
                                    />
                                    <label className="form-check-label" htmlFor={`status-${value}`}>
                                        {label}
                                    </label>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
