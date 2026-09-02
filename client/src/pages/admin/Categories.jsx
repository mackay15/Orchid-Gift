import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function AdminCategories() {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingCat, setEditingCat] = useState(null);
  const [formData, setFormData] = useState({ name: '', description: '' });

  const loadCategories = async () => {
    try {
      const res = await api.get('/categories');
      setCategories(res.data || []);
    } catch (err) {
      console.error('Failed to load categories', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCategories();
  }, []);

  const handleOpenModal = (cat = null) => {
    if (cat) {
      setEditingCat(cat);
      setFormData({ name: cat.name, description: cat.description || '' });
    } else {
      setEditingCat(null);
      setFormData({ name: '', description: '' });
    }
    setModalOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingCat) {
        await api.put(`/categories/${editingCat.category_id}`, formData);
      } else {
        await api.post('/categories', formData);
      }
      setModalOpen(false);
      loadCategories();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to save category.');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Delete category? Attached products will also be deleted.')) return;
    try {
      await api.delete(`/categories/${id}`);
      loadCategories();
    } catch (err) {
      alert(err.response?.data?.error || 'Delete failed.');
    }
  };

  return (
    <div className="min-h-screen bg-cream-50 flex text-ink-900 font-sans selection:bg-rose-200 selection:text-ink-900">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="font-bold text-3xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Categories Management
            </h1>
            <p className="text-sm text-ink-600 mt-1">Manage gift grouping categories</p>
          </div>
          <button 
            onClick={() => handleOpenModal()} 
            className="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2.5 px-4 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]"
          >
            + New Category
          </button>
        </div>

        {loading ? (
          <div className="bg-white border border-cream-200 rounded-2xl p-6 h-64 animate-pulse shadow-sm" />
        ) : (
          <div className="bg-white border border-cream-200 rounded-2xl shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead className="bg-cream-50/50 border-b border-cream-200">
                  <tr>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Category Name</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500">Description</th>
                    <th className="px-6 py-4 text-xs font-bold uppercase tracking-wider text-ink-500 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-cream-100">
                  {categories.map((c) => (
                    <tr key={c.category_id} className="hover:bg-cream-50/50 transition-colors">
                      <td className="px-6 py-4 font-semibold text-ink-900">{c.name}</td>
                      <td className="px-6 py-4 text-sm text-ink-600">{c.description}</td>
                      <td className="px-6 py-4 text-right space-x-2">
                        <button 
                          onClick={() => handleOpenModal(c)} 
                          className="px-3 py-1.5 text-xs font-semibold rounded-lg bg-cream-100 text-ink-700 hover:bg-cream-200 transition-colors"
                        >
                          Edit
                        </button>
                        <button 
                          onClick={() => handleDelete(c.category_id)} 
                          className="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 transition-colors"
                        >
                          Delete
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {modalOpen && (
          <div className="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 max-w-md w-full shadow-2xl animate-slide-up" onClick={(e) => e.stopPropagation()}>
              <div className="flex items-center justify-between border-b border-cream-200 pb-4 mb-6">
                <h3 className="font-bold text-xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                  {editingCat ? 'Edit Category' : 'Add Category'}
                </h3>
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="p-1 rounded-lg text-ink-400 hover:text-ink-700 hover:bg-cream-100 transition-colors"
                >
                  ✕
                </button>
              </div>
              
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1">
                    Category Name *
                  </label>
                  <input
                    type="text"
                    required
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full px-3.5 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1">
                    Description
                  </label>
                  <textarea
                    rows={3}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full px-3.5 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
                  />
                </div>
                <div className="flex justify-end gap-3 pt-4 border-t border-cream-100 mt-6">
                  <button 
                    type="button" 
                    onClick={() => setModalOpen(false)} 
                    className="px-4 py-2 text-sm font-semibold text-ink-600 bg-cream-100 hover:bg-cream-200 rounded-xl transition-colors"
                  >
                    Cancel
                  </button>
                  <button 
                    type="submit" 
                    className="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-all active:scale-[0.98]"
                  >
                    Save Category
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
