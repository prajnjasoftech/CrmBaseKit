import { useForm } from '@inertiajs/react';

export default function FollowUpForm({ followUp = null, parentType, parentId, statuses, onCancel }) {
    const isEditing = followUp !== null;

    const { data, setData, post, put, processing, errors } = useForm({
        follow_up_date: followUp?.follow_up_date?.split('T')[0] || '',
        notes: followUp?.notes || '',
        status: followUp?.status || 'pending',
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        const baseUrl = parentType === 'lead'
            ? `/leads/${parentId}/follow-ups`
            : `/customers/${parentId}/follow-ups`;

        if (isEditing) {
            put(`${baseUrl}/${followUp.id}`);
        } else {
            post(baseUrl);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <div className="row g-3">
                <div className="col-md-6">
                    <label className="form-label">Follow-up Date *</label>
                    <input
                        type="date"
                        className={`form-control ${errors.follow_up_date ? 'is-invalid' : ''}`}
                        value={data.follow_up_date}
                        onChange={(e) => setData('follow_up_date', e.target.value)}
                    />
                    {errors.follow_up_date && <div className="invalid-feedback">{errors.follow_up_date}</div>}
                </div>

                <div className="col-md-6">
                    <label className="form-label">Status</label>
                    <select
                        className={`form-select ${errors.status ? 'is-invalid' : ''}`}
                        value={data.status}
                        onChange={(e) => setData('status', e.target.value)}
                    >
                        {Object.entries(statuses).map(([value, label]) => (
                            <option key={value} value={value}>{label}</option>
                        ))}
                    </select>
                    {errors.status && <div className="invalid-feedback">{errors.status}</div>}
                </div>

                <div className="col-12">
                    <label className="form-label">Notes</label>
                    <textarea
                        className={`form-control ${errors.notes ? 'is-invalid' : ''}`}
                        value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)}
                        rows={4}
                        placeholder="Add any notes about this follow-up..."
                    />
                    {errors.notes && <div className="invalid-feedback">{errors.notes}</div>}
                </div>

                <div className="col-12 d-flex justify-content-end gap-2">
                    {onCancel && (
                        <button type="button" className="btn btn-outline-secondary" onClick={onCancel}>
                            Cancel
                        </button>
                    )}
                    <button type="submit" className="btn btn-primary" disabled={processing}>
                        {processing ? 'Saving...' : (isEditing ? 'Update Follow-up' : 'Schedule Follow-up')}
                    </button>
                </div>
            </div>
        </form>
    );
}
