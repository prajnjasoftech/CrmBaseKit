import { Link, router } from '@inertiajs/react';

export default function ContactPersonList({ contacts, parentType, parentId, canManage = false }) {
    const handleDelete = (contactId) => {
        if (confirm('Are you sure you want to delete this contact person?')) {
            const baseUrl = parentType === 'lead'
                ? `/leads/${parentId}/contacts`
                : `/customers/${parentId}/contacts`;
            router.delete(`${baseUrl}/${contactId}`);
        }
    };

    const handleSetPrimary = (contactId) => {
        const baseUrl = parentType === 'lead'
            ? `/leads/${parentId}/contacts`
            : `/customers/${parentId}/contacts`;
        router.post(`${baseUrl}/${contactId}/set-primary`);
    };

    if (!contacts || contacts.length === 0) {
        return (
            <div className="text-muted">
                No contact persons added yet.
            </div>
        );
    }

    return (
        <div className="table-responsive">
            <table className="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        {canManage && <th className="text-end">Actions</th>}
                    </tr>
                </thead>
                <tbody>
                    {contacts.map((contact) => (
                        <tr key={contact.id}>
                            <td>
                                <div className="d-flex align-items-center gap-2">
                                    {contact.name}
                                    {contact.is_primary && (
                                        <span className="badge bg-primary">Primary</span>
                                    )}
                                </div>
                            </td>
                            <td>{contact.designation || '-'}</td>
                            <td>
                                {contact.email ? (
                                    <a href={`mailto:${contact.email}`}>{contact.email}</a>
                                ) : '-'}
                            </td>
                            <td>
                                {contact.mobile ? (
                                    <a href={`tel:${contact.mobile}`}>{contact.mobile}</a>
                                ) : '-'}
                            </td>
                            {canManage && (
                                <td className="text-end">
                                    <div className="d-flex justify-content-end gap-1">
                                        {!contact.is_primary && (
                                            <button
                                                type="button"
                                                className="btn btn-outline-info btn-sm"
                                                onClick={() => handleSetPrimary(contact.id)}
                                                title="Set as primary"
                                            >
                                                <i className="bi bi-star"></i>
                                            </button>
                                        )}
                                        <Link
                                            href={`/${parentType}s/${parentId}/contacts/${contact.id}/edit`}
                                            className="btn btn-outline-primary btn-sm"
                                            title="Edit"
                                        >
                                            <i className="bi bi-pencil"></i>
                                        </Link>
                                        <button
                                            type="button"
                                            className="btn btn-outline-danger btn-sm"
                                            onClick={() => handleDelete(contact.id)}
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
