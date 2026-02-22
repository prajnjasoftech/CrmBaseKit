import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Edit({ customer, project, services, users, statuses, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        name: project.name || '',
        description: project.description || '',
        service_id: project.service_id || '',
        status: project.status || 'pending',
        start_date: project.start_date || '',
        end_date: project.end_date || '',
        budget: project.budget || '',
        assigned_to: project.assigned_to || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/customers/${customer.id}/projects/${project.id}`);
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title={`Edit Project - ${project.name}`} />

            <div className="page-header">
                <h1 className="page-title">Edit Project</h1>
                <p className="page-subtitle">Update project for {customer.name}</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <form onSubmit={handleSubmit}>
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Project Information</h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-md-8">
                                        <label className="form-label">Project Name *</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    <div className="col-md-4">
                                        <label className="form-label">Service *</label>
                                        <select
                                            className={`form-select ${errors.service_id ? 'is-invalid' : ''}`}
                                            value={data.service_id}
                                            onChange={(e) => setData('service_id', e.target.value)}
                                        >
                                            <option value="">Select service</option>
                                            {services.map((service) => (
                                                <option key={service.id} value={service.id}>{service.name}</option>
                                            ))}
                                        </select>
                                        {errors.service_id && <div className="invalid-feedback">{errors.service_id}</div>}
                                    </div>

                                    <div className="col-12">
                                        <label className="form-label">Description</label>
                                        <textarea
                                            className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                                            rows="3"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                        />
                                        {errors.description && <div className="invalid-feedback">{errors.description}</div>}
                                    </div>

                                    <div className="col-md-4">
                                        <label className="form-label">Start Date</label>
                                        <input
                                            type="date"
                                            className={`form-control ${errors.start_date ? 'is-invalid' : ''}`}
                                            value={data.start_date}
                                            onChange={(e) => setData('start_date', e.target.value)}
                                        />
                                        {errors.start_date && <div className="invalid-feedback">{errors.start_date}</div>}
                                    </div>

                                    <div className="col-md-4">
                                        <label className="form-label">End Date</label>
                                        <input
                                            type="date"
                                            className={`form-control ${errors.end_date ? 'is-invalid' : ''}`}
                                            value={data.end_date}
                                            onChange={(e) => setData('end_date', e.target.value)}
                                        />
                                        {errors.end_date && <div className="invalid-feedback">{errors.end_date}</div>}
                                    </div>

                                    <div className="col-md-4">
                                        <label className="form-label">Budget</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            className={`form-control ${errors.budget ? 'is-invalid' : ''}`}
                                            value={data.budget}
                                            onChange={(e) => setData('budget', e.target.value)}
                                        />
                                        {errors.budget && <div className="invalid-feedback">{errors.budget}</div>}
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
                                </div>
                            </div>
                        </div>

                        <div className="d-flex justify-content-end gap-2">
                            <Link href={`/customers/${customer.id}`} className="btn btn-outline-secondary">
                                Cancel
                            </Link>
                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                {processing ? 'Saving...' : 'Save Changes'}
                            </button>
                        </div>
                    </form>
                </div>

                <div className="col-lg-4">
                    <div className="admin-card mb-4">
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

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Info</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">Created</dt>
                                <dd>{new Date(project.created_at).toLocaleDateString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(project.updated_at).toLocaleDateString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
