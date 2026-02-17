import { useState } from 'react';
import { Link, router } from '@inertiajs/react';

export default function Header({ user, onToggleSidebar, onToggleMobileSidebar }) {
    const [searchQuery, setSearchQuery] = useState('');
    const [showUserMenu, setShowUserMenu] = useState(false);

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            router.get('/search', { q: searchQuery });
        }
    };

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    return (
        <header className="admin-header">
            <div className="header-container">
                <div className="header-left">
                    <button
                        className="hamburger-menu"
                        onClick={onToggleMobileSidebar}
                        aria-label="Toggle mobile menu"
                    >
                        <i className="bi bi-list"></i>
                    </button>

                    <form className="header-search" onSubmit={handleSearch}>
                        <i className="bi bi-search search-icon"></i>
                        <input
                            type="search"
                            className="search-input"
                            placeholder="Search... (Ctrl+K)"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                        />
                    </form>
                </div>

                <div className="header-right">
                    <button className="header-btn" title="Notifications">
                        <i className="bi bi-bell"></i>
                        <span className="badge"></span>
                    </button>

                    <button className="header-btn" title="Messages">
                        <i className="bi bi-chat"></i>
                    </button>

                    <div className="dropdown">
                        <button
                            className="header-profile"
                            onClick={() => setShowUserMenu(!showUserMenu)}
                        >
                            <img
                                src={user?.avatar || '/assets/images/avatar-placeholder.png'}
                                alt="Profile"
                                className="profile-avatar"
                                onError={(e) => {
                                    e.target.src = 'https://ui-avatars.com/api/?name=' +
                                        encodeURIComponent(user?.name || 'User');
                                }}
                            />
                            <div className="profile-info">
                                <div className="profile-name">
                                    {user?.name || 'Guest User'}
                                </div>
                                <div className="profile-role">
                                    {user?.role || 'Administrator'}
                                </div>
                            </div>
                            <i className="bi bi-chevron-down ms-2"></i>
                        </button>

                        {showUserMenu && (
                            <div
                                className="dropdown-menu show"
                                style={{
                                    position: 'absolute',
                                    right: 0,
                                    top: '100%',
                                    marginTop: '0.5rem',
                                }}
                            >
                                <Link href="/profile" className="dropdown-item">
                                    <i className="bi bi-person me-2"></i>
                                    Profile
                                </Link>
                                <Link href="/settings" className="dropdown-item">
                                    <i className="bi bi-gear me-2"></i>
                                    Settings
                                </Link>
                                <hr className="dropdown-divider" />
                                <button
                                    className="dropdown-item text-danger"
                                    onClick={handleLogout}
                                >
                                    <i className="bi bi-box-arrow-right me-2"></i>
                                    Logout
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </header>
    );
}
