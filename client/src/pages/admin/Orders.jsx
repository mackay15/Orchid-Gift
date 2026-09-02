import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function AdminOrders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filterStatus, setFilterStatus] = useState('');
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [detailLoading, setDetailLoading] = useState(false);

  const loadOrders = async () => {
    try {
      const res = await api.get('/orders');
      setOrders(res.data || []);
    } catch (err) {
      console.error('Failed to load orders', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadOrders();
  }, []);

  const handleUpdateStatus = async (id, order_status, payment_status) => {
    try {
      await api.patch(`/orders/${id}/status`, { order_status, payment_status });
      loadOrders();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to update order status.');
    }
  };

  const handleViewOrderDetail = async (id) => {
    setDetailLoading(true);
    try {
      const res = await api.get(`/orders/${id}`);
      setSelectedOrder(res.data);
    } catch (err) {
      alert('Failed to load order details');
    } finally {
      setDetailLoading(false);
    }
  };

  const filteredOrders = orders.filter((o) => !filterStatus || o.order_status === filterStatus);

  return (
    <div className="min-h-screen bg-cream-50 flex text-ink-900 font-sans selection:bg-rose-200 selection:text-ink-900">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-4">
          <div>
            <h1 className="font-bold text-3xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Orders Management
            </h1>
            <p className="text-sm text-ink-600 mt-1">Process online & POS orders and manage fulfillment stages</p>
          </div>

          <div className="flex gap-2">
            {['', 'Pending', 'Processing', 'Completed', 'Cancelled'].map((st) => (
              <button
                key={st}
                onClick={() => setFilterStatus(st)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors ${
                  filterStatus === st
                    ? 'bg-rose-100 text-rose-700 border border-rose-300'
                    : 'bg-white text-ink-600 border border-cream-200 hover:bg-cream-100'
                }`}
              >
                {st || 'All Orders'}
              </button>
            ))}
          </div>
        </div>

        {loading ? (
          <div className="bg-white border border-cream-200 rounded-2xl p-6 h-96 animate-pulse shadow-sm" />
        ) : (
          <div className="bg-white border border-cream-200 rounded-2xl shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead className="bg-cream-50/50 border-b border-cream-200">
                  <tr>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Order #</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Customer</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Type</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Amount</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Fulfillment Stage</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Payment Status</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-cream-100">
                  {filteredOrders.map((o) => (
                    <tr key={o.order_id} className="hover:bg-cream-50/50 transition-colors">
                      <td className="px-6 py-4 font-semibold text-ink-900">#{o.order_id}</td>
                      <td className="px-6 py-4 text-sm text-ink-600">{o.customer_name || 'Walk-in Guest'}</td>
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cream-100 text-ink-700 border border-cream-200">
                          {o.order_type}
                        </span>
                      </td>
                      <td className="px-6 py-4 font-bold text-ink-900">GH₵{parseFloat(o.total_amount).toFixed(2)}</td>
                      <td className="px-6 py-4">
                        <select
                          value={o.order_status}
                          onChange={(e) => handleUpdateStatus(o.order_id, e.target.value, undefined)}
                          className="bg-cream-100 border border-cream-300 text-ink-900 text-xs font-semibold rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-rose-300"
                        >
                          <option value="Pending">Pending</option>
                          <option value="Processing">Processing</option>
                          <option value="Completed">Completed</option>
                          <option value="Cancelled">Cancelled</option>
                        </select>
                      </td>
                      <td className="px-6 py-4">
                        <select
                          value={o.payment_status}
                          onChange={(e) => handleUpdateStatus(o.order_id, undefined, e.target.value)}
                          className="bg-cream-100 border border-cream-300 text-ink-900 text-xs font-semibold rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-rose-300"
                        >
                          <option value="Unpaid">Unpaid</option>
                          <option value="Paid">Paid</option>
                        </select>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleViewOrderDetail(o.order_id)}
                          className="px-3 py-1.5 text-xs font-semibold rounded-lg bg-cream-100 text-ink-700 hover:bg-cream-200 transition-colors"
                        >
                          View Items
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Order Details Modal */}
        {selectedOrder && (
          <div className="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 max-w-lg w-full shadow-2xl animate-slide-up" onClick={(e) => e.stopPropagation()}>
              <div className="flex justify-between items-center pb-4 border-b border-cream-200 mb-4">
                <div>
                  <h3 className="font-bold text-xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                    Order #{selectedOrder.order_id} Items
                  </h3>
                  <p className="text-sm text-ink-600 mt-1">
                    Customer: {selectedOrder.customer_name || 'Walk-in Guest'} • {selectedOrder.order_type}
                  </p>
                </div>
                <button 
                  onClick={() => setSelectedOrder(null)} 
                  className="p-1 rounded-lg text-ink-400 hover:text-ink-700 hover:bg-cream-100 transition-colors"
                >
                  ✕
                </button>
              </div>

              <div className="space-y-3 max-h-60 overflow-y-auto pr-1 mb-4">
                {selectedOrder.items?.map((item) => (
                  <div key={item.item_id} className="p-4 rounded-xl bg-cream-50 border border-cream-200 flex justify-between items-center text-sm">
                    <div>
                      <p className="text-ink-900 font-semibold">{item.name}</p>
                      <p className="text-ink-600 mt-0.5">Qty: {item.quantity} x GH₵{parseFloat(item.unit_price).toFixed(2)}</p>
                    </div>
                    <span className="font-bold text-ink-900">
                      GH₵{parseFloat(item.total_price).toFixed(2)}
                    </span>
                  </div>
                ))}
              </div>

              <div className="pt-4 border-t border-cream-200 flex justify-between items-center">
                <span className="text-sm font-semibold uppercase tracking-wider text-ink-600">Total Order Amount</span>
                <span className="font-bold text-xl text-emerald-600">
                  GH₵{parseFloat(selectedOrder.total_amount).toFixed(2)}
                </span>
              </div>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}

