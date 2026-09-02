import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Navbar from '../../components/Navbar';
import Footer from '../../components/Footer';
import { useCart } from '../../context/CartContext';
import { useAuth } from '../../context/AuthContext';
import api from '../../api/axios';

export default function Checkout() {
  const { items, total, clearCart } = useCart();
  const { user } = useAuth();
  const navigate = useNavigate();

  const [paymentMethod, setPaymentMethod] = useState('Card');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handlePlaceOrder = async (e) => {
    e.preventDefault();
    if (!items.length) return;
    setLoading(true);
    setError('');

    try {
      // 1. Create order on backend
      const orderRes = await api.post('/orders', {
        items: items.map((i) => ({ product_id: i.product_id, quantity: i.quantity })),
        payment_method: paymentMethod,
        order_type: 'online',
      });

      const order = orderRes.data;

      // 2. If Mobile Money / Card, simulate Paystack popup flow or call verification
      if (paymentMethod === 'Cash') {
        clearCart();
        navigate('/orders');
      } else {
        // Mock Paystack transaction reference for sandbox / demo execution
        const ref = `PAYSTACK_${Date.now()}`;
        await api.post('/payments/verify', {
          reference: ref,
          order_id: order.order_id,
        });
        clearCart();
        navigate('/orders');
      }
    } catch (err) {
      setError(err.response?.data?.error || err.message || 'Order placement failed.');
    } finally {
      setLoading(false);
    }
  };

  if (!items.length) {
    return (
      <div className="home-page min-h-screen flex flex-col">
        <Navbar />
        <div className="py-24 text-center text-ink-600 flex-1">Your cart is empty.</div>
        <Footer />
      </div>
    );
  }

  return (
    <div className="home-page min-h-screen flex flex-col selection:bg-rose-100 selection:text-rose-600">
      <Navbar />

      <main className="py-12 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto flex-1 w-full">
        <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">
          Secure Checkout
        </span>
        <h1 className="font-bold text-3xl sm:text-4xl text-ink-900 mt-1 mb-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
          Complete Your Order
        </h1>
        <p className="text-sm text-ink-600 mb-8">Confirm your delivery details and choose a payment method</p>

        {error && (
          <div className="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
            {error}
          </div>
        )}

        <form onSubmit={handlePlaceOrder} className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {/* Billing Info */}
          <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 space-y-4 shadow-sm">
            <h3 className="font-bold text-xl text-ink-900 mb-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Customer Details
            </h3>
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Full Name</label>
              <input type="text" readOnly value={user?.full_name || ''} className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm opacity-80" />
            </div>
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Email</label>
              <input type="email" readOnly value={user?.email || ''} className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm opacity-80" />
            </div>

            <div className="pt-4 border-t border-cream-300">
              <h4 className="font-bold text-base text-ink-900 mb-3" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Payment Method
              </h4>
              <div className="space-y-2">
                {[
                  { id: 'Card', label: 'Credit / Debit Card (Paystack)' },
                  { id: 'Mobile Money', label: 'Mobile Money (MTN, Telecel, AT)' },
                  { id: 'Cash', label: 'Pay on Delivery (Cash)' },
                ].map((m) => (
                  <label
                    key={m.id}
                    className={`flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition-all ${
                      paymentMethod === m.id
                        ? 'bg-rose-50 border-rose-300 text-rose-700 font-semibold'
                        : 'bg-white border-cream-300 text-ink-600 hover:bg-cream-100'
                    }`}
                  >
                    <input
                      type="radio"
                      name="payment_method"
                      value={m.id}
                      checked={paymentMethod === m.id}
                      onChange={(e) => setPaymentMethod(e.target.value)}
                      className="accent-rose-500"
                    />
                    <span className="text-sm font-medium">{m.label}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>

          {/* Items Summary & Confirm */}
          <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 flex flex-col justify-between space-y-6 shadow-sm">
            <div>
              <h3 className="font-bold text-xl text-ink-900 mb-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Order Items ({items.length})
              </h3>
              <div className="space-y-3 max-h-60 overflow-y-auto pr-2">
                {items.map((item) => (
                  <div key={item.product_id} className="flex justify-between items-center text-sm border-b border-cream-200 pb-2">
                    <div>
                      <p className="text-ink-900 font-semibold">{item.name}</p>
                      <p className="text-xs text-ink-600">Qty: {item.quantity}</p>
                    </div>
                    <span className="font-bold text-rose-500">
                      GH₵{(parseFloat(item.price) * item.quantity).toFixed(2)}
                    </span>
                  </div>
                ))}
              </div>
            </div>

            <div className="pt-4 border-t border-cream-300 space-y-4">
              <div className="flex justify-between items-center text-xl font-bold text-ink-900">
                <span>Total Amount:</span>
                <span className="text-rose-500">GH₵{total.toFixed(2)}</span>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="home-btn-primary w-full py-3.5 text-sm justify-center font-semibold"
              >
                {loading ? 'PROCESSING ORDER...' : `PAY & COMPLETE ORDER (GH₵${total.toFixed(2)}) →`}
              </button>
            </div>
          </div>
        </form>
      </main>

      <Footer />
    </div>
  );
}
