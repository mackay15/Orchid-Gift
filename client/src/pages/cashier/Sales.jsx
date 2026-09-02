import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function CashierSales() {
  const [sales, setSales] = useState([]);
  const [loading, setLoading] = useState(true);

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

  return (
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="font-display font-bold text-3xl text-white">Transactions & Receipts</h1>
            <p className="text-sm text-gray-400">View physical and online completed transaction receipts</p>
          </div>
        </div>

        {loading ? (
          <div className="glass-card h-96 animate-pulse" />
        ) : (
          <div className="glass-card overflow-hidden">
            <table className="orchid-table">
              <thead>
                <tr>
                  <th>Sale #</th>
                  <th>Order #</th>
                  <th>Order Type</th>
                  <th>Payment Method</th>
                  <th>Total Amount</th>
                  <th>Date</th>
                  <th className="text-right">Receipt</th>
                </tr>
              </thead>
              <tbody>
                {sales.map((s) => (
                  <tr key={s.sales_id}>
                    <td className="font-bold text-white">#{s.sales_id}</td>
                    <td className="text-orchid-300 font-semibold">Order #{s.order_id}</td>
                    <td><span className="badge-orchid text-xs">{s.order_type}</span></td>
                    <td className="text-xs text-gray-300">{s.payment_method}</td>
                    <td className="font-bold text-emerald-400">GH₵{parseFloat(s.total_amount).toFixed(2)}</td>
                    <td className="text-xs text-gray-400">{new Date(s.created_at).toLocaleString()}</td>
                    <td className="text-right">
                      <Link to={`/cashier/receipt/${s.order_id}`} className="btn-ghost py-1 px-3 text-xs">
                        Print Receipt
                      </Link>
                    </td>
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
