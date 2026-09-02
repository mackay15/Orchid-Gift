import React from 'react';
import { Link } from 'react-router-dom';

const occasions = [
  {
    title: 'Birthday Gifts',
    icon: '🎂',
    img: '/home/occasion-birthday.png',
    to: '/shop?occasion=birthday',
  },
  {
    title: 'Anniversary',
    icon: '💍',
    img: '/home/occasion-anniversary.png',
    to: '/shop?occasion=anniversary',
  },
  {
    title: 'Wedding Gifts',
    icon: '💐',
    img: '/home/occasion-wedding.png',
    to: '/shop?occasion=wedding',
  },
  {
    title: 'Thank You Gifts',
    icon: '🙏',
    img: '/home/occasion-thankyou.png',
    to: '/shop?occasion=thankyou',
  },
  {
    title: 'Congratulations',
    icon: '🥂',
    img: 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=600&q=80',
    to: '/shop?occasion=congratulations',
  },
  {
    title: 'Christmas Gifts',
    icon: '🎄',
    img: '/home/occasion-christmas.png',
    to: '/shop?occasion=christmas',
  },
];

export default function OccasionSection() {
  return (
    <section className="py-16 bg-white" aria-labelledby="occasions-heading">
      <div className="max-w-[1400px] mx-auto px-6">

        {/* Header */}
        <div className="flex items-end justify-between mb-10 gap-4 flex-wrap">
          <div className="flex-1">
            <p className="home-section-label">Featured by Occasion</p>
            <h2 id="occasions-heading" className="home-section-title mb-0">
              Shop the Moment
            </h2>
          </div>
          <Link to="/shop"
            className="flex items-center gap-1.5 text-rose-500 font-semibold text-sm hover:text-rose-600 transition-colors shrink-0">
            VIEW ALL OCCASIONS
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>

        {/* Grid */}
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          {occasions.map((occ, i) => (
            <Link key={i} to={occ.to} className="home-occasion-card" aria-label={occ.title}>
              <img src={occ.img} alt={occ.title} loading="lazy" />
              <div className="occ-overlay" />
              <div className="occ-content">
                <div className="text-2xl mb-2" aria-hidden="true">{occ.icon}</div>
                <p className="occ-title">{occ.title}</p>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
