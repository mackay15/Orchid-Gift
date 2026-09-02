import React from 'react';
import { Link } from 'react-router-dom';
import HomeProductCard from './HomeProductCard';

const SkeletonCard = () => (
  <div className="home-product-card animate-pulse">
    <div className="card-image-wrap" style={{ background: '#F0EAE4' }} />
    <div className="card-body space-y-3">
      <div className="h-4 bg-cream-300 rounded w-3/4" />
      <div className="h-3 bg-cream-200 rounded w-1/2" />
      <div className="h-6 bg-cream-200 rounded w-1/3" />
    </div>
  </div>
);

export default function BestSellersSection({ products = [], loading = false }) {
  return (
    <section className="py-16 bg-white" aria-labelledby="bestsellers-heading">
      <div className="max-w-[1400px] mx-auto px-6">

        {/* Header */}
        <div className="flex items-end justify-between mb-10 gap-4 flex-wrap">
          <div>
            <p className="home-section-label" style={{ justifyContent: 'flex-start' }}>
              <span className="h-px w-8 bg-blush-400" style={{ flex: 'none' }} />
              Featured
            </p>
            <h2 id="bestsellers-heading" className="home-section-title text-left mt-1 mb-0">
              Best Sellers
            </h2>
            <p className="text-ink-600 text-sm mt-1">Handpicked favourites loved by our customers</p>
          </div>
          <Link to="/shop"
            className="flex items-center gap-1.5 text-rose-500 font-semibold text-sm hover:text-rose-600 transition-colors whitespace-nowrap shrink-0">
            VIEW ALL BEST SELLERS
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>

        {/* Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-5">
          {loading
            ? [1,2,3,4].map(n => <SkeletonCard key={n} />)
            : products.slice(0, 8).map((product, i) => (
                <HomeProductCard key={product.product_id} product={product} index={i} />
              ))
          }
        </div>
      </div>
    </section>
  );
}
