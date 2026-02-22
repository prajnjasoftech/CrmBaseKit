import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Edit({ service, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        name: service.name || '',
        description: service.description || '',
        status: service.status || 'active',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/services/${service.id}`);
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title={`Edit ${service.name}`} />

            <div className="page-header">
                <h1 className="page-title">Edit Service</h1>
                <p className="page-subtitle">Update service information</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <form onSubmit={handleSubmit}>
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Service Information</h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-12">
                                        <label className="form-label">Service Name *</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    <div className="col-12">
                                        <label className="form-label">Description</label>
                                        <textarea
                                            className={`form-control ${errors.description ? 'is-invalid' : ''}`}
                                            rows="4"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Describe the service..."
                                        />
                                        {errors.description && <div className="invalid-feedback">{errors.description}</div>}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="d-flex justify-content-end gap-2">
                            <Link href="/services" className="btn btn-outline-secondary">
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
                            <div className="form-check mb-2">
                                <input
                                    type="radio"
                                    className="form-check-input"
                                    id="status-active"
                                    checked={data.status === 'active'}
                                    onChange={() => setData('status', 'active')}
                                />
                                <label className="form-check-label" htmlFor="status-active">
                                    Active
                                </label>
                            </div>
                            <div className="form-check">
                                <input
                                    type="radio"
                                    className="form-check-input"
                                    id="status-inactive"
                                    checked={data.status === 'inactive'}
                                    onChange={() => setData('status', 'inactive')}
                                />
                                <label className="form-check-label" htmlFor="status-inactive">
                                    Inactive
                                </label>
                            </div>
                        </div>
                    </div>

                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Info</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">Created</dt>
                                <dd>{new Date(service.created_at).toLocaleDateString()}</dd>
                                <dt className="text-muted small">Last Updated</dt>
                                <dd className="mb-0">{new Date(service.updated_at).toLocaleDateString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
