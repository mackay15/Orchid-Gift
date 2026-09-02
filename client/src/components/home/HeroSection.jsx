import React from 'react';
import { Link } from 'react-router-dom';

const HeroHeartDecor = ({ className, style }) => (
  <div className={className} style={style} aria-hidden="true">
    <svg width="100%" height="100%" viewBox="0 0 40 36" fill="none">
      <path d="M20 33.5C20 33.5 2 22 2 11C2 6.03 6.03 2 11 2C14.04 2 16.71 3.45 18.5 5.68C19.02 6.31 20.98 6.31 21.5 5.68C23.29 3.45 25.96 2 29 2C33.97 2 38 6.03 38 11C38 22 20 33.5 20 33.5Z"
        fill="currentColor" opacity="0.85"/>
    </svg>
  </div>
);

export default function HeroSection() {
  return (
    <section className="relative overflow-hidden" style={{ background: 'linear-gradient(135deg, #fdf2f5 0%, #F8F5F2 60%, #fce8ed 100%)' }}
      aria-label="Hero section">
      {/* Decorative floating shapes */}
      <HeroHeartDecor
        className="float-slow absolute top-16 left-12 w-10 h-10 text-rose-300 opacity-40 hidden md:block"
        style={{ animationDelay: '0s' }}
      />
      <HeroHeartDecor
        className="float-med absolute top-32 right-24 w-7 h-7 text-blush-400 opacity-30 hidden md:block"
        style={{ animationDelay: '1.5s' }}
      />
      <HeroHeartDecor
        className="float-slow absolute bottom-20 left-[30%] w-5 h-5 text-rose-400 opacity-25 hidden lg:block"
        style={{ animationDelay: '2.5s' }}
      />
      <div className="absolute top-1/3 right-10 w-64 h-64 rounded-full opacity-10 blur-3xl pointer-events-none"
        style={{ background: 'radial-gradient(circle, #B85C73, transparent)' }}
        aria-hidden="true"
      />

      <div className="max-w-[1400px] mx-auto px-6 py-16 md:py-20 lg:py-24">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

          {/* ── Left: Content ──────────────────────────── */}
          <div className="flex flex-col items-start gap-6 order-2 lg:order-1">
            {/* Small label */}
            <div className="flex items-center gap-2">
              <span className="h-px w-8 bg-rose-400" aria-hidden="true"/>
              <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">
                Thoughtful Gifts for Every Moment
              </span>
            </div>

            {/* Heading */}
            <h1 style={{ fontFamily: "'Playfair Display', Georgia, serif" }}
              className="text-4xl sm:text-5xl lg:text-[54px] font-bold leading-[1.15] text-ink-900">
              Make Every<br />
              <span className="italic" style={{ color: '#B85C73' }}>Moment Special</span>
            </h1>

            {/* Description */}
            <p className="text-ink-600 text-base sm:text-lg leading-relaxed max-w-lg">
              Discover curated bouquets, artisan chocolate boxes, luxury hampers, and heartfelt keepsakes — crafted to make every occasion unforgettable.
            </p>

            {/* CTA Buttons */}
            <div className="flex flex-wrap items-center gap-3 mt-2">
              <Link to="/shop" id="hero-shop-now-btn" className="home-btn-primary text-sm">
                SHOP NOW
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </Link>
              <Link to="/shop" id="hero-explore-btn" className="home-btn-secondary text-sm">
                EXPLORE COLLECTIONS
              </Link>
            </div>

            {/* Social proof bar */}
            <div className="flex items-center gap-6 pt-2 mt-2 border-t border-cream-300 w-full">
              <div className="text-center">
                <p className="font-bold text-xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>5K+</p>
                <p className="text-xs text-ink-600">Happy Customers</p>
              </div>
              <div className="h-10 w-px bg-cream-300" aria-hidden="true"/>
              <div className="text-center">
                <p className="font-bold text-xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>200+</p>
                <p className="text-xs text-ink-600">Curated Products</p>
              </div>
              <div className="h-10 w-px bg-cream-300" aria-hidden="true"/>
              <div className="flex items-center gap-1">
                {[1,2,3,4,5].map(s => (
                  <svg key={s} width="14" height="14" viewBox="0 0 24 24" fill="#F59E0B" stroke="none">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                  </svg>
                ))}
                <span className="text-xs text-ink-600 ml-1">4.9 / 5</span>
              </div>
            </div>
          </div>

          {/* ── Right: Image ────────────────────────────── */}
          <div className="relative order-1 lg:order-2 flex justify-center lg:justify-end">
            {/* Decorative circle behind image */}
            <div className="absolute inset-0 m-auto rounded-full opacity-15 pointer-events-none"
              style={{
                width: '85%', height: '85%',
                background: 'radial-gradient(circle, #D9A6A6 0%, transparent 70%)',
                top: '50%', left: '50%',
                transform: 'translate(-50%, -50%)',
              }}
              aria-hidden="true"
            />
            <div className="relative w-full max-w-[520px] aspect-[4/4.5] rounded-[28px] overflow-hidden shadow-2xl border border-white"
              style={{ boxShadow: '0 24px 80px rgba(184,92,115,0.18), 0 4px 20px rgba(43,43,43,0.08)' }}>
              <img
                src="/home/hero.png"
                alt="Elegant gift arrangement with orchid flowers and premium gift boxes"
                className="w-full h-full object-cover"
                loading="eager"
              />
              {/* Small floating badge */}
              <div className="absolute bottom-6 left-6 bg-white rounded-2xl px-4 py-3 shadow-lg border border-cream-300 flex items-center gap-3">
                <div className="w-9 h-9 rounded-full bg-rose-100 flex items-center justify-center text-xl">🎁</div>
                <div>
                  <p className="text-xs font-semibold text-ink-900">Premium Gifting</p>
                  <p className="text-[11px] text-ink-600">Handpicked with love</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
