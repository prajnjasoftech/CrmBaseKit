import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import FollowUpForm from '../../Components/FollowUpForm';

export default function Edit({ parent, parentType, followUp, statuses, auth }) {
    const parentName = parent.name || (parentType === 'lead' ? 'Lead' : 'Customer');
    const backUrl = `/${parentType}s/${parent.id}`;

    return (
        <AdminLayout user={auth?.user}>
            <Head title={`Edit Follow-up - ${parentName}`} />

            <div className="page-header">
                <h1 className="page-title">Edit Follow-up</h1>
                <p className="page-subtitle">Update follow-up details</p>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">Follow-up Details</h2>
                        </div>
                        <div className="card-body">
                            <FollowUpForm
                                followUp={followUp}
                                parentType={parentType}
                                parentId={parent.id}
                                statuses={statuses}
                                onCancel={() => window.history.back()}
                            />
                        </div>
                    </div>
                </div>

                <div className="col-lg-4">
                    <div className="admin-card">
                        <div className="card-header">
                            <h2 className="card-title">{parentType === 'lead' ? 'Lead' : 'Customer'} Info</h2>
                        </div>
                        <div className="card-body">
                            <dl className="mb-0">
                                <dt className="text-muted small">Name</dt>
                                <dd>{parent.name}</dd>
                                <dt className="text-muted small">Email</dt>
                                <dd>{parent.email || '-'}</dd>
                                <dt className="text-muted small">Phone</dt>
                                <dd className="mb-0">{parent.phone || '-'}</dd>
                            </dl>
                        </div>
                        <div className="card-footer">
                            <Link href={backUrl} className="btn btn-outline-secondary btn-sm w-100">
                                <i className="bi bi-arrow-left me-2"></i>
                                Back to {parentType === 'lead' ? 'Lead' : 'Customer'}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
