import { Head, Link, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <>
            <Head title="Forgot Password" />

            <div className="auth-wrapper">
                <div className="auth-card">
                    <div className="auth-header">
                        <h1 className="auth-title">Forgot Password</h1>
                        <p className="auth-subtitle">
                            Enter your email address and we'll send you a password reset link.
                        </p>
                    </div>

                    {status && (
                        <div className="alert alert-success mb-4">{status}</div>
                    )}

                    <form onSubmit={handleSubmit}>
                        <div className="mb-4">
                            <label className="form-label">Email Address</label>
                            <input
                                type="email"
                                className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                autoComplete="email"
                                autoFocus
                            />
                            {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                        </div>

                        <button type="submit" className="btn btn-primary w-100" disabled={processing}>
                            {processing ? 'Sending...' : 'Send Reset Link'}
                        </button>
                    </form>

                    <div className="auth-footer">
                        <p className="mb-0">
                            Remember your password?{' '}
                            <Link href="/login" className="text-decoration-none">
                                Sign in
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
