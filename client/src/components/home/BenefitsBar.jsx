import React from 'react';

const benefits = [
  {
    icon: (
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
      </svg>
    ),
    heading: 'Unique & Premium Gifts',
    sub: 'Curated with love and elegance',
  },
  {
    icon: (
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><path d="M9 17h6"/><rect x="13" y="10" width="8" height="7" rx="1"/><circle cx="8" cy="17" r="2"/><circle cx="19" cy="17" r="2"/>
      </svg>
    ),
    heading: 'Fast & Reliable Shipping',
    sub: 'Delivered right to your door',
  },
  {
    icon: (
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
      </svg>
    ),
    heading: 'Easy Returns & Refunds',
    sub: 'Hassle-free 30-day returns',
  },
  {
    icon: (
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
    ),
    heading: 'Secure Payments',
    sub: 'Your data is always protected',
  },
];

export default function BenefitsBar() {
  return (
    <section className="bg-white border-y border-cream-300" aria-label="Service benefits">
      <div className="max-w-[1400px] mx-auto px-6">
        <div className="flex flex-col sm:flex-row items-stretch divide-y sm:divide-y-0 sm:divide-x divide-cream-300">
          {benefits.map((b, i) => (
            <div key={i} className="home-benefit-item">
              <div className="shrink-0 w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center">
                {b.icon}
              </div>
              <div>
                <p className="font-semibold text-ink-900 text-sm leading-tight">{b.heading}</p>
                <p className="text-ink-600 text-xs mt-0.5">{b.sub}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
