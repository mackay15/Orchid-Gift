import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../api/axios';

export default function CashierReceipt() {
  const { order_id } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadReceipt() {
      try {
        const res = await api.get(`/orders/${order_id}`);
        setOrder(res.data);
      } catch (err) {
        console.error('Failed to load receipt', err);
      } finally {
        setLoading(false);
      }
    }
    loadReceipt();
  }, [order_id]);

  if (loading) {
    return <div className="min-h-screen bg-gray-950 flex items-center justify-center text-white">Loading receipt...</div>;
  }

  if (!order) {
    return <div className="min-h-screen bg-gray-950 flex items-center justify-center text-red-400">Order not found.</div>;
  }

  return (
    <div className="min-h-screen bg-gray-950 py-12 px-4 flex flex-col items-center">
      <div className="no-print mb-6 flex gap-4">
        <Link to="/cashier" className="btn-ghost py-2 px-4 text-xs">
          ← Back to POS
        </Link>
        <button onClick={() => window.print()} className="btn-orchid py-2 px-6 text-xs">
          🖨️ Print Receipt
        </button>
      </div>

      {/* Printable Receipt Paper */}
      <div className="w-full max-w-sm bg-white text-gray-900 font-mono p-6 rounded-lg shadow-2xl space-y-4 print:shadow-none print:w-full print:max-w-none">
        <div className="text-center border-b border-dashed border-gray-400 pb-4">
          <h2 className="font-bold text-xl uppercase tracking-widest">ORCHID GIFT & MORE</h2>
          <p className="text-xs text-gray-600">Accra, Ghana • +233 50 000 0000</p>
          <p className="text-xs text-gray-500 mt-1">{new Date(order.created_at).toLocaleString()}</p>
        </div>

        <div className="text-xs space-y-1">
          <p><strong>Receipt #:</strong> REC-{order.order_id}</p>
          <p><strong>Order #:</strong> #{order.order_id}</p>
          <p><strong>Type:</strong> {order.order_type.toUpperCase()}</p>
          <p><strong>Customer:</strong> {order.customer_name || 'Walk-in Customer'}</p>
        </div>

        <table className="w-full text-xs text-left border-t border-b border-dashed border-gray-400 py-2 my-2">
          <thead>
            <tr className="border-b border-gray-300">
              <th className="py-1">QTY</th>
              <th className="py-1">ITEM</th>
              <th className="py-1 text-right">TOTAL</th>
            </tr>
          </thead>
          <tbody>
            {order.items?.map((item) => (
              <tr key={item.item_id}>
                <td className="py-1 font-bold">{item.quantity}x</td>
                <td className="py-1 truncate max-w-[140px]">{item.name}</td>
                <td className="py-1 text-right">GH₵{parseFloat(item.total_price).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>

        <div className="text-xs space-y-1 border-b border-dashed border-gray-400 pb-3">
          <div className="flex justify-between font-bold text-sm">
            <span>TOTAL:</span>
            <span>GH₵{parseFloat(order.total_amount).toFixed(2)}</span>
          </div>
          <div className="flex justify-between text-gray-600">
            <span>STATUS:</span>
            <span>{order.payment_status}</span>
          </div>
        </div>

        <div className="text-center text-[10px] text-gray-500 pt-2">
          <p>Thank you for shopping with Orchid Gift!</p>
          <p className="font-semibold text-gray-700">Please visit again soon ✨</p>
        </div>
      </div>
    </div>
  );
}
