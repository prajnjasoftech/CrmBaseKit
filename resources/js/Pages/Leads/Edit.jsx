import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import ContactPersonList from '../../Components/ContactPersonList';

export default function Edit({ lead, statuses, sources, entityTypes, users, businesses, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        name: lead.name || '',
        company: lead.company || '',
        source: lead.source || 'other',
        status: lead.status || 'new',
        notes: lead.notes || '',
        assigned_to: lead.assigned_to || '',
        business_id: lead.business_id || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/leads/${lead.id}`);
    };

    const isBusiness = lead.entity_type === 'business';

    return (
        <AdminLayout user={auth?.user}>
            <Head title={`Edit Lead - ${lead.name}`} />

            <div className="page-header">
                <h1 className="page-title">Edit Lead</h1>
                <p className="page-subtitle">Update lead information</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <form onSubmit={handleSubmit}>
                        <div className="admin-card mb-4">
                            <div className="card-header">
                                <h2 className="card-title">
                                    {isBusiness ? 'Business Information' : 'Personal Information'}
                                </h2>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">
                                            {isBusiness ? 'Business Name *' : 'Name *'}
                                        </label>
                                        <input
                                            type="text"
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    {!isBusiness && (
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
                                            {isBusiness ? 'Business Email' : 'Email'}
                                            <span className="badge bg-secondary ms-2">Cannot be changed</span>
                                        </label>
                                        <input
                                            type="email"
                                            className="form-control"
                                            value={lead.email || ''}
                                            disabled
                                        />
                                        <small className="text-muted">Email cannot be modified after creation.</small>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">
                                            {isBusiness ? 'Business Phone' : 'Phone'}
                                            <span className="badge bg-secondary ms-2">Cannot be changed</span>
                                        </label>
                                        <input
                                            type="tel"
                                            className="form-control"
                                            value={lead.phone || ''}
                                            disabled
                                        />
                                        <small className="text-muted">Phone cannot be modified after creation.</small>
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

                        {isBusiness && (
                            <div className="admin-card mb-4">
                                <div className="card-header d-flex justify-content-between align-items-center">
                                    <h2 className="card-title mb-0">Contact Persons</h2>
                                    <Link href={`/leads/${lead.id}/contacts/create`} className="btn btn-primary btn-sm">
                                        <i className="bi bi-plus me-1"></i>
                                        Add Contact
                                    </Link>
                                </div>
                                <div className="card-body">
                                    <ContactPersonList
                                        contacts={lead.contact_people || []}
                                        parentType="lead"
                                        parentId={lead.id}
                                        canManage={true}
                                    />
                                </div>
                            </div>
                        )}

                        <div className="d-flex justify-content-end gap-2">
                            <Link href="/leads" className="btn btn-outline-secondary">
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
                            <h2 className="card-title">Entity Type</h2>
                        </div>
                        <div className="card-body">
                            <span className={`badge ${isBusiness ? 'bg-info' : 'bg-secondary'}`}>
                                {entityTypes[lead.entity_type] || lead.entity_type}
                            </span>
                            <small className="text-muted d-block mt-2">
                                Entity type cannot be changed after creation.
                            </small>
                        </div>
                    </div>

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
