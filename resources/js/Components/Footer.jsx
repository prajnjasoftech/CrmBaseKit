export default function Footer() {
    const currentYear = new Date().getFullYear();

    return (
        <footer className="admin-footer">
            <div className="footer-content">
                <div className="footer-copyright">
                    &copy; {currentYear} CRM Base Kit. All rights reserved.
                </div>
                <div className="footer-links">
                    <a href="/docs">Documentation</a>
                    <a href="/support">Support</a>
                    <a href="/privacy">Privacy</a>
                </div>
            </div>
        </footer>
    );
}
