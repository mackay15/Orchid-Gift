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
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-4">
          <div>
            <h1 className="font-display font-bold text-3xl text-white">Orders Management</h1>
            <p className="text-sm text-gray-400">Process online & POS orders and manage fulfillment stages</p>
          </div>

          <div className="flex gap-2">
            {['', 'Pending', 'Processing', 'Completed', 'Cancelled'].map((st) => (
              <button
                key={st}
                onClick={() => setFilterStatus(st)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap ${
                  filterStatus === st
                    ? 'bg-orchid-800/40 text-orchid-200 border border-orchid-700/30'
                    : 'bg-white/5 text-gray-400'
                }`}
              >
                {st || 'All Orders'}
              </button>
            ))}
          </div>
        </div>

        {loading ? (
          <div className="glass-card h-96 animate-pulse" />
        ) : (
          <div className="glass-card overflow-hidden">
            <table className="orchid-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Fulfillment Stage</th>
                  <th>Payment Status</th>
                  <th className="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                {filteredOrders.map((o) => (
                  <tr key={o.order_id}>
                    <td className="font-bold text-white">#{o.order_id}</td>
                    <td className="text-xs text-gray-300">{o.customer_name || 'Walk-in Guest'}</td>
                    <td><span className="badge-orchid text-xs">{o.order_type}</span></td>
                    <td className="font-bold text-orchid-300">GH₵{parseFloat(o.total_amount).toFixed(2)}</td>
                    <td>
                      <select
                        value={o.order_status}
                        onChange={(e) => handleUpdateStatus(o.order_id, e.target.value, undefined)}
                        className="bg-gray-900 border border-white/15 text-xs text-white rounded-lg p-1.5 focus:outline-none"
                      >
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                      </select>
                    </td>
                    <td>
                      <select
                        value={o.payment_status}
                        onChange={(e) => handleUpdateStatus(o.order_id, undefined, e.target.value)}
                        className="bg-gray-900 border border-white/15 text-xs text-white rounded-lg p-1.5 focus:outline-none"
                      >
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid</option>
                      </select>
                    </td>
                    <td className="text-right">
                      <button
                        onClick={() => handleViewOrderDetail(o.order_id)}
                        className="btn-ghost py-1 px-3 text-xs"
                      >
                        View Items
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Order Details Modal */}
        {selectedOrder && (
          <div className="modal-overlay" onClick={() => setSelectedOrder(null)}>
            <div className="modal-box max-w-lg" onClick={(e) => e.stopPropagation()}>
              <div className="flex justify-between items-center pb-4 border-b border-white/10 mb-4">
                <div>
                  <h3 className="font-display font-bold text-lg text-white">
                    Order #{selectedOrder.order_id} Items
                  </h3>
                  <p className="text-xs text-gray-400">
                    Customer: {selectedOrder.customer_name || 'Walk-in Guest'} • {selectedOrder.order_type}
                  </p>
                </div>
                <button onClick={() => setSelectedOrder(null)} className="text-gray-400 hover:text-white font-bold p-1">
                  ✕
                </button>
              </div>

              <div className="space-y-3 max-h-60 overflow-y-auto pr-1 mb-4">
                {selectedOrder.items?.map((item) => (
                  <div key={item.item_id} className="p-3 rounded-xl bg-white/5 border border-white/10 flex justify-between items-center text-xs">
                    <div>
                      <p className="text-white font-semibold">{item.name}</p>
                      <p className="text-gray-400">Qty: {item.quantity} x GH₵{parseFloat(item.unit_price).toFixed(2)}</p>
                    </div>
                    <span className="font-bold text-orchid-300">
                      GH₵{parseFloat(item.total_price).toFixed(2)}
                    </span>
                  </div>
                ))}
              </div>

              <div className="pt-3 border-t border-white/10 flex justify-between items-center">
                <span className="text-xs text-gray-400">Total Order Amount</span>
                <span className="font-bold text-lg text-emerald-400">
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

