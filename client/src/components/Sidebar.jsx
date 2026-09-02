import { useState, useEffect } from 'react';
import { NavLink, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const adminLinks = [
  { to: '/admin',            label: 'Dashboard',   icon: '📊' },
  { to: '/admin/products',   label: 'Products',    icon: '📦' },
  { to: '/admin/categories', label: 'Categories',  icon: '🏷️' },
  { to: '/admin/orders',     label: 'Orders',      icon: '🛍️' },
  { to: '/admin/users',      label: 'Users',       icon: '👥' },
  { to: '/admin/reviews',    label: 'Reviews',     icon: '⭐' },
  { to: '/admin/reports',    label: 'Reports',     icon: '📈' },
];

const cashierLinks = [
  { to: '/cashier',            label: 'POS Terminal', icon: '🖥️' },
  { to: '/cashier/sales',      label: 'Sales Log',    icon: '💰' },
  { to: '/cashier/customers',  label: 'Customers',    icon: '👤' },
  { to: '/cashier/logs',       label: 'Activity Log', icon: '📋' },
];

export default function Sidebar() {
  const { user, logout, isAdmin } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [mobileOpen, setMobileOpen] = useState(false);
  const links = isAdmin ? adminLinks : cashierLinks;

  // Auto-close mobile sidebar on location change
  useEffect(() => {
    setMobileOpen(false);
  }, [location.pathname]);

  return (
    <>
      {/* ── Mobile Top Header Bar ─────────────────────────────── */}
      <header className="lg:hidden fixed top-0 left-0 right-0 h-16 bg-white border-b border-cream-300 z-30 px-4 flex items-center justify-between shadow-xs">
        <div className="flex items-center gap-3">
          <button
            onClick={() => setMobileOpen(true)}
            className="p-2 rounded-xl text-ink-700 hover:bg-cream-100 transition-colors"
            aria-label="Open navigation menu"
          >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
              <line x1="3" y1="12" x2="21" y2="12" /><line x1="3" y1="6" x2="21" y2="6" /><line x1="3" y1="18" x2="21" y2="18" />
            </svg>
          </button>

          <NavLink to="/" className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg overflow-hidden shadow-xs">
              <img src="/orchid_logo.png" alt="Orchid Gift" className="w-full h-full object-cover" />
            </div>
            <span className="font-bold text-base text-ink-900 tracking-tight" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Orchid Gift
            </span>
          </NavLink>
        </div>

        <div className="flex items-center gap-2">
          <div className="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-xs font-bold text-white shadow-xs">
            {user?.full_name?.[0] || 'U'}
          </div>
        </div>
      </header>

      {/* ── Mobile Backdrop Overlay ───────────────────────────── */}
      {mobileOpen && (
        <div
          onClick={() => setMobileOpen(false)}
          className="fixed inset-0 bg-black/50 backdrop-blur-xs z-40 lg:hidden transition-opacity"
          aria-hidden="true"
        />
      )}

      {/* ── Sidebar Navigation Container ──────────────────────── */}
      <aside
        className={`fixed left-0 top-0 h-screen w-64 bg-white border-r border-cream-300 flex flex-col py-6 z-50 shadow-xl lg:shadow-sm transform transition-transform duration-300 ease-in-out ${
          mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        }`}
      >
        {/* Logo & Close Button Header */}
        <div className="px-6 mb-6 flex items-center justify-between">
          <NavLink to="/" className="flex items-center gap-3 group" aria-label="Orchid Gift homepage">
            <div className="w-10 h-10 rounded-xl overflow-hidden shadow-sm group-hover:scale-105 transition-transform">
              <img src="/orchid_logo.png" alt="Orchid Gift" className="w-full h-full object-cover" />
            </div>
            <div className="leading-none">
              <span className="block font-bold text-lg text-ink-900 tracking-tight" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Orchid Gift
              </span>
              <span className="block text-xs text-rose-500 font-medium tracking-wide">
                &amp; More
              </span>
            </div>
          </NavLink>

          {/* Close button for mobile */}
          <button
            onClick={() => setMobileOpen(false)}
            className="lg:hidden p-1.5 rounded-lg text-ink-500 hover:text-rose-500 hover:bg-cream-100 transition-colors"
            aria-label="Close menu"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>

        {/* User Info */}
        <div className="px-4 mb-6">
          <div className="bg-cream-100 border border-cream-300 rounded-xl p-3 flex items-center gap-3">
            <div className="w-9 h-9 rounded-full bg-rose-500 flex items-center justify-center text-sm font-bold text-white shrink-0 shadow-xs">
              {user?.full_name?.[0] || 'U'}
            </div>
            <div className="min-w-0">
              <p className="text-sm font-semibold text-ink-900 truncate">{user?.full_name}</p>
              <span className="inline-block bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-bold px-2 py-0.5 rounded-full capitalize">
                {user?.role}
              </span>
            </div>
          </div>
        </div>

        {/* Nav Links */}
        <nav className="flex-1 px-3 space-y-1 overflow-y-auto">
          {links.map(link => (
            <NavLink
              key={link.to}
              to={link.to}
              end={link.to === '/admin' || link.to === '/cashier'}
              className={({ isActive }) => `flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium transition-all ${
                isActive
                  ? 'bg-rose-50 text-rose-600 border border-rose-200 font-semibold shadow-xs'
                  : 'text-ink-600 hover:text-rose-500 hover:bg-cream-100'
              }`}
            >
              <span className="text-base">{link.icon}</span>
              <span>{link.label}</span>
            </NavLink>
          ))}
        </nav>

        {/* Bottom Actions */}
        <div className="px-3 pt-4 border-t border-cream-300 space-y-1">
          <NavLink to="/" className="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium text-ink-600 hover:text-rose-500 hover:bg-cream-100 transition-all">
            <span className="text-base">🏬</span><span>View Store</span>
          </NavLink>
          <button
            id="sidebar-logout-btn"
            onClick={() => { logout(); navigate('/login'); }}
            className="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium text-red-500 hover:bg-red-50 w-full transition-all"
          >
            <span className="text-base">🚪</span><span>Sign Out</span>
          </button>
        </div>
      </aside>
    </>
  );
}
