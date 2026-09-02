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
    <div className="min-h-screen bg-cream-50 flex text-ink-900 font-sans selection:bg-rose-200 selection:text-ink-900">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-4">
          <div>
            <h1 className="font-bold text-3xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Sales Analytics & Reports
            </h1>
            <p className="text-sm text-ink-600 mt-1">Detailed financial transactions, payment breakdown, and total revenue</p>
          </div>

          <div className="flex gap-2">
            {['', 'Cash', 'Card', 'Mobile Money'].map((method) => (
              <button
                key={method}
                onClick={() => setMethodFilter(method)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors ${
                  methodFilter === method
                    ? 'bg-rose-100 text-rose-700 border border-rose-300'
                    : 'bg-white text-ink-600 border border-cream-200 hover:bg-cream-100'
                }`}
              >
                {method || 'All Payments'}
              </button>
            ))}
          </div>
        </div>

        {/* Financial Summary Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div className="bg-white border border-cream-200 rounded-2xl p-5 shadow-sm">
            <span className="text-xs text-ink-500 font-bold uppercase tracking-wider">Total Sales Revenue</span>
            <p className="font-bold text-2xl text-emerald-600 mt-2">GH₵{totalRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-ink-400 font-medium">{sales.length} transactions</span>
          </div>

          <div className="bg-white border border-cream-200 rounded-2xl p-5 shadow-sm">
            <span className="text-xs text-ink-500 font-bold uppercase tracking-wider font-mono">Cash Payments</span>
            <p className="font-bold text-2xl text-amber-600 mt-2">GH₵{cashRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-ink-400 font-medium">{sales.filter(s => s.payment_method === 'Cash').length} transactions</span>
          </div>

          <div className="bg-white border border-cream-200 rounded-2xl p-5 shadow-sm">
            <span className="text-xs text-ink-500 font-bold uppercase tracking-wider font-mono">Card Payments</span>
            <p className="font-bold text-2xl text-blue-600 mt-2">GH₵{cardRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-ink-400 font-medium">{sales.filter(s => s.payment_method === 'Card').length} transactions</span>
          </div>

          <div className="bg-white border border-cream-200 rounded-2xl p-5 shadow-sm">
            <span className="text-xs text-ink-500 font-bold uppercase tracking-wider font-mono">Mobile Money</span>
            <p className="font-bold text-2xl text-purple-600 mt-2">GH₵{momoRevenue.toFixed(2)}</p>
            <span className="text-[11px] text-ink-400 font-medium">{sales.filter(s => s.payment_method === 'Mobile Money').length} transactions</span>
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
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Sales ID</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Order ID</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Order Type</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Payment Method</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Transaction Ref</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Total Amount</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Date & Time</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-cream-100">
                  {filteredSales.map((s) => (
                    <tr key={s.sales_id} className="hover:bg-cream-50/50 transition-colors">
                      <td className="px-6 py-4 font-semibold text-ink-900">#{s.sales_id}</td>
                      <td className="px-6 py-4"><span className="font-semibold text-rose-600">Order #{s.order_id}</span></td>
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cream-100 text-ink-700 border border-cream-200">
                          {s.order_type}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${
                          s.payment_method === 'Cash' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                          s.payment_method === 'Card' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-purple-50 text-purple-700 border-purple-200'
                        }`}>
                          {s.payment_method}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-xs font-mono text-ink-500">{s.transaction_id || 'N/A'}</td>
                      <td className="px-6 py-4 font-bold text-emerald-600">GH₵{parseFloat(s.total_amount).toFixed(2)}</td>
                      <td className="px-6 py-4 text-xs text-ink-500">{new Date(s.created_at).toLocaleString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}

