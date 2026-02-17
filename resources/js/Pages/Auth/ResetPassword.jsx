import { Head, useForm } from '@inertiajs/react';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token: token,
        email: email || '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/reset-password');
    };

    return (
        <>
            <Head title="Reset Password" />

            <div className="auth-wrapper">
                <div className="auth-card">
                    <div className="auth-header">
                        <h1 className="auth-title">Reset Password</h1>
                        <p className="auth-subtitle">Enter your new password below.</p>
                    </div>

                    <form onSubmit={handleSubmit}>
                        <input type="hidden" name="token" value={data.token} />

                        <div className="mb-3">
                            <label className="form-label">Email Address</label>
                            <input
                                type="email"
                                className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                autoComplete="email"
                            />
                            {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                        </div>

                        <div className="mb-3">
                            <label className="form-label">New Password</label>
                            <input
                                type="password"
                                className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="new-password"
                                autoFocus
                            />
                            {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                        </div>

                        <div className="mb-4">
                            <label className="form-label">Confirm New Password</label>
                            <input
                                type="password"
                                className={`form-control ${errors.password_confirmation ? 'is-invalid' : ''}`}
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                autoComplete="new-password"
                            />
                            {errors.password_confirmation && (
                                <div className="invalid-feedback">{errors.password_confirmation}</div>
                            )}
                        </div>

                        <button type="submit" className="btn btn-primary w-100" disabled={processing}>
                            {processing ? 'Resetting...' : 'Reset Password'}
                        </button>
                    </form>
                </div>
            </div>
        </>
    );
}
