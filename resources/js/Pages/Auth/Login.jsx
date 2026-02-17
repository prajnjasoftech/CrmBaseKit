import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Login" />

            <div className="auth-wrapper">
                <div className="auth-card">
                    <div className="auth-header">
                        <h1 className="auth-title">CRM Base Kit</h1>
                        <p className="auth-subtitle">Sign in to your account</p>
                    </div>

                    {status && (
                        <div className="alert alert-success mb-4">{status}</div>
                    )}

                    <form onSubmit={handleSubmit}>
                        <div className="mb-3">
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

                        <div className="mb-3">
                            <label className="form-label">Password</label>
                            <input
                                type="password"
                                className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="current-password"
                            />
                            {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                        </div>

                        <div className="mb-3 d-flex justify-content-between align-items-center">
                            <div className="form-check">
                                <input
                                    type="checkbox"
                                    className="form-check-input"
                                    id="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                />
                                <label className="form-check-label" htmlFor="remember">
                                    Remember me
                                </label>
                            </div>
                            <Link href="/forgot-password" className="text-decoration-none small">
                                Forgot password?
                            </Link>
                        </div>

                        <button type="submit" className="btn btn-primary w-100" disabled={processing}>
                            {processing ? 'Signing in...' : 'Sign In'}
                        </button>
                    </form>

                    <div className="auth-footer">
                        <p className="mb-0">
                            Don't have an account?{' '}
                            <Link href="/register" className="text-decoration-none">
                                Create one
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
