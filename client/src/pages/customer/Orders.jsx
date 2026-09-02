import React, { useEffect, useState } from 'react';
import Navbar from '../../components/Navbar';
import Footer from '../../components/Footer';
import api from '../../api/axios';

export default function Orders() {
  const [orders, setOrders] = useState([]);
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchOrders() {
      try {
        const res = await api.get('/orders');
        setOrders(res.data);
      } catch (err) {
        console.error('Failed to load orders', err);
      } finally {
        setLoading(false);
      }
    }
    fetchOrders();
  }, []);

  const handleViewOrder = async (orderId) => {
    try {
      const res = await api.get(`/orders/${orderId}`);
      setSelectedOrder(res.data);
    } catch (err) {
      console.error('Failed to load order detail', err);
    }
  };

  return (
    <div className="home-page min-h-screen flex flex-col selection:bg-rose-100 selection:text-rose-600">
      <Navbar />

      <main className="py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto flex-1 w-full">
        <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">
          Purchase History
        </span>
        <h1 className="font-bold text-3xl sm:text-4xl text-ink-900 mt-1 mb-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
          My Orders
        </h1>
        <p className="text-sm text-ink-600 mb-8">Track your order statuses, delivery progress, and invoice details</p>

        {loading ? (
          <div className="bg-white border border-cream-300 rounded-2xl h-64 animate-pulse" />
        ) : orders.length === 0 ? (
          <div className="bg-white border border-cream-300 rounded-2xl p-12 text-center shadow-sm">
            <span className="text-4xl">📦</span>
            <h3 className="font-bold text-xl text-ink-900 mt-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>No Orders Placed Yet</h3>
            <p className="text-ink-600 text-sm mt-1">Your placed order history will appear here.</p>
          </div>
        ) : (
          <div className="space-y-4">
            {orders.map((order) => (
              <div key={order.order_id} className="bg-white border border-cream-300 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm hover:border-rose-300 transition-colors">
                <div>
                  <div className="flex items-center gap-3 flex-wrap">
                    <span className="font-bold text-ink-900 text-lg" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                      Order #{order.order_id}
                    </span>
                    <span className={`px-3 py-0.5 rounded-full text-xs font-semibold ${
                      order.order_status === 'Completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                      order.order_status === 'Processing' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                      order.order_status === 'Cancelled' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200'
                    }`}>
                      {order.order_status}
                    </span>
                    <span className={`px-3 py-0.5 rounded-full text-xs font-semibold ${
                      order.payment_status === 'Paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'
                    }`}>
                      {order.payment_status}
                    </span>
                  </div>
                  <p className="text-xs text-ink-600 mt-1">
                    Placed on {new Date(order.created_at).toLocaleDateString()} — {order.order_type}
                  </p>
                </div>

                <div className="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                  <span className="font-bold text-xl text-rose-500">
                    GH₵{parseFloat(order.total_amount).toFixed(2)}
                  </span>
                  <button
                    onClick={() => handleViewOrder(order.order_id)}
                    className="home-btn-secondary py-2 px-4 text-xs"
                  >
                    View Invoice
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Invoice Modal */}
        {selectedOrder && (
          <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={() => setSelectedOrder(null)}>
            <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 max-w-lg w-full shadow-2xl animate-slide-up" onClick={(e) => e.stopPropagation()}>
              <div className="flex items-center justify-between border-b border-cream-300 pb-4 mb-4">
                <div>
                  <h3 className="font-bold text-xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                    Invoice Order #{selectedOrder.order_id}
                  </h3>
                  <p className="text-xs text-ink-600">{new Date(selectedOrder.created_at).toLocaleString()}</p>
                </div>
                <button onClick={() => setSelectedOrder(null)} className="text-ink-600 hover:text-ink-900 font-bold p-1">
                  ✕
                </button>
              </div>

              <div className="space-y-3 mb-6 max-h-60 overflow-y-auto">
                {selectedOrder.items?.map((item) => (
                  <div key={item.item_id} className="flex justify-between items-center text-sm border-b border-cream-200 pb-2">
                    <div>
                      <p className="text-ink-900 font-semibold">{item.name}</p>
                      <p className="text-xs text-ink-600">
                        {item.quantity} x GH₵{parseFloat(item.unit_price).toFixed(2)}
                      </p>
                    </div>
                    <span className="font-bold text-rose-500">
                      GH₵{parseFloat(item.total_price).toFixed(2)}
                    </span>
                  </div>
                ))}
              </div>

              <div className="pt-4 border-t border-cream-300 flex justify-between items-center text-xl font-bold text-ink-900">
                <span>Total Amount:</span>
                <span className="text-rose-500">GH₵{parseFloat(selectedOrder.total_amount).toFixed(2)}</span>
              </div>
            </div>
          </div>
        )}
      </main>

      <Footer />
    </div>
  );
}
