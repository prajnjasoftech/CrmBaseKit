import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const handleResend = (e) => {
        e.preventDefault();
        post('/email/verification-notification');
    };

    return (
        <>
            <Head title="Verify Email" />

            <div className="auth-wrapper">
                <div className="auth-card">
                    <div className="auth-header">
                        <div className="auth-icon mb-3">
                            <i className="bi bi-envelope-check display-4 text-primary"></i>
                        </div>
                        <h1 className="auth-title">Verify Your Email</h1>
                        <p className="auth-subtitle">
                            Thanks for signing up! Before getting started, could you verify your email
                            address by clicking on the link we just emailed to you?
                        </p>
                    </div>

                    {status === 'verification-link-sent' && (
                        <div className="alert alert-success mb-4">
                            A new verification link has been sent to your email address.
                        </div>
                    )}

                    <div className="d-grid gap-3">
                        <form onSubmit={handleResend}>
                            <button type="submit" className="btn btn-primary w-100" disabled={processing}>
                                {processing ? 'Sending...' : 'Resend Verification Email'}
                            </button>
                        </form>

                        <form action="/logout" method="POST">
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="btn btn-outline-secondary w-100"
                            >
                                Log Out
                            </Link>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
