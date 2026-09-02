import React from 'react';
import { Link } from 'react-router-dom';

// Default fallback categories when API data is available
const DEFAULT_ICONS = {
  default: (
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
    </svg>
  ),
};

const STATIC_CATS = [
  {
    name: 'Birthday Gifts', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2 1 2 1"/><line x1="2" y1="21" x2="22" y2="21"/>
        <path d="M7 8v2"/><path d="M12 8v2"/><path d="M17 8v2"/><path d="M7 4s1.5-2 2.5-2 2.5 2 2.5 2"/><path d="M12 4s1.5-2 2.5-2 2.5 2 2.5 2"/>
      </svg>
    ),
  },
  {
    name: 'Anniversary', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
      </svg>
    ),
  },
  {
    name: 'Personalized', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
      </svg>
    ),
  },
  {
    name: 'Wedding Gifts', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/>
        <path d="M12 3v2M4.22 10.22l1.42 1.42M1 18h2M21 18h2M19.78 10.22l-1.42 1.42"/>
      </svg>
    ),
  },
  {
    name: 'Gifts for Him', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
      </svg>
    ),
  },
  {
    name: 'Gifts for Her', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="12" cy="8" r="6"/><path d="M12 14v6M9 17h6M8 2s1.5 2 4 2 4-2 4-2"/>
      </svg>
    ),
  },
  {
    name: 'Home Decor', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
    ),
  },
  {
    name: 'Plants & Flowers', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 22V12"/><path d="M5 12c0-3.87 3.13-7 7-7s7 3.13 7 7"/><path d="M12 12C12 8 8 4 4 4c0 4 3.58 7.46 8 8z"/><path d="M12 12c0-4-4-8-8-8 0 4 3.58 7.46 8 8z"/>
      </svg>
    ),
  },
  {
    name: 'Gift Hampers', icon: (
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
      </svg>
    ),
  },
];

export default function CategorySection({ apiCategories = [] }) {
  // Use API categories if available (mapped to show icon), otherwise fall back to statics
  const cats = apiCategories.length > 0
    ? apiCategories.slice(0, 9).map((c, i) => ({
        name: c.name,
        to: `/shop?category=${c.category_id}`,
        icon: STATIC_CATS[i % STATIC_CATS.length]?.icon || DEFAULT_ICONS.default,
      }))
    : STATIC_CATS.map((c) => ({ ...c, to: `/shop?category=${encodeURIComponent(c.name)}` }));

  return (
    <section className="py-16 bg-cream-100" aria-labelledby="categories-heading">
      <div className="max-w-[1400px] mx-auto px-6">
        {/* Section header */}
        <p className="home-section-label">Shop by Category</p>
        <h2 id="categories-heading" className="home-section-title">Explore Our Collections</h2>
        <p className="home-section-subtitle">Find the perfect gift for every person and every occasion</p>

        {/* Category grid */}
        <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-9 gap-3 sm:gap-4">
          {cats.map((cat, i) => (
            <Link key={i} to={cat.to} className="home-category-card" aria-label={cat.name}>
              <div className="cat-icon-wrap">
                {cat.icon}
              </div>
              <span className="cat-name">{cat.name}</span>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
