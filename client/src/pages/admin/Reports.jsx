import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function AdminReports() {
  const [sales, setSales] = useState([]);
  const [loading, setLoading] = useState(true);
  const [methodFilter, setMethodFilter] = useState('');

  useEffect(() => {
    async function loadSales() {
      try {
        const res = await api.get('/sales');
        setSales(res.data || []);
      } catch (err) {
        console.error('Failed to load sales log', err);
      } finally {
        setLoading(false);
      }
    }
    loadSales();
  }, []);

  const totalRevenue = sales.reduce((sum, s) => sum + parseFloat(s.total_amount), 0);
  const cashRevenue = sales.filter(s => s.payment_method === 'Cash').reduce((sum, s) => sum + parseFloat(s.total_amount), 0);
  const cardRevenue = sales.filter(s => s.payment_method === 'Card').reduce((sum, s) => sum + parseFloat(s.total_amount), 0);
  const momoRevenue = sales.filter(s => s.payment_method === 'Mobile Money').reduce((sum, s) => sum + parseFloat(s.total_amount), 0);

  const filteredSales = sales.filter(s => !methodFilter || s.payment_method === methodFilter);

  return (
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-4">
          <div>
            <h1 className="font-display font-bold text-3xl text-white">Sales Analytics & Reports</h1>
            <p className="text-sm text-gray-400">Detailed financial transactions, payment breakdown, and total revenue</p>
          </div>

          <div className="flex gap-2">
            {['', 'Cash', 'Card', 'Mobile Money'].map((method) => (
              <button
                key={method}
                onClick={() => setMethodFilter(method)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap ${
                  methodFilter === method
                    ? 'bg-orchid-800/40 text-orchid-200 border border-orchid-700/30'
                    : 'bg-white/5 text-gray-400'
                }`}
              >
                {method || 'All Payments'}
              </button>
            ))}
          </div>
        </div>

        {/* Financial Summary Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div className="glass-card p-5">
            <span className="text-xs text-gray-400 font-semibold uppercase tracking-wider">Total Sales Revenue</span>
            <p className="font-display font-bold text-2xl text-emerald-400 mt-2">GH₵{totalRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-gray-500">{sales.length} transactions</span>
          </div>

          <div className="glass-card p-5">
            <span className="text-xs text-gray-400 font-semibold uppercase tracking-wider font-mono">Cash Payments</span>
            <p className="font-display font-bold text-2xl text-amber-400 mt-2">GH₵{cashRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-gray-500">{sales.filter(s => s.payment_method === 'Cash').length} transactions</span>
          </div>

          <div className="glass-card p-5">
            <span className="text-xs text-gray-400 font-semibold uppercase tracking-wider font-mono">Card Payments</span>
            <p className="font-display font-bold text-2xl text-blue-400 mt-2">GH₵{cardRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-gray-500">{sales.filter(s => s.payment_method === 'Card').length} transactions</span>
          </div>

          <div className="glass-card p-5">
            <span className="text-xs text-gray-400 font-semibold uppercase tracking-wider font-mono">Mobile Money</span>
            <p className="font-display font-bold text-2xl text-purple-400 mt-2">GH₵{momoRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-gray-500">{sales.filter(s => s.payment_method === 'Mobile Money').length} transactions</span>
          </div>
        </div>

        {loading ? (
          <div className="glass-card h-96 animate-pulse" />
        ) : (
          <div className="glass-card overflow-hidden">
            <table className="orchid-table">
              <thead>
                <tr>
                  <th>Sales ID</th>
                  <th>Order ID</th>
                  <th>Order Type</th>
                  <th>Payment Method</th>
                  <th>Transaction Ref</th>
                  <th>Total Amount</th>
                  <th>Date & Time</th>
                </tr>
              </thead>
              <tbody>
                {filteredSales.map((s) => (
                  <tr key={s.sales_id}>
                    <td className="font-bold text-white">#{s.sales_id}</td>
                    <td><span className="font-semibold text-orchid-300">Order #{s.order_id}</span></td>
                    <td><span className="badge-orchid text-xs">{s.order_type}</span></td>
                    <td>
                      <span className={`badge text-xs ${
                        s.payment_method === 'Cash' ? 'badge-warning' :
                        s.payment_method === 'Card' ? 'badge-orchid' : 'badge-success'
                      }`}>
                        {s.payment_method}
                      </span>
                    </td>
                    <td className="text-xs font-mono text-gray-400">{s.transaction_id || 'N/A'}</td>
                    <td className="font-bold text-emerald-400">GH₵{parseFloat(s.total_amount).toFixed(2)}</td>
                    <td className="text-xs text-gray-400">{new Date(s.created_at).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </main>
    </div>
  );
}

