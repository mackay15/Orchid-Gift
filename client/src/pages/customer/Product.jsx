import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import Navbar from '../../components/Navbar';
import Footer from '../../components/Footer';
import StarRating from '../../components/StarRating';
import { useCart } from '../../context/CartContext';
import { useAuth } from '../../context/AuthContext';
import api from '../../api/axios';

export default function Product() {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(true);
  const [inWishlist, setInWishlist] = useState(false);

  // Review form state
  const [newRating, setNewRating] = useState(5);
  const [newReview, setNewReview] = useState('');
  const [reviewSubmitting, setReviewSubmitting] = useState(false);
  const [reviewMsg, setReviewMsg] = useState('');

  const { addItem } = useCart();
  const { isLoggedIn, isCustomer } = useAuth();

  useEffect(() => {
    async function loadProduct() {
      try {
        const res = await api.get(`/products/${id}`);
        setProduct(res.data);

        if (isLoggedIn) {
          const wishRes = await api.get('/wishlist');
          const isSaved = wishRes.data.some((item) => item.product_id === parseInt(id));
          setInWishlist(isSaved);
        }
      } catch (err) {
        console.error('Failed to load product details', err);
      } finally {
        setLoading(false);
      }
    }
    loadProduct();
  }, [id, isLoggedIn]);

  const handleWishlistToggle = async () => {
    if (!isLoggedIn) return;
    try {
      const res = await api.post(`/wishlist/${id}`);
      setInWishlist(res.data.action === 'added');
    } catch (err) {
      console.error('Wishlist error', err);
    }
  };

  const handleReviewSubmit = async (e) => {
    e.preventDefault();
    setReviewSubmitting(true);
    setReviewMsg('');
    try {
      await api.post('/reviews', {
        product_id: parseInt(id),
        rating: newRating,
        review: newReview,
      });
      setReviewMsg('Thank you! Your review was submitted for admin approval.');
      setNewReview('');
    } catch (err) {
      setReviewMsg(err.response?.data?.error || 'Failed to submit review.');
    } finally {
      setReviewSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="home-page min-h-screen flex flex-col">
        <Navbar />
        <div className="py-16 max-w-[1400px] mx-auto px-6 w-full flex-1">
          <div className="bg-white border border-cream-300 rounded-2xl h-96 animate-pulse" />
        </div>
        <Footer />
      </div>
    );
  }

  if (!product) {
    return (
      <div className="home-page min-h-screen flex flex-col">
        <Navbar />
        <div className="py-24 text-center text-ink-600 flex-1">Product not found.</div>
        <Footer />
      </div>
    );
  }

  const isOut = product.stock_quantity <= 0;

  return (
    <div className="home-page min-h-screen flex flex-col selection:bg-rose-100 selection:text-rose-600">
      <Navbar />

      <main className="py-12 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto flex-1 w-full">
        <Link to="/shop" className="text-xs text-ink-600 hover:text-rose-500 transition-colors inline-flex items-center gap-1 mb-6 font-medium">
          ← Back to Shop
        </Link>

        {/* Product Details Header */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 bg-white border border-cream-300 rounded-2xl p-8 sm:p-10 shadow-sm">
          {/* Image */}
          <div className="relative aspect-square rounded-2xl overflow-hidden bg-cream-100 border border-cream-300">
            <img
              src={product.image_url || 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=600&q=80'}
              alt={product.name}
              className="w-full h-full object-cover"
            />
          </div>

          {/* Details */}
          <div className="flex flex-col justify-between space-y-6">
            <div>
              <span className="bg-rose-50 border border-rose-200 text-rose-600 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider mb-3 inline-block">
                {product.category_name}
              </span>
              <h1 className="font-bold text-3xl sm:text-4xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                {product.name}
              </h1>
              <p className="font-bold text-3xl text-rose-500 mt-3">
                GH₵{parseFloat(product.price).toFixed(2)}
              </p>
              <p className="text-ink-600 text-sm leading-relaxed mt-4">{product.description}</p>

              <div className="mt-6 flex items-center gap-4">
                <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
                  isOut ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                }`}>
                  {isOut ? 'Out of Stock' : `${product.stock_quantity} available`}
                </span>
                {isLoggedIn && (
                  <button
                    onClick={handleWishlistToggle}
                    className={`text-xs font-semibold px-4 py-2 rounded-xl border transition-all ${
                      inWishlist
                        ? 'bg-rose-50 border-rose-300 text-rose-600'
                        : 'bg-white border-cream-300 text-ink-600 hover:text-rose-500 hover:border-rose-300'
                    }`}
                  >
                    {inWishlist ? '❤️ In Wishlist' : '🤍 Add to Wishlist'}
                  </button>
                )}
              </div>
            </div>

            {/* Actions */}
            {(!isLoggedIn || isCustomer) && (
              <div className="pt-6 border-t border-cream-300 flex items-center gap-4 flex-wrap">
                <div className="flex items-center gap-3 bg-cream-100 border border-cream-300 rounded-xl px-4 py-2">
                  <button
                    onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                    className="text-ink-600 hover:text-rose-500 font-bold text-lg px-1"
                  >
                    -
                  </button>
                  <span className="font-semibold text-ink-900 px-2">{quantity}</span>
                  <button
                    onClick={() => setQuantity((q) => Math.min(product.stock_quantity, q + 1))}
                    className="text-ink-600 hover:text-rose-500 font-bold text-lg px-1"
                  >
                    +
                  </button>
                </div>

                <button
                  disabled={isOut}
                  onClick={() => addItem(product, quantity)}
                  className="home-btn-primary py-3.5 px-8 text-sm flex-1 justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  ADD {quantity} TO CART — GH₵{(parseFloat(product.price) * quantity).toFixed(2)}
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Reviews Section */}
        <div className="mt-12 bg-white border border-cream-300 rounded-2xl p-8 sm:p-10 shadow-sm">
          <h2 className="font-bold text-2xl text-ink-900 mb-6" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
            Customer Reviews
          </h2>

          {/* Existing Reviews */}
          {product.reviews && product.reviews.length > 0 ? (
            <div className="space-y-4 mb-8">
              {product.reviews.map((rev) => (
                <div key={rev.review_id} className="p-4 rounded-xl bg-cream-100 border border-cream-300 space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="font-semibold text-ink-900 text-sm">{rev.full_name}</span>
                    <StarRating rating={rev.rating} />
                  </div>
                  <p className="text-ink-600 text-xs leading-relaxed">{rev.review}</p>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-ink-600 text-sm mb-8">No approved reviews yet for this product.</p>
          )}

          {/* Submit Review */}
          {isLoggedIn && isCustomer && (
            <div className="pt-6 border-t border-cream-300">
              <h3 className="font-bold text-lg text-ink-900 mb-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Write a Review
              </h3>
              {reviewMsg && (
                <p className="mb-4 text-xs font-medium text-rose-700 bg-rose-50 border border-rose-200 p-3 rounded-xl">
                  {reviewMsg}
                </p>
              )}
              <form onSubmit={handleReviewSubmit} className="space-y-4 max-w-xl">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Rating</label>
                  <StarRating rating={newRating} setRating={setNewRating} readOnly={false} />
                </div>
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Review</label>
                  <textarea
                    rows={3}
                    required
                    value={newReview}
                    onChange={(e) => setNewReview(e.target.value)}
                    placeholder="Share your feedback..."
                    className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-300 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                  />
                </div>
                <button type="submit" disabled={reviewSubmitting} className="home-btn-primary py-2.5 px-6 text-sm">
                  {reviewSubmitting ? 'SUBMITTING...' : 'SUBMIT REVIEW'}
                </button>
              </form>
            </div>
          )}
        </div>
      </main>

      <Footer />
    </div>
  );
}
