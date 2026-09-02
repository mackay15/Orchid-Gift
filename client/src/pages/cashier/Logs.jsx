import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function CashierLogs() {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadLogs() {
      try {
        const res = await api.get('/cashier/logs');
        setLogs(res.data || []);
      } catch (err) {
        console.error('Failed to load cashier logs', err);
      } finally {
        setLoading(false);
      }
    }
    loadLogs();
  }, []);

  return (
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="font-display font-bold text-3xl text-white">Cashier Activity Log</h1>
            <p className="text-sm text-gray-400">Audit trail of walk-in sales and terminal operations</p>
          </div>
        </div>

        {loading ? (
          <div className="glass-card h-96 animate-pulse" />
        ) : (
          <div className="glass-card overflow-hidden">
            <table className="orchid-table">
              <thead>
                <tr>
                  <th>Log ID</th>
                  <th>Cashier</th>
                  <th>Action</th>
                  <th>Details</th>
                  <th>Timestamp</th>
                </tr>
              </thead>
              <tbody>
                {logs.map((l) => (
                  <tr key={l.log_id}>
                    <td className="font-bold text-white">#{l.log_id}</td>
                    <td className="text-xs text-orchid-300 font-semibold">{l.cashier_name}</td>
                    <td><span className="badge-orchid text-xs font-mono">{l.action}</span></td>
                    <td className="text-xs text-gray-300">{l.details}</td>
                    <td className="text-xs text-gray-400">{new Date(l.created_at).toLocaleString()}</td>
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
