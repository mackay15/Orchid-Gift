import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function AdminUsers() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [updatingId, setUpdatingId] = useState(null);
  const [search, setSearch] = useState('');

  // Modal State
  const [modalOpen, setModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    full_name: '',
    username: '',
    email: '',
    password: '',
    role: 'cashier',
    status: 'Active',
  });
  const [modalLoading, setModalLoading] = useState(false);
  const [modalError, setModalError] = useState('');

  const loadUsers = async () => {
    try {
      const res = await api.get('/users');
      setUsers(res.data || []);
    } catch (err) {
      console.error('Failed to load users', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadUsers();
  }, []);

  const handleToggleStatus = async (user_id, currentStatus) => {
    const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
    try {
      await api.patch(`/users/${user_id}/status`, { status: newStatus });
      loadUsers();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to change user status.');
    }
  };

  const handleRoleChange = async (user_id, newRole) => {
    setUpdatingId(user_id);
    try {
      await api.patch(`/users/${user_id}/role`, { role: newRole });
      await loadUsers();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to update user role.');
    } finally {
      setUpdatingId(null);
    }
  };

  const handleOpenModal = () => {
    setFormData({
      full_name: '',
      username: '',
      email: '',
      password: '',
      role: 'cashier',
      status: 'Active',
    });
    setModalError('');
    setModalOpen(true);
  };

  const handleCreateUser = async (e) => {
    e.preventDefault();
    setModalLoading(true);
    setModalError('');

    try {
      await api.post('/users', formData);
      setModalOpen(false);
      await loadUsers();
    } catch (err) {
      setModalError(err.response?.data?.error || 'Failed to create user account.');
    } finally {
      setModalLoading(false);
    }
  };

  const filteredUsers = users.filter(u =>
    u.full_name?.toLowerCase().includes(search.toLowerCase()) ||
    u.username?.toLowerCase().includes(search.toLowerCase()) ||
    u.email?.toLowerCase().includes(search.toLowerCase()) ||
    u.role?.toLowerCase().includes(search.toLowerCase())
  );

  return (
    <div className="home-page min-h-screen flex selection:bg-rose-100 selection:text-rose-600">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        {/* Header */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
          <div>
            <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">User Management</span>
            <h1 className="font-bold text-2xl sm:text-3xl text-ink-900 mt-1" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              User Accounts &amp; Roles
            </h1>
            <p className="text-xs sm:text-sm text-ink-600">Create accounts, assign Cashier/Admin permissions, and manage access</p>
          </div>

          <button
            onClick={handleOpenModal}
            className="home-btn-primary py-2.5 px-5 text-sm shrink-0 flex items-center gap-2 shadow-sm"
          >
            <span>➕</span>
            <span>Add New User</span>
          </button>
        </div>

        {/* Search Bar */}
        <div className="mb-6 max-w-md">
          <input
            type="text"
            placeholder="Search by name, @username, email, or role..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full px-4 py-2.5 rounded-xl bg-white border border-cream-300 text-ink-900 placeholder-ink-400 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 shadow-xs"
          />
        </div>

        {/* Users Table */}
        {loading ? (
          <div className="bg-white border border-cream-300 rounded-2xl h-96 animate-pulse" />
        ) : (
          <div className="bg-white border border-cream-300 rounded-2xl overflow-hidden shadow-sm">
            <div className="overflow-x-auto">
              <table className="w-full text-sm text-left">
                <thead className="bg-cream-100 border-b border-cream-300 text-ink-900 font-semibold uppercase text-xs">
                  <tr>
                    <th className="px-6 py-4">User</th>
                    <th className="px-6 py-4">Email</th>
                    <th className="px-6 py-4">Assigned Role</th>
                    <th className="px-6 py-4">Account Status</th>
                    <th className="px-6 py-4">Joined Date</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-cream-200">
                  {filteredUsers.map((u) => (
                    <tr key={u.user_id} className="hover:bg-cream-50 transition-colors">
                      <td className="px-6 py-4">
                        <div>
                          <p className="font-semibold text-ink-900 text-sm">{u.full_name}</p>
                          <p className="text-xs text-ink-600">@{u.username}</p>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-xs text-ink-600">{u.email}</td>
                      <td className="px-6 py-4">
                        {/* Role Selector */}
                        <select
                          value={u.role}
                          disabled={updatingId === u.user_id}
                          onChange={(e) => handleRoleChange(u.user_id, e.target.value)}
                          className={`text-xs font-semibold px-3 py-1.5 rounded-xl border focus:outline-none cursor-pointer transition-all ${
                            u.role === 'admin'
                              ? 'bg-rose-50 text-rose-700 border-rose-300'
                              : u.role === 'cashier'
                              ? 'bg-amber-50 text-amber-800 border-amber-300'
                              : 'bg-cream-100 text-ink-900 border-cream-300'
                          }`}
                        >
                          <option value="customer">Customer</option>
                          <option value="cashier">Cashier</option>
                          <option value="admin">Admin</option>
                        </select>
                      </td>
                      <td className="px-6 py-4">
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          u.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'
                        }`}>
                          {u.status}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-xs text-ink-600">
                        {new Date(u.created_at).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 text-right">
                        {u.role !== 'admin' && (
                          <button
                            onClick={() => handleToggleStatus(u.user_id, u.status)}
                            className={`px-3 py-1 text-xs font-semibold rounded-lg transition-colors ${
                              u.status === 'Active'
                                ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100'
                                : 'bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100'
                            }`}
                          >
                            {u.status === 'Active' ? 'Deactivate' : 'Activate'}
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* ── Add User Modal Dialog ─────────────────────────── */}
        {modalOpen && (
          <div className="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 max-w-lg w-full shadow-2xl animate-slide-up">
              <div className="flex items-center justify-between border-b border-cream-200 pb-4 mb-6">
                <div>
                  <h3 className="font-bold text-xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                    Create New User
                  </h3>
                  <p className="text-xs text-ink-600 mt-0.5">Add staff or customer credentials with role assignment</p>
                </div>
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="p-1 rounded-lg text-ink-400 hover:text-ink-700 hover:bg-cream-100 transition-colors"
                >
                  ✕
                </button>
              </div>

              {modalError && (
                <div className="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                  {modalError}
                </div>
              )}

              <form onSubmit={handleCreateUser} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1">
                    Full Name *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Sarah Mensah"
                    value={formData.full_name}
                    onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                    className="w-full px-3.5 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                  />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1">
                      Username *
                    </label>
                    <input
                      type="text"
                      required
                      placeholder="e.g. smensah"
                      value={formData.username}
                      onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                      className="w-full px-3.5 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1">
                      Role *
                    </label>
                    <select
                      value={formData.role}
                      onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                      className="w-full px-3.5 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                    >
                      <option value="cashier">Cashier (POS Staff)</option>
                      <option value="admin">Admin (Manager)</option>
                      <option value="customer">Customer</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1">
                    Email Address *
                  </label>
                  <input
                    type="email"
                    required
                    placeholder="e.g. smensah@orchid.com"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full px-3.5 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1">
                    Initial Password *
                  </label>
                  <input
                    type="password"
                    required
                    placeholder="••••••••"
                    value={formData.password}
                    onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                    className="w-full px-3.5 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                  />
                </div>

                <div className="flex items-center justify-end gap-3 pt-4 border-t border-cream-200">
                  <button
                    type="button"
                    onClick={() => setModalOpen(false)}
                    className="home-btn-secondary py-2.5 px-4 text-xs"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={modalLoading}
                    className="home-btn-primary py-2.5 px-6 text-xs disabled:opacity-50"
                  >
                    {modalLoading ? 'CREATING USER...' : 'SAVE USER →'}
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
