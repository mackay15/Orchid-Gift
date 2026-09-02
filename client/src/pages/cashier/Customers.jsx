import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function CashierCustomers() {
  const [customers, setCustomers] = useState([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);

  // Registration modal states
  const [modalOpen, setModalOpen] = useState(false);
  const [formData, setFormData] = useState({ full_name: '', username: '', email: '', password: 'customer123' });
  const [submitting, setSubmitting] = useState(false);

  const loadCustomers = async () => {
    try {
      const res = await api.get('/cashier/customers', { params: { search: search || undefined } });
      setCustomers(res.data || []);
    } catch (err) {
      console.error('Failed to load customers', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCustomers();
  }, [search]);

  const handleRegisterCustomer = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      await api.post('/auth/register', {
        ...formData,
        role: 'customer',
      });
      alert('Walk-in customer registered successfully!');
      setModalOpen(false);
      setFormData({ full_name: '', username: '', email: '', password: 'customer123' });
      loadCustomers();
    } catch (err) {
      alert(err.response?.data?.error || 'Registration failed.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8 flex-wrap gap-4">
          <div>
            <h1 className="font-display font-bold text-3xl text-white">Walk-in Customer Directory</h1>
            <p className="text-sm text-gray-400">Search & capture customer details for walk-in transactions</p>
          </div>

          <div className="flex items-center gap-3">
            <input
              type="text"
              placeholder="Search by name, email..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="input-orchid py-2 px-4 text-xs w-64"
            />
            <button onClick={() => setModalOpen(true)} className="btn-orchid py-2 px-4 text-xs font-semibold whitespace-nowrap">
              + Register Customer
            </button>
          </div>
        </div>

        {loading ? (
          <div className="glass-card h-96 animate-pulse" />
        ) : (
          <div className="glass-card overflow-hidden">
            <table className="orchid-table">
              <thead>
                <tr>
                  <th>Customer Name</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {customers.map((c) => (
                  <tr key={c.user_id}>
                    <td className="font-semibold text-white">{c.full_name}</td>
                    <td className="text-xs text-orchid-300">@{c.username}</td>
                    <td className="text-xs text-gray-300">{c.email}</td>
                    <td>
                      <span className="badge-success text-xs">Active</span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Modal */}
        {modalOpen && (
          <div className="modal-overlay" onClick={() => setModalOpen(false)}>
            <div className="modal-box max-w-md" onClick={(e) => e.stopPropagation()}>
              <h3 className="font-display font-bold text-lg text-white mb-4">
                Register New Walk-in Customer
              </h3>
              <form onSubmit={handleRegisterCustomer} className="space-y-4">
                <div>
                  <label className="input-label">Full Name</label>
                  <input
                    type="text"
                    required
                    value={formData.full_name}
                    onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                    className="input-orchid py-2 text-sm"
                  />
                </div>
                <div>
                  <label className="input-label">Username</label>
                  <input
                    type="text"
                    required
                    value={formData.username}
                    onChange={(e) => setFormData({ ...formData, username: e.target.value.toLowerCase().replace(/\s+/g, '') })}
                    className="input-orchid py-2 text-sm"
                  />
                </div>
                <div>
                  <label className="input-label">Email Address</label>
                  <input
                    type="email"
                    required
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="input-orchid py-2 text-sm"
                  />
                </div>
                <div className="flex justify-end gap-2 pt-2">
                  <button type="button" onClick={() => setModalOpen(false)} className="btn-ghost py-2 px-4 text-xs">
                    Cancel
                  </button>
                  <button type="submit" disabled={submitting} className="btn-orchid py-2 px-4 text-xs">
                    {submitting ? 'Registering...' : 'Register Customer'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}

