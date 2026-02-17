import { Head, Link, useForm } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <>
            <Head title="Register" />

            <div className="auth-wrapper">
                <div className="auth-card">
                    <div className="auth-header">
                        <h1 className="auth-title">CRM Base Kit</h1>
                        <p className="auth-subtitle">Create your account</p>
                    </div>

                    <form onSubmit={handleSubmit}>
                        <div className="mb-3">
                            <label className="form-label">Full Name</label>
                            <input
                                type="text"
                                className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                autoComplete="name"
                                autoFocus
                            />
                            {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                        </div>

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
                            <label className="form-label">Password</label>
                            <input
                                type="password"
                                className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="new-password"
                            />
                            {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                            <div className="form-text">
                                Min 8 characters
                            </div>
                        </div>

                        <div className="mb-4">
                            <label className="form-label">Confirm Password</label>
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
                            {processing ? 'Creating account...' : 'Create Account'}
                        </button>
                    </form>

                    <div className="auth-footer">
                        <p className="mb-0">
                            Already have an account?{' '}
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
