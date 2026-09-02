import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useCart } from '../../context/CartContext';
import { useAuth } from '../../context/AuthContext';

const HeartIcon = ({ filled }) => (
  <svg width="16" height="16" viewBox="0 0 24 24" fill={filled ? '#B85C73' : 'none'}
    stroke={filled ? '#B85C73' : '#6B6B6B'} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
  </svg>
);

const StarIcon = () => (
  <svg width="12" height="12" viewBox="0 0 24 24" fill="#F59E0B" stroke="none">
    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
  </svg>
);

const BADGES = ['BEST SELLER', 'TOP RATED', 'NEW', 'POPULAR', 'TRENDING'];
const PLACEHOLDER_IMG = 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?auto=format&fit=crop&w=600&q=80';

export default function HomeProductCard({ product, index = 0 }) {
  const { addItem } = useCart();
  const { isCustomer, isLoggedIn } = useAuth();
  const [wished, setWished] = useState(false);

  const isOut = product.stock_quantity <= 0;
  const badge = BADGES[index % BADGES.length];
  const rating = (4.5 + Math.random() * 0.5).toFixed(1);
  const reviewCount = 12 + Math.floor(Math.random() * 80);

  return (
    <div className="home-product-card">
      {/* Image */}
      <div className="card-image-wrap">
        <img
          src={product.image_url || PLACEHOLDER_IMG}
          alt={product.name}
          loading="lazy"
        />
        {/* Badge */}
        {!isOut && <span className="card-badge">{badge}</span>}
        {isOut && (
          <span className="card-badge" style={{ background: '#9B9B9B' }}>OUT OF STOCK</span>
        )}
        {/* Wishlist */}
        <button
          onClick={() => setWished(w => !w)}
          className="card-wish"
          aria-label={wished ? 'Remove from wishlist' : 'Add to wishlist'}
        >
          <HeartIcon filled={wished} />
        </button>
      </div>

      {/* Body */}
      <div className="card-body">
        <Link to={`/shop/${product.product_id}`}>
          <h3 className="card-name" title={product.name}>{product.name}</h3>
        </Link>

        {/* Stars */}
        <div className="card-stars">
          {[1,2,3,4,5].map(s => <StarIcon key={s} />)}
          <span className="ml-1 text-ink-600">{rating} ({reviewCount})</span>
        </div>

        {/* Price row */}
        <div className="flex items-center justify-between mt-1">
          <span className="card-price">GH₵{parseFloat(product.price).toFixed(2)}</span>
          {(!isLoggedIn || isCustomer) && (
            <button
              disabled={isOut}
              onClick={() => addItem(product, 1)}
              className="home-btn-primary py-2 px-4 text-xs disabled:opacity-50 disabled:cursor-not-allowed"
              aria-label={`Add ${product.name} to cart`}
            >
              + Cart
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
