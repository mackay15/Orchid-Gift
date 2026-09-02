import React from 'react';
import { Link } from 'react-router-dom';

const TruckIcon = () => (
  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
    <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><path d="M9 17h6"/>
    <rect x="13" y="10" width="8" height="7" rx="1"/><circle cx="8" cy="17" r="2"/><circle cx="19" cy="17" r="2"/>
  </svg>
);
const SparkleIcon = () => (
  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 2l2.4 7.4L22 12l-7.6 2.6L12 22l-2.4-7.4L2 12l7.6-2.6L12 2z"/>
  </svg>
);
const TagIcon = () => (
  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
    <line x1="7" y1="7" x2="7.01" y2="7"/>
  </svg>
);

const promos = [
  {
    bg: 'linear-gradient(135deg, #fce8ed 0%, #f5d3da 100%)',
    borderColor: '#D9A6A6',
    icon: <TruckIcon />,
    iconColor: '#B85C73',
    eyebrow: 'Limited Offer',
    title: 'FREE SHIPPING',
    subtitle: 'On all orders over GH₵300',
    cta: 'Shop Now',
    to: '/shop',
    ctaStyle: 'home-btn-primary',
  },
  {
    bg: 'linear-gradient(135deg, #F8F5F2 0%, #F0EAE4 100%)',
    borderColor: '#E8E2DE',
    icon: <SparkleIcon />,
    iconColor: '#B85C73',
    eyebrow: 'Make It Unique',
    title: 'PERSONALIZED GIFTS',
    subtitle: 'Add a heartfelt personal touch',
    cta: 'Explore Now',
    to: '/shop?personalized=1',
    ctaStyle: 'home-btn-secondary',
  },
  {
    bg: 'linear-gradient(135deg, #2B2B2B 0%, #3d2b2b 100%)',
    borderColor: '#4a3a3a',
    icon: <TagIcon />,
    iconColor: '#D9A6A6',
    eyebrow: 'Exclusive Deal',
    title: 'EXTRA 10% OFF',
    subtitle: (
      <>Use code:{' '}
        <span className="font-mono font-bold tracking-wider"
          style={{ background: 'rgba(255,255,255,0.12)', padding: '2px 8px', borderRadius: '4px' }}>
          ORCHID10
        </span>
      </>
    ),
    cta: 'Shop Now',
    to: '/shop',
    ctaStyle: 'home-btn-primary',
    dark: true,
  },
];

export default function PromoSection() {
  return (
    <section className="py-16 bg-cream-100" aria-label="Promotional offers">
      <div className="max-w-[1400px] mx-auto px-6">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
          {promos.map((p, i) => (
            <div
              key={i}
              className="home-promo-card"
              style={{ background: p.bg, border: `1px solid ${p.borderColor}` }}
            >
              {/* Icon */}
              <div className="p-3 rounded-xl inline-flex" style={{ background: 'rgba(255,255,255,0.25)', color: p.iconColor }}>
                {p.icon}
              </div>

              {/* Text */}
              <div>
                <p className="text-xs font-semibold tracking-[0.15em] uppercase mb-1"
                  style={{ color: p.dark ? '#D9A6A6' : '#B85C73' }}>
                  {p.eyebrow}
                </p>
                <h3 className="font-bold text-xl leading-tight mb-2"
                  style={{
                    fontFamily: "'Playfair Display', Georgia, serif",
                    color: p.dark ? '#F8F5F2' : '#2B2B2B',
                  }}>
                  {p.title}
                </h3>
                <p className="text-sm leading-relaxed"
                  style={{ color: p.dark ? '#c8c8c8' : '#6B6B6B' }}>
                  {p.subtitle}
                </p>
              </div>

              {/* CTA */}
              <Link
                to={p.to}
                className={`${p.ctaStyle} mt-2 text-sm py-3 px-6`}
                style={p.dark && p.ctaStyle === 'home-btn-secondary' ? {
                  borderColor: 'rgba(255,255,255,0.25)',
                  color: '#F8F5F2',
                } : {}}
              >
                {p.cta} →
              </Link>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
