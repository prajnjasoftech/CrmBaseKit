import { Link, usePage } from '@inertiajs/react';

const menuItems = [
    {
        section: 'Main',
        items: [
            { name: 'Dashboard', icon: 'bi-grid', href: '/' },
            { name: 'Analytics', icon: 'bi-graph-up', href: '/analytics' },
        ],
    },
    {
        section: 'CRM',
        items: [
            { name: 'Leads', icon: 'bi-funnel', href: '/leads' },
            { name: 'Customers', icon: 'bi-people', href: '/customers' },
            { name: 'Businesses', icon: 'bi-building', href: '/businesses' },
            { name: 'Services', icon: 'bi-briefcase', href: '/services' },
        ],
    },
    {
        section: 'Communication',
        items: [
            { name: 'News Board', icon: 'bi-megaphone', href: '/news' },
            { name: 'Messages', icon: 'bi-chat-dots', href: '/messages' },
        ],
    },
    {
        section: 'Administration',
        items: [
            { name: 'Users', icon: 'bi-person-gear', href: '/users' },
            { name: 'Roles', icon: 'bi-shield-check', href: '/roles' },
            { name: 'Settings', icon: 'bi-gear', href: '/settings' },
        ],
    },
];

export default function Sidebar({ collapsed, open, onClose }) {
    const { url } = usePage();

    const isActive = (href) => {
        if (href === '/') {
            return url === '/';
        }
        return url.startsWith(href);
    };

    return (
        <aside className={`admin-sidebar ${open ? 'show' : ''}`}>
            <div className="sidebar-header">
                <Link href="/" className="sidebar-brand">
                    <svg className="brand-logo" viewBox="0 0 32 32" fill="none">
                        <rect width="32" height="32" rx="8" fill="#6366f1" />
                        <path
                            d="M10 16L14 20L22 12"
                            stroke="white"
                            strokeWidth="2.5"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        />
                    </svg>
                    <span className="brand-text">CRM Kit</span>
                </Link>
            </div>

            <nav className="sidebar-nav">
                {menuItems.map((section, idx) => (
                    <div key={idx} className="nav-section">
                        <div className="nav-section-title">{section.section}</div>
                        <ul className="nav flex-column">
                            {section.items.map((item, itemIdx) => (
                                <li key={itemIdx} className="nav-item">
                                    <Link
                                        href={item.href}
                                        className={`nav-link ${isActive(item.href) ? 'active' : ''}`}
                                        onClick={onClose}
                                    >
                                        <i className={`nav-icon bi ${item.icon}`}></i>
                                        <span className="nav-text">{item.name}</span>
                                        {item.badge && (
                                            <span className="nav-badge badge bg-primary">
                                                {item.badge}
                                            </span>
                                        )}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}
            </nav>
        </aside>
    );
}
