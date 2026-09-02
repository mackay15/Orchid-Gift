import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useCart } from '../../context/CartContext';

/* ── Inline SVG Icons ──────────────────────────────────────── */
const SearchIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
  </svg>
);
const UserIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
  </svg>
);
const CartIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
    <path d="M16 10a4 4 0 0 1-8 0"/>
  </svg>
);
const MenuIcon = () => (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
  </svg>
);
const CloseIcon = () => (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
  </svg>
);
const ChevronIcon = ({ open }) => (
  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"
    style={{ transform: open ? 'rotate(180deg)' : 'none', transition: 'transform 0.2s' }}>
    <polyline points="6 9 12 15 18 9"/>
  </svg>
);

const NAV_LINKS = [
  { label: 'Home', to: '/' },
  { label: 'Shop', to: '/shop' },
  { label: 'Occasions', to: '/shop?occasion=1', dropdown: true },
  { label: 'Personalized', to: '/shop?personalized=1' },
  { label: 'New Arrivals', to: '/shop?sort=newest' },
  { label: 'Contact', to: '/shop' },
];

const OCCASION_DROPS = [
  { label: 'Birthday Gifts', to: '/shop?occasion=birthday' },
  { label: 'Anniversary', to: '/shop?occasion=anniversary' },
  { label: 'Wedding Gifts', to: '/shop?occasion=wedding' },
  { label: 'Thank You Gifts', to: '/shop?occasion=thankyou' },
  { label: 'Christmas Gifts', to: '/shop?occasion=christmas' },
];

export default function HomeNavbar() {
  const { user, isLoggedIn, logout, isAdmin, isCashier } = useAuth();
  const { itemCount } = useCart();
  const navigate = useNavigate();
  const location = useLocation();

  const [scrolled, setScrolled]         = useState(false);
  const [menuOpen, setMenuOpen]         = useState(false);
  const [dropOpen, setDropOpen]         = useState(false);
  const [mobileDropOpen, setMobileDropOpen] = useState(false);
  const [userDrop, setUserDrop]         = useState(false);
  const [searchOpen, setSearchOpen]     = useState(false);
  const [searchQuery, setSearchQuery]   = useState('');

  const dashboardPath = isAdmin ? '/admin' : isCashier ? '/cashier' : '/orders';

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  // Close dropdowns on route change
  useEffect(() => {
    setMenuOpen(false);
    setDropOpen(false);
    setUserDrop(false);
    setSearchOpen(false);
  }, [location.pathname]);

  const isActive = (to) => location.pathname === to || (to !== '/' && location.pathname.startsWith(to.split('?')[0]));

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/shop?search=${encodeURIComponent(searchQuery.trim())}`);
      setSearchOpen(false);
      setMenuOpen(false);
    }
  };

  return (
    <header className={`home-navbar sticky top-0 z-40 bg-white/95 backdrop-blur-md transition-all duration-300 ${scrolled ? 'shadow-md py-1' : 'border-b border-cream-300'}`}>
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6">
        <div className="flex items-center justify-between h-[68px] sm:h-[72px] gap-3">

          {/* ── Logo ─────────────────────────────────────── */}
          <Link to="/" className="flex items-center gap-2.5 sm:gap-3 shrink-0 group" aria-label="Orchid Gift homepage">
            <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-xl overflow-hidden shadow-sm group-hover:scale-105 transition-transform">
              <img src="/orchid_logo.png" alt="Orchid Gift" className="w-full h-full object-cover" />
            </div>
            <div className="leading-none">
              <span className="block font-bold text-base sm:text-[17px] text-ink-900 tracking-tight"
                style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Orchid Gift
              </span>
              <span className="block text-[10px] sm:text-[11px] text-rose-500 font-medium tracking-wide">
                &amp; More
              </span>
            </div>
          </Link>

          {/* ── Desktop Nav ───────────────────────────────── */}
          <nav className="hidden lg:flex items-center gap-1" aria-label="Main navigation">
            {NAV_LINKS.map((link) =>
              link.dropdown ? (
                <div key={link.label} className="relative">
                  <button
                    onClick={() => setDropOpen(o => !o)}
                    className={`home-nav-link flex items-center gap-1.5 ${dropOpen ? 'active' : ''}`}
                  >
                    {link.label}
                    <ChevronIcon open={dropOpen} />
                  </button>
                  {dropOpen && (
                    <div className="absolute top-full left-0 mt-2 bg-white border border-cream-300 rounded-xl shadow-lg py-2 w-48 z-50 animate-fade-in">
                      {OCCASION_DROPS.map(d => (
                        <Link key={d.to} to={d.to}
                          onClick={() => setDropOpen(false)}
                          className="block px-4 py-2.5 text-sm text-ink-600 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                          {d.label}
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              ) : (
                <Link key={link.to} to={link.to}
                  className={`home-nav-link ${isActive(link.to) ? 'active' : ''}`}>
                  {link.label}
                </Link>
              )
            )}
          </nav>

          {/* ── Right Actions ─────────────────────────────── */}
          <div className="flex items-center gap-1 sm:gap-1.5">

            {/* Search Button */}
            <button
              onClick={() => setSearchOpen(o => !o)}
              aria-label="Toggle Search"
              className={`p-2 sm:p-2.5 rounded-lg text-ink-600 hover:text-rose-500 hover:bg-rose-50 transition-colors ${searchOpen ? 'bg-rose-50 text-rose-500' : ''}`}
            >
              <SearchIcon />
            </button>

            {/* Cart Button */}
            {(!isLoggedIn || user?.role === 'customer') && (
              <Link to="/cart" id="home-navbar-cart" aria-label="Shopping cart"
                className="relative p-2 sm:p-2.5 rounded-lg text-ink-600 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                <CartIcon />
                {itemCount > 0 && (
                  <span className="absolute -top-0.5 -right-0.5 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shadow-xs">
                    {itemCount > 9 ? '9+' : itemCount}
                  </span>
                )}
              </Link>
            )}

            {/* User Dropdown / Auth Buttons */}
            {isLoggedIn ? (
              <div className="relative ml-0.5 sm:ml-1">
                <button id="home-user-btn"
                  onClick={() => setUserDrop(o => !o)}
                  className="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg border border-cream-300 hover:border-rose-300 hover:bg-rose-50 transition-all"
                  aria-expanded={userDrop}
                  aria-label="User menu"
                >
                  <div className="w-7 h-7 rounded-full bg-rose-500 flex items-center justify-center text-white text-xs font-bold shadow-xs">
                    {user.full_name?.[0] || 'U'}
                  </div>
                  <span className="hidden md:block text-sm font-medium text-ink-900 max-w-[90px] truncate">
                    {user.full_name?.split(' ')[0]}
                  </span>
                  <ChevronIcon open={userDrop} />
                </button>

                {userDrop && (
                  <div className="absolute right-0 mt-2 w-52 bg-white border border-cream-300 rounded-xl shadow-xl py-2 z-50 animate-fade-in">
                    <div className="px-4 py-3 border-b border-cream-300">
                      <p className="text-xs text-ink-500">Signed in as</p>
                      <p className="text-sm font-semibold text-ink-900 truncate">{user.full_name}</p>
                      <span className="inline-block mt-1 text-[10px] uppercase font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                        {user.role}
                      </span>
                    </div>
                    <Link to={dashboardPath} onClick={() => setUserDrop(false)} className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-ink-600 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                      📊 Dashboard
                    </Link>
                    {user?.role === 'customer' && (
                      <>
                        <Link to="/orders" onClick={() => setUserDrop(false)} className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-ink-600 hover:text-rose-500 hover:bg-rose-50 transition-colors">📦 My Orders</Link>
                        <Link to="/wishlist" onClick={() => setUserDrop(false)} className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-ink-600 hover:text-rose-500 hover:bg-rose-50 transition-colors">❤️ Wishlist</Link>
                      </>
                    )}
                    <button onClick={() => { logout(); navigate('/login'); }}
                      className="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 border-t border-cream-300 mt-1 transition-colors">
                      🚪 Sign Out
                    </button>
                  </div>
                )}
              </div>
            ) : (
              <div className="hidden sm:flex items-center gap-2 ml-1">
                <Link to="/login"    className="home-btn-secondary py-1.5 px-3.5 text-xs sm:text-sm">Login</Link>
                <Link to="/register" className="home-btn-primary  py-1.5 px-3.5 text-xs sm:text-sm">Register</Link>
              </div>
            )}

            {/* Mobile Hamburger Toggle */}
            <button
              className="lg:hidden p-2 sm:p-2.5 rounded-lg text-ink-700 hover:bg-rose-50 transition-colors ml-0.5"
              onClick={() => setMenuOpen(o => !o)}
              aria-label={menuOpen ? 'Close menu' : 'Open menu'}
            >
              {menuOpen ? <CloseIcon /> : <MenuIcon />}
            </button>
          </div>
        </div>

        {/* ── Search Dropdown / Input Bar ──────────────────── */}
        {searchOpen && (
          <div className="pb-4 pt-1 animate-fade-in border-t border-cream-200 mt-1">
            <form onSubmit={handleSearchSubmit} className="flex items-center gap-2 max-w-xl mx-auto">
              <input
                type="text"
                autoFocus
                placeholder="Search flowers, hampers, chocolates, gifts..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="flex-1 px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-400 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
              />
              <button type="submit" className="home-btn-primary py-2.5 px-5 text-xs sm:text-sm shrink-0">
                Search
              </button>
              <button
                type="button"
                onClick={() => setSearchOpen(false)}
                className="p-2.5 rounded-xl text-ink-500 hover:text-ink-800 transition-colors"
                aria-label="Close search"
              >
                ✕
              </button>
            </form>
          </div>
        )}
      </div>

      {/* ── Mobile Navigation Drawer ───────────────────────── */}
      {menuOpen && (
        <div className="lg:hidden border-t border-cream-300 bg-white shadow-xl animate-slide-up max-h-[calc(100vh-72px)] overflow-y-auto">
          <div className="max-w-[1400px] mx-auto px-6 py-5 flex flex-col gap-1.5">
            {NAV_LINKS.map(link => (
              link.dropdown ? (
                <div key={link.label} className="flex flex-col">
                  <button
                    onClick={() => setMobileDropOpen(o => !o)}
                    className={`flex items-center justify-between py-3 px-3 rounded-xl text-sm font-semibold text-ink-800 hover:bg-rose-50 transition-colors ${mobileDropOpen ? 'bg-rose-50/70 text-rose-600' : ''}`}
                  >
                    <span>{link.label}</span>
                    <ChevronIcon open={mobileDropOpen} />
                  </button>
                  {mobileDropOpen && (
                    <div className="pl-4 pr-2 py-1 space-y-1 bg-cream-50/70 rounded-xl my-1 border border-cream-200">
                      {OCCASION_DROPS.map(d => (
                        <Link
                          key={d.to}
                          to={d.to}
                          onClick={() => setMenuOpen(false)}
                          className="block py-2 px-3 text-xs font-medium text-ink-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                        >
                          {d.label}
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              ) : (
                <Link
                  key={link.to}
                  to={link.to}
                  onClick={() => setMenuOpen(false)}
                  className={`block py-3 px-3 rounded-xl text-sm font-semibold transition-colors ${
                    isActive(link.to) ? 'bg-rose-50 text-rose-600 font-bold' : 'text-ink-800 hover:bg-rose-50'
                  }`}
                >
                  {link.label}
                </Link>
              )
            ))}

            <hr className="border-cream-300 my-2" />

            {/* Mobile Auth and User State */}
            {isLoggedIn ? (
              <div className="space-y-2 pt-1">
                <div className="flex items-center gap-3 p-3 bg-cream-100 rounded-xl border border-cream-200">
                  <div className="w-10 h-10 rounded-full bg-rose-500 flex items-center justify-center text-white text-sm font-bold shadow-xs">
                    {user.full_name?.[0] || 'U'}
                  </div>
                  <div className="min-w-0 flex-1">
                    <p className="text-sm font-bold text-ink-900 truncate">{user.full_name}</p>
                    <p className="text-xs text-ink-500 capitalize">{user.role}</p>
                  </div>
                </div>

                <Link
                  to={dashboardPath}
                  onClick={() => setMenuOpen(false)}
                  className="flex items-center gap-3 py-3 px-3 rounded-xl text-sm font-medium text-ink-700 hover:bg-rose-50 transition-colors"
                >
                  <span>📊</span> Dashboard
                </Link>

                {user?.role === 'customer' && (
                  <>
                    <Link
                      to="/orders"
                      onClick={() => setMenuOpen(false)}
                      className="flex items-center gap-3 py-3 px-3 rounded-xl text-sm font-medium text-ink-700 hover:bg-rose-50 transition-colors"
                    >
                      <span>📦</span> My Orders
                    </Link>
                    <Link
                      to="/wishlist"
                      onClick={() => setMenuOpen(false)}
                      className="flex items-center gap-3 py-3 px-3 rounded-xl text-sm font-medium text-ink-700 hover:bg-rose-50 transition-colors"
                    >
                      <span>❤️</span> My Wishlist
                    </Link>
                  </>
                )}

                <button
                  onClick={() => { logout(); navigate('/login'); setMenuOpen(false); }}
                  className="w-full flex items-center justify-center gap-2 py-3 mt-2 rounded-xl text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 transition-colors"
                >
                  <span>🚪</span> Sign Out
                </button>
              </div>
            ) : (
              <div className="flex flex-col gap-2 pt-2">
                <Link
                  to="/login"
                  onClick={() => setMenuOpen(false)}
                  className="home-btn-secondary text-center py-3 text-sm"
                >
                  Sign In
                </Link>
                <Link
                  to="/register"
                  onClick={() => setMenuOpen(false)}
                  className="home-btn-primary text-center py-3 text-sm"
                >
                  Create Account
                </Link>
              </div>
            )}
          </div>
        </div>
      )}
    </header>
  );
}
