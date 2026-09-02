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
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="font-display font-bold text-3xl text-white">Categories Management</h1>
            <p className="text-sm text-gray-400">Manage gift grouping categories</p>
          </div>
          <button onClick={() => handleOpenModal()} className="btn-orchid text-sm py-2.5 px-4">
            + New Category
          </button>
        </div>

        {loading ? (
          <div className="glass-card h-64 animate-pulse" />
        ) : (
          <div className="glass-card overflow-hidden">
            <table className="orchid-table">
              <thead>
                <tr>
                  <th>Category Name</th>
                  <th>Description</th>
                  <th className="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                {categories.map((c) => (
                  <tr key={c.category_id}>
                    <td className="font-semibold text-white">{c.name}</td>
                    <td className="text-xs text-gray-400">{c.description}</td>
                    <td className="text-right space-x-2">
                      <button onClick={() => handleOpenModal(c)} className="btn-ghost py-1 px-3 text-xs">
                        Edit
                      </button>
                      <button onClick={() => handleDelete(c.category_id)} className="btn-danger py-1 px-3 text-xs">
                        Delete
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {modalOpen && (
          <div className="modal-overlay" onClick={() => setModalOpen(false)}>
            <div className="modal-box max-w-md" onClick={(e) => e.stopPropagation()}>
              <h3 className="font-display font-bold text-lg text-white mb-4">
                {editingCat ? 'Edit Category' : 'Add Category'}
              </h3>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="input-label">Category Name</label>
                  <input
                    type="text"
                    required
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="input-orchid py-2 text-sm"
                  />
                </div>
                <div>
                  <label className="input-label">Description</label>
                  <textarea
                    rows={3}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="input-orchid text-sm"
                  />
                </div>
                <div className="flex justify-end gap-2 pt-2">
                  <button type="button" onClick={() => setModalOpen(false)} className="btn-ghost py-2 px-4 text-xs">
                    Cancel
                  </button>
                  <button type="submit" className="btn-orchid py-2 px-4 text-xs">
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
