import React from 'react';
import { Link } from 'react-router-dom';
import Navbar from '../../components/Navbar';
import Footer from '../../components/Footer';
import { useCart } from '../../context/CartContext';

export default function Cart() {
  const { items, updateQty, removeItem, clearCart, total } = useCart();

  return (
    <div className="home-page min-h-screen flex flex-col selection:bg-rose-100 selection:text-rose-600">
      <Navbar />

      <main className="py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto flex-1 w-full">
        <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">
          Your Shopping Bag
        </span>
        <h1 className="font-bold text-3xl sm:text-4xl text-ink-900 mt-1 mb-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
          Shopping Cart
        </h1>
        <p className="text-sm text-ink-600 mb-8">Review your selected items before proceeding to checkout</p>

        {items.length === 0 ? (
          <div className="bg-white border border-cream-300 rounded-2xl p-12 text-center my-8 shadow-sm">
            <span className="text-5xl">🛍️</span>
            <h3 className="font-bold text-xl text-ink-900 mt-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>Your Cart is Empty</h3>
            <p className="text-ink-600 text-sm mt-1">Looks like you haven't added any luxury gifts yet.</p>
            <Link to="/shop" className="home-btn-primary mt-6 inline-block py-3 px-8 text-sm">
              Explore Shop
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Cart Items List */}
            <div className="lg:col-span-2 space-y-4">
              {items.map((item) => (
                <div key={item.product_id} className="bg-white border border-cream-300 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4 shadow-sm">
                  <div className="flex items-center gap-3 w-full sm:w-auto flex-1 min-w-0">
                    <img
                      src={item.image_url || 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=600&q=80'}
                      alt={item.name}
                      className="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl bg-cream-100 border border-cream-300 shrink-0"
                    />
                    <div className="flex-1 min-w-0">
                      <h4 className="font-semibold text-ink-900 text-sm sm:text-base truncate">{item.name}</h4>
                      <p className="text-rose-500 text-xs sm:text-sm font-bold mt-0.5">
                        GH₵{parseFloat(item.price).toFixed(2)}
                      </p>
                    </div>
                  </div>

                  <div className="flex items-center justify-between w-full sm:w-auto gap-4 pt-2 sm:pt-0 border-t sm:border-t-0 border-cream-200">
                    <div className="flex items-center gap-2 bg-cream-100 border border-cream-300 rounded-xl px-3 py-1.5">
                      <button
                        onClick={() => updateQty(item.product_id, item.quantity - 1)}
                        className="text-ink-600 hover:text-rose-500 font-bold px-1"
                      >
                        -
                      </button>
                      <span className="text-xs font-semibold text-ink-900 px-1">{item.quantity}</span>
                      <button
                        onClick={() => updateQty(item.product_id, item.quantity + 1)}
                        className="text-ink-600 hover:text-rose-500 font-bold px-1"
                      >
                        +
                      </button>
                    </div>

                    <div className="text-right">
                      <p className="font-bold text-ink-900 text-sm sm:text-base">
                        GH₵{(parseFloat(item.price) * item.quantity).toFixed(2)}
                      </p>
                      <button
                        onClick={() => removeItem(item.product_id)}
                        className="text-xs text-red-500 hover:underline mt-0.5 block"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              ))}

              <div className="flex justify-between items-center pt-2">
                <button onClick={clearCart} className="text-xs text-ink-600 hover:text-red-500 underline">
                  Clear Entire Cart
                </button>
              </div>
            </div>

            {/* Order Summary */}
            <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 h-fit space-y-6 shadow-sm">
              <h3 className="font-bold text-xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Order Summary
              </h3>

              <div className="space-y-3 text-sm border-b border-cream-300 pb-4">
                <div className="flex justify-between text-ink-600">
                  <span>Subtotal</span>
                  <span className="font-semibold text-ink-900">GH₵{total.toFixed(2)}</span>
                </div>
                <div className="flex justify-between text-ink-600">
                  <span>Delivery</span>
                  <span className="text-emerald-600 font-medium">Free</span>
                </div>
              </div>

              <div className="flex justify-between items-center text-xl font-bold text-ink-900">
                <span>Total</span>
                <span className="text-rose-500">GH₵{total.toFixed(2)}</span>
              </div>

              <Link to="/checkout" className="home-btn-primary w-full py-3.5 text-center text-sm block justify-center">
                PROCEED TO CHECKOUT →
              </Link>
            </div>
          </div>
        )}
      </main>

      <Footer />
    </div>
  );
}
