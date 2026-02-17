import { useState } from 'react';
import Sidebar from '../Components/Sidebar';
import Header from '../Components/Header';
import Footer from '../Components/Footer';

export default function AdminLayout({ children, user = null }) {
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const toggleSidebar = () => {
        setSidebarCollapsed(!sidebarCollapsed);
    };

    const toggleMobileSidebar = () => {
        setSidebarOpen(!sidebarOpen);
    };

    return (
        <div className={`admin-layout ${sidebarCollapsed ? 'sidebar-collapsed' : ''}`}>
            <div className="admin-wrapper">
                <Sidebar
                    collapsed={sidebarCollapsed}
                    open={sidebarOpen}
                    onClose={() => setSidebarOpen(false)}
                />

                <div className="admin-main">
                    <Header
                        user={user}
                        onToggleSidebar={toggleSidebar}
                        onToggleMobileSidebar={toggleMobileSidebar}
                    />

                    <main className="admin-content">
                        {children}
                    </main>

                    <Footer />
                </div>
            </div>

            {/* Mobile sidebar toggle */}
            <button
                className="sidebar-toggle"
                onClick={toggleMobileSidebar}
                aria-label="Toggle sidebar"
            >
                <i className="bi bi-list"></i>
            </button>

            {/* Mobile overlay */}
            {sidebarOpen && (
                <div
                    className="sidebar-overlay"
                    onClick={() => setSidebarOpen(false)}
                    style={{
                        position: 'fixed',
                        inset: 0,
                        background: 'rgba(0,0,0,0.5)',
                        zIndex: 1039,
                    }}
                />
            )}
        </div>
    );
}
