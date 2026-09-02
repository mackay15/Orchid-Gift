import React from 'react';

const trustItems = [
  {
    icon: (
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
      </svg>
    ),
    heading: 'Handpicked With Love',
    sub: 'Every item is curated with care and elegance',
  },
  {
    icon: (
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
        <polyline points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>
    ),
    heading: 'Quality You Can Trust',
    sub: 'Premium products that truly impress',
  },
  {
    icon: (
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
    ),
    heading: 'Support 24/7',
    sub: "We're always here when you need us",
  },
  {
    icon: (
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="8" r="7"/><path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/>
      </svg>
    ),
    heading: 'Happy Customers',
    sub: '5,000+ delighted customers and growing',
  },
];

export default function TrustSection() {
  return (
    <section className="bg-cream-100 border-y border-cream-300" aria-label="Trust and quality assurance">
      <div className="max-w-[1400px] mx-auto px-6">
        <div className="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-cream-300">
          {trustItems.map((item, i) => (
            <div key={i} className="home-trust-item">
              <div className="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center mb-1">
                {item.icon}
              </div>
              <h3 className="font-semibold text-sm text-ink-900 leading-tight">{item.heading}</h3>
              <p className="text-xs text-ink-600 leading-relaxed max-w-[160px]">{item.sub}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
