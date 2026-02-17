import { useForm } from '@inertiajs/react';

export default function ContactPersonForm({ contact = null, parentType, parentId, onCancel }) {
    const isEditing = contact !== null;

    const { data, setData, post, put, processing, errors } = useForm({
        name: contact?.name || '',
        email: contact?.email || '',
        mobile: contact?.mobile || '',
        designation: contact?.designation || '',
        is_primary: contact?.is_primary || false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        const baseUrl = parentType === 'lead'
            ? `/leads/${parentId}/contacts`
            : `/customers/${parentId}/contacts`;

        if (isEditing) {
            put(`${baseUrl}/${contact.id}`);
        } else {
            post(baseUrl);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <div className="row g-3">
                <div className="col-md-6">
                    <label className="form-label">Name *</label>
                    <input
                        type="text"
                        className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="Contact person name"
                    />
                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                </div>

                <div className="col-md-6">
                    <label className="form-label">Designation</label>
                    <input
                        type="text"
                        className={`form-control ${errors.designation ? 'is-invalid' : ''}`}
                        value={data.designation}
                        onChange={(e) => setData('designation', e.target.value)}
                        placeholder="e.g., CEO, Manager"
                    />
                    {errors.designation && <div className="invalid-feedback">{errors.designation}</div>}
                </div>

                <div className="col-md-6">
                    <label className="form-label">Email</label>
                    <input
                        type="email"
                        className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="contact@example.com"
                    />
                    {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                </div>

                <div className="col-md-6">
                    <label className="form-label">Mobile</label>
                    <input
                        type="tel"
                        className={`form-control ${errors.mobile ? 'is-invalid' : ''}`}
                        value={data.mobile}
                        onChange={(e) => setData('mobile', e.target.value)}
                        placeholder="+1 234 567 8900"
                    />
                    {errors.mobile && <div className="invalid-feedback">{errors.mobile}</div>}
                </div>

                <div className="col-12">
                    <div className="form-check">
                        <input
                            type="checkbox"
                            className="form-check-input"
                            id="is_primary"
                            checked={data.is_primary}
                            onChange={(e) => setData('is_primary', e.target.checked)}
                        />
                        <label className="form-check-label" htmlFor="is_primary">
                            Set as primary contact
                        </label>
                    </div>
                </div>

                <div className="col-12 d-flex justify-content-end gap-2">
                    {onCancel && (
                        <button type="button" className="btn btn-outline-secondary" onClick={onCancel}>
                            Cancel
                        </button>
                    )}
                    <button type="submit" className="btn btn-primary" disabled={processing}>
                        {processing ? 'Saving...' : (isEditing ? 'Update Contact' : 'Add Contact')}
                    </button>
                </div>
            </div>
        </form>
    );
}
