import React from 'react';
import { Link } from 'react-router-dom';

/* ── Social Icons ─────────────────────────────────────────── */
const FacebookIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
  </svg>
);
const InstagramIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
  </svg>
);
const PinterestIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
  </svg>
);
const TikTokIcon = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
    <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.93a8.2 8.2 0 0 0 4.81 1.53V7.01a4.85 4.85 0 0 1-1.04-.32z"/>
  </svg>
);

const SHOP_LINKS = ['All Gifts', 'Best Sellers', 'New Arrivals', 'Personalized Gifts', 'Gift Hampers', 'Sale'];
const OCCASION_LINKS = ['Birthday', 'Anniversary', 'Wedding', "Valentine's Day", "Mother's Day", 'Christmas'];
const HELP_LINKS = ['Track Your Order', 'Shipping Policy', 'Returns & Refunds', 'FAQs', 'Terms & Conditions', 'Privacy Policy'];
const ABOUT_LINKS = ['Our Story', 'Blog', 'Corporate Gifting', 'Careers'];

const SOCIAL = [
  { Icon: FacebookIcon,  label: 'Facebook',  href: '#' },
  { Icon: InstagramIcon, label: 'Instagram', href: '#' },
  { Icon: PinterestIcon, label: 'Pinterest', href: '#' },
  { Icon: TikTokIcon,   label: 'TikTok',    href: '#' },
];

const PAYMENT_METHODS = ['Visa', 'Mastercard', 'PayPal', 'Amex'];

export default function HomeFooter() {
  return (
    <footer className="home-footer" role="contentinfo">
      <div className="max-w-[1400px] mx-auto px-6">

        {/* ── Main Footer Grid ───────────────────────── */}
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-8 pb-12 border-b border-white/10">

          {/* Col 1 – Brand */}
          <div className="col-span-2 sm:col-span-1">
            <Link to="/" className="flex items-center gap-3 mb-5 group" aria-label="Orchid Gift homepage">
              <div className="w-10 h-10 rounded-xl overflow-hidden">
                <img src="/orchid_logo.png" alt="Orchid Gift" className="w-full h-full object-cover" />
              </div>
              <div>
                <p className="font-bold text-base text-cream-50"
                  style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  Orchid Gift
                </p>
                <p className="text-xs text-blush-400">&amp; More</p>
              </div>
            </Link>
            <p className="text-ink-300 text-sm leading-relaxed mb-6 max-w-[220px]">
              We craft unforgettable gifting experiences — from fresh bouquets and artisan chocolates to luxury hampers and personalized keepsakes.
            </p>
            {/* Social Icons */}
            <div className="flex items-center gap-2.5">
              {SOCIAL.map(({ Icon, label, href }) => (
                <a key={label} href={href} aria-label={label}
                  className="w-9 h-9 rounded-lg flex items-center justify-center text-ink-300
                             hover:text-cream-50 transition-all"
                  style={{ background: 'rgba(255,255,255,0.07)', border: '1px solid rgba(255,255,255,0.08)' }}
                  onMouseEnter={e => e.currentTarget.style.background='#B85C73'}
                  onMouseLeave={e => e.currentTarget.style.background='rgba(255,255,255,0.07)'}
                >
                  <Icon />
                </a>
              ))}
            </div>
          </div>

          {/* Col 2 – Shop */}
          <div>
            <h4 className="text-cream-50 font-semibold text-xs uppercase tracking-[0.15em] mb-5">Shop</h4>
            <ul>
              {SHOP_LINKS.map(l => (
                <li key={l}>
                  <Link to="/shop" className="home-footer-link">{l}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Col 3 – Occasions */}
          <div>
            <h4 className="text-cream-50 font-semibold text-xs uppercase tracking-[0.15em] mb-5">Occasions</h4>
            <ul>
              {OCCASION_LINKS.map(l => (
                <li key={l}>
                  <Link to="/shop" className="home-footer-link">{l}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Col 4 – Help */}
          <div>
            <h4 className="text-cream-50 font-semibold text-xs uppercase tracking-[0.15em] mb-5">Help &amp; Info</h4>
            <ul>
              {HELP_LINKS.map(l => (
                <li key={l}>
                  <Link to="/shop" className="home-footer-link">{l}</Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Col 5 – About */}
          <div>
            <h4 className="text-cream-50 font-semibold text-xs uppercase tracking-[0.15em] mb-5">About Us</h4>
            <ul>
              {ABOUT_LINKS.map(l => (
                <li key={l}>
                  <Link to="/shop" className="home-footer-link">{l}</Link>
                </li>
              ))}
            </ul>

            {/* App Download (decorative) */}
            <div className="mt-6 space-y-2">
              {['App Store', 'Google Play'].map(store => (
                <button key={store}
                  className="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-cream-50 text-xs font-medium transition-colors"
                  style={{ background: 'rgba(255,255,255,0.07)', border: '1px solid rgba(255,255,255,0.12)' }}
                >
                  <span aria-hidden="true">{store === 'App Store' ? '🍎' : '▶'}</span>
                  {store}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* ── Bottom Bar ──────────────────────────────── */}
        <div className="py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          {/* Copyright */}
          <p className="text-ink-300 text-xs">
            © {new Date().getFullYear()} Orchid Gift &amp; More. All Rights Reserved.
          </p>

          {/* Payment Methods + Security */}
          <div className="flex items-center gap-3 flex-wrap justify-center">
            <div className="flex items-center gap-1.5 text-ink-300 text-xs">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
              Secure Payments
            </div>
            <div className="h-4 w-px bg-white/10" aria-hidden="true" />
            {PAYMENT_METHODS.map(m => (
              <span key={m}
                className="px-2.5 py-1 rounded text-[10px] font-bold text-ink-300"
                style={{ background: 'rgba(255,255,255,0.07)', border: '1px solid rgba(255,255,255,0.1)' }}>
                {m}
              </span>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
