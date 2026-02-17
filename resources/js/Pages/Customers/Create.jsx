import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Create({ statuses, entityTypes, users, businesses, countries, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        entity_type: 'individual',
        email: '',
        phone: '',
        company: '',
        address: '',
        city: '',
        state: '',
        postal_code: '',
        country: 'US',
        status: 'active',
        notes: '',
        assigned_to: '',
        business_id: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/customers');
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Create Customer" />

            <div className="page-header">
                <h1 className="page-title">Create Customer</h1>
                <p className="page-subtitle">Add a new customer directly</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <form onSubmit={handleSubmit}>
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Entity Type</h2>
                            </div>
                            <div className="card-body">
                                <div className="d-flex gap-4">
                                    {Object.entries(entityTypes).map(([value, label]) => (
                                        <div className="form-check" key={value}>
                                            <input
                                                type="radio"
                                                className="form-check-input"
                                                id={`entity-type-${value}`}
                                                checked={data.entity_type === value}
                                                onChange={() => setData('entity_type', value)}
                                            />
                                            <label className="form-check-label" htmlFor={`entity-type-${value}`}>
                                                {label}
                                            </label>
                                        </div>
                                    ))}
                                </div>
                                {errors.entity_type && <div className="text-danger small mt-1">{errors.entity_type}</div>}
                                <small className="text-muted d-block mt-2">
                                    {data.entity_type === 'individual'
                                        ? 'For individual people or sole proprietors.'
                                        : 'For companies, organizations, or business entities. You can add contact persons after creation.'}
                                </small>
                            </div>
                        </div>

                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">
                                    {data.entity_type === 'individual' ? 'Personal Information' : 'Business Information'}
                                </h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">
                                            {data.entity_type === 'individual' ? 'Name *' : 'Business Name *'}
                                        </label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder={data.entity_type === 'individual' ? 'John Doe' : 'Acme Corporation'}
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    {data.entity_type === 'individual' && (
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
                                    )}

                                    <div className="col-md-6">
                                        <label className="form-label">
                                            {data.entity_type === 'individual' ? 'Email' : 'Business Email'}
                                        </label>
                                        <input
                                            type="email"
                                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                        />
                                        {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">
                                            {data.entity_type === 'individual' ? 'Phone' : 'Business Phone'}
                                        </label>
                                        <input
                                            type="tel"
                                            className={`form-control ${errors.phone ? 'is-invalid' : ''}`}
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                        />
                                        {errors.phone && <div className="invalid-feedback">{errors.phone}</div>}
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

                                    <div className="col-md-6">
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

                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">Notes</h2>
                            </div>
                            <div className="card-body">
                                <textarea
                                    className={`form-control ${errors.notes ? 'is-invalid' : ''}`}
                                    rows="4"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Add any relevant notes about this customer..."
                                />
                                {errors.notes && <div className="invalid-feedback">{errors.notes}</div>}
                            </div>
                        </div>

                        <div className="d-flex justify-content-end gap-2">
                            <Link href="/customers" className="btn btn-outline-secondary">
                                Cancel
                            </Link>
                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Customer'}
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
