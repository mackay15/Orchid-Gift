import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip } from 'recharts';

export default function AdminDashboard() {
  const [summary, setSummary] = useState(null);
  const [chartData, setChartData] = useState([]);
  const [lowStock, setLowStock] = useState([]);
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadDashboard() {
      try {
        const [sumRes, chartRes, lowRes, notifRes] = await Promise.all([
          api.get('/sales/summary'),
          api.get('/sales/chart'),
          api.get('/products/low-stock'),
          api.get('/notifications'),
        ]);
        setSummary(sumRes.data);
        setChartData(chartRes.data || []);
        setLowStock(lowRes.data || []);
        setNotifications(notifRes.data || []);
      } catch (err) {
        console.error('Failed to load admin summary', err);
      } finally {
        setLoading(false);
      }
    }
    loadDashboard();
  }, []);

  return (
    <div className="home-page min-h-screen flex selection:bg-rose-100 selection:text-rose-600">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">
              Management Portal
            </span>
            <h1 className="font-bold text-3xl text-ink-900 mt-1" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Admin Dashboard
            </h1>
            <p className="text-sm text-ink-600">System metrics, inventory alerts, and sales performance</p>
          </div>
          <span className="bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1 text-xs font-semibold rounded-full">
            Live System Sync
          </span>
        </div>

        {loading ? (
          <div className="bg-white border border-cream-300 rounded-2xl h-96 animate-pulse" />
        ) : (
          <div className="space-y-8">
            {/* Stat Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
              <div className="bg-white border border-cream-300 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <span className="text-xs font-semibold text-ink-600 uppercase tracking-wider">Gross Sales</span>
                <span className="font-bold text-2xl text-emerald-600 mt-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  GH₵{parseFloat(summary?.gross_sales || 0).toFixed(2)}
                </span>
              </div>
              <div className="bg-white border border-cream-300 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <span className="text-xs font-semibold text-ink-600 uppercase tracking-wider">Total Orders</span>
                <span className="font-bold text-2xl text-ink-900 mt-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  {summary?.total_orders || 0}
                </span>
              </div>
              <div className="bg-white border border-cream-300 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <span className="text-xs font-semibold text-ink-600 uppercase tracking-wider">Low Stock Items</span>
                <span className="font-bold text-2xl text-amber-600 mt-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  {summary?.low_stock || 0}
                </span>
              </div>
              <div className="bg-white border border-cream-300 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <span className="text-xs font-semibold text-ink-600 uppercase tracking-wider">Pending Reviews</span>
                <span className="font-bold text-2xl text-rose-500 mt-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  {summary?.pending_reviews || 0}
                </span>
              </div>
              <div className="bg-white border border-cream-300 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <span className="text-xs font-semibold text-ink-600 uppercase tracking-wider">Active Customers</span>
                <span className="font-bold text-2xl text-blue-600 mt-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  {summary?.total_customers || 0}
                </span>
              </div>
            </div>

            {/* Sales Chart & Low Stock */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Revenue Chart */}
              <div className="lg:col-span-2 bg-white border border-cream-300 rounded-2xl p-6 shadow-sm">
                <h3 className="font-bold text-xl text-ink-900 mb-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  Revenue Trend (Last 7 Days)
                </h3>
                <div className="h-64">
                  {chartData.length > 0 ? (
                    <ResponsiveContainer width="100%" height="100%">
                      <AreaChart data={chartData}>
                        <defs>
                          <linearGradient id="roseGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="5%" stopColor="#B85C73" stopOpacity={0.8} />
                            <stop offset="95%" stopColor="#B85C73" stopOpacity={0} />
                          </linearGradient>
                        </defs>
                        <XAxis dataKey="label" stroke="#6B6B6B" fontSize={12} />
                        <YAxis stroke="#6B6B6B" fontSize={12} />
                        <Tooltip contentStyle={{ backgroundColor: '#ffffff', borderColor: '#E8E2DE', borderRadius: '12px', boxShadow: '0 8px 24px rgba(0,0,0,0.08)' }} />
                        <Area type="monotone" dataKey="total" stroke="#B85C73" fillOpacity={1} fill="url(#roseGrad)" />
                      </AreaChart>
                    </ResponsiveContainer>
                  ) : (
                    <div className="h-full flex items-center justify-center text-ink-600 text-sm">
                      No sales data recorded in the last 7 days.
                    </div>
                  )}
                </div>
              </div>

              {/* Low Stock Alerts */}
              <div className="bg-white border border-cream-300 rounded-2xl p-6 shadow-sm">
                <h3 className="font-bold text-xl text-ink-900 mb-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  Low Stock Alerts
                </h3>
                {lowStock.length === 0 ? (
                  <p className="text-ink-600 text-xs">All inventory items are sufficiently stocked.</p>
                ) : (
                  <div className="space-y-3">
                    {lowStock.map((item) => (
                      <div key={item.product_id} className="p-3.5 rounded-xl bg-cream-100 border border-cream-300 flex justify-between items-center text-sm">
                        <div>
                          <p className="text-ink-900 font-semibold">{item.name}</p>
                          <p className="text-xs text-ink-600">{item.category_name}</p>
                        </div>
                        <span className="bg-red-50 text-red-700 border border-red-200 text-xs font-bold px-2.5 py-0.5 rounded-full">{item.stock_quantity} left</span>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Notifications Log */}
            <div className="bg-white border border-cream-300 rounded-2xl p-6 shadow-sm">
              <h3 className="font-bold text-xl text-ink-900 mb-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Recent Notifications
              </h3>
              {notifications.length === 0 ? (
                <p className="text-ink-600 text-xs">No pending notifications.</p>
              ) : (
                <div className="space-y-2">
                  {notifications.map((n) => (
                    <div key={n.notification_id} className="p-3 rounded-xl bg-cream-100 border border-cream-200 flex justify-between items-center text-xs text-ink-900">
                      <span>{n.message}</span>
                      <span className="text-ink-600">{new Date(n.created_at).toLocaleDateString()}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
