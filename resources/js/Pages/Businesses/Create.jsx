import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Create({ industries, countries, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        registration_number: '',
        email: '',
        phone: '',
        website: '',
        address: '',
        city: '',
        state: '',
        postal_code: '',
        country: 'US',
        industry: '',
        status: 'pending',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/businesses');
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Create Business" />

            <div className="page-header">
                <h1 className="page-title">Create Business</h1>
                <p className="page-subtitle">Add a new business to the system</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <form onSubmit={handleSubmit}>
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Basic Information</h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-md-8">
                                        <label className="form-label">Business Name *</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    <div className="col-md-4">
                                        <label className="form-label">Registration Number</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.registration_number ? 'is-invalid' : ''}`}
                                            value={data.registration_number}
                                            onChange={(e) => setData('registration_number', e.target.value)}
                                        />
                                        {errors.registration_number && <div className="invalid-feedback">{errors.registration_number}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Email *</label>
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
                                        <label className="form-label">Website</label>
                                        <input
                                            type="url"
                                            className={`form-control ${errors.website ? 'is-invalid' : ''}`}
                                            value={data.website}
                                            onChange={(e) => setData('website', e.target.value)}
                                            placeholder="https://"
                                        />
                                        {errors.website && <div className="invalid-feedback">{errors.website}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Industry</label>
                                        <select
                                            className={`form-select ${errors.industry ? 'is-invalid' : ''}`}
                                            value={data.industry}
                                            onChange={(e) => setData('industry', e.target.value)}
                                        >
                                            <option value="">Select industry</option>
                                            {industries.map((industry) => (
                                                <option key={industry} value={industry}>{industry}</option>
                                            ))}
                                        </select>
                                        {errors.industry && <div className="invalid-feedback">{errors.industry}</div>}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Address</h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-12">
                                        <label className="form-label">Street Address</label>
                                        <textarea
                                            className={`form-control ${errors.address ? 'is-invalid' : ''}`}
                                            rows="2"
                                            value={data.address}
                                            onChange={(e) => setData('address', e.target.value)}
                                        />
                                        {errors.address && <div className="invalid-feedback">{errors.address}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">City</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.city ? 'is-invalid' : ''}`}
                                            value={data.city}
                                            onChange={(e) => setData('city', e.target.value)}
                                        />
                                        {errors.city && <div className="invalid-feedback">{errors.city}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">State / Province</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.state ? 'is-invalid' : ''}`}
                                            value={data.state}
                                            onChange={(e) => setData('state', e.target.value)}
                                        />
                                        {errors.state && <div className="invalid-feedback">{errors.state}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Postal Code</label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.postal_code ? 'is-invalid' : ''}`}
                                            value={data.postal_code}
                                            onChange={(e) => setData('postal_code', e.target.value)}
                                        />
                                        {errors.postal_code && <div className="invalid-feedback">{errors.postal_code}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Country</label>
                                        <select
                                            className={`form-select ${errors.country ? 'is-invalid' : ''}`}
                                            value={data.country}
                                            onChange={(e) => setData('country', e.target.value)}
                                        >
                                            {Object.entries(countries).map(([code, name]) => (
                                                <option key={code} value={code}>{name}</option>
                                            ))}
                                        </select>
                                        {errors.country && <div className="invalid-feedback">{errors.country}</div>}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="d-flex justify-content-end gap-2">
                            <Link href="/businesses" className="btn btn-outline-secondary">
                                Cancel
                            </Link>
                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Business'}
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
                            <div className="form-check mb-2">
                                <input
                                    type="radio"
                                    className="form-check-input"
                                    id="status-pending"
                                    checked={data.status === 'pending'}
                                    onChange={() => setData('status', 'pending')}
                                />
                                <label className="form-check-label" htmlFor="status-pending">
                                    Pending
                                </label>
                            </div>
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
                </div>
            </div>
        </AdminLayout>
    );
}
