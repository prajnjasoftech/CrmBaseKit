import { Link, router } from '@inertiajs/react';

export default function FollowUpList({ followUps, parentType, parentId, canManage = false }) {
    const handleDelete = (followUpId) => {
        if (confirm('Are you sure you want to delete this follow-up?')) {
            const baseUrl = parentType === 'lead'
                ? `/leads/${parentId}/follow-ups`
                : `/customers/${parentId}/follow-ups`;
            router.delete(`${baseUrl}/${followUpId}`);
        }
    };

    const handleComplete = (followUpId) => {
        if (confirm('Mark this follow-up as completed?')) {
            const baseUrl = parentType === 'lead'
                ? `/leads/${parentId}/follow-ups`
                : `/customers/${parentId}/follow-ups`;
            router.post(`${baseUrl}/${followUpId}/complete`);
        }
    };

    const statusColors = {
        pending: 'bg-warning text-dark',
        completed: 'bg-success',
        cancelled: 'bg-secondary',
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    const isOverdue = (followUp) => {
        if (followUp.status !== 'pending') return false;
        const followUpDate = new Date(followUp.follow_up_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return followUpDate < today;
    };

    if (!followUps || followUps.length === 0) {
        return (
            <div className="text-muted">
                No follow-ups scheduled yet.
            </div>
        );
    }

    return (
        <div className="table-responsive">
            <table className="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Created By</th>
                        {canManage && <th className="text-end">Actions</th>}
                    </tr>
                </thead>
                <tbody>
                    {followUps.map((followUp) => (
                        <tr key={followUp.id} className={isOverdue(followUp) ? 'table-danger' : ''}>
                            <td>
                                <div className="d-flex align-items-center gap-2">
                                    {formatDate(followUp.follow_up_date)}
                                    {isOverdue(followUp) && (
                                        <span className="badge bg-danger">Overdue</span>
                                    )}
                                </div>
                            </td>
                            <td>
                                <div className="text-truncate" style={{ maxWidth: '250px' }}>
                                    {followUp.notes || '-'}
                                </div>
                            </td>
                            <td>
                                <span className={`badge ${statusColors[followUp.status] || 'bg-secondary'}`}>
                                    {followUp.status.charAt(0).toUpperCase() + followUp.status.slice(1)}
                                </span>
                            </td>
                            <td>{followUp.creator?.name || '-'}</td>
                            {canManage && (
                                <td className="text-end">
                                    <div className="d-flex justify-content-end gap-1">
                                        {followUp.status === 'pending' && (
                                            <button
                                                type="button"
                                                className="btn btn-outline-success btn-sm"
                                                onClick={() => handleComplete(followUp.id)}
                                                title="Mark as completed"
                                            >
                                                <i className="bi bi-check-lg"></i>
                                            </button>
                                        )}
                                        <Link
                                            href={`/${parentType}s/${parentId}/follow-ups/${followUp.id}/edit`}
                                            className="btn btn-outline-primary btn-sm"
                                            title="Edit"
                                        >
                                            <i className="bi bi-pencil"></i>
                                        </Link>
                                        <button
                                            type="button"
                                            className="btn btn-outline-danger btn-sm"
                                            onClick={() => handleDelete(followUp.id)}
                                            title="Delete"
                                        >
                                            <i className="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
