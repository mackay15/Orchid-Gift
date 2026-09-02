import React, { useState } from 'react';
import { Link } from 'react-router-dom';

// Inline SVG icons to avoid adding a dependency
const TruckIcon = () => (
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><path d="M9 17h6"/><rect x="13" y="10" width="8" height="7" rx="1"/><circle cx="8" cy="17" r="2"/><circle cx="19" cy="17" r="2"/>
  </svg>
);
const TagIcon = () => (
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
  </svg>
);
const XIcon = () => (
  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
  </svg>
);

export default function AnnouncementBar() {
  const [visible, setVisible] = useState(true);
  if (!visible) return null;

  return (
    <div className="announcement-bar relative">
      <div className="max-w-[1400px] mx-auto px-6 flex items-center justify-between gap-4">

        {/* Left – Shipping */}
        <div className="hidden sm:flex items-center gap-1.5 text-cream-300">
          <TruckIcon />
          <span>Free Shipping on Orders Over <strong>GH₵300</strong></span>
        </div>

        {/* Center – Promo */}
        <div className="flex items-center gap-1.5 text-center flex-1 justify-center">
          <TagIcon />
          <span>
            <strong>10% Off</strong> Your First Order — Use Code:{' '}
            <span className="font-mono font-bold tracking-wider text-blush-300 bg-white/10 px-2 py-0.5 rounded">
              ORCHID10
            </span>
          </span>
        </div>

        {/* Right – Utility links */}
        <div className="hidden sm:flex items-center gap-4 text-ink-100 shrink-0">
          <Link to="/orders" className="hover:text-blush-300 transition-colors">
            Track Order
          </Link>
          <span className="opacity-30">|</span>
          <Link to="/shop" className="hover:text-blush-300 transition-colors">
            Help Center
          </Link>

          {/* Dismiss */}
          <button
            onClick={() => setVisible(false)}
            aria-label="Close announcement"
            className="ml-2 opacity-50 hover:opacity-100 transition-opacity"
          >
            <XIcon />
          </button>
        </div>
      </div>
    </div>
  );
}
