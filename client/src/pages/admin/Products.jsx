import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function AdminProducts() {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingProd, setEditingProd] = useState(null);

  const [formData, setFormData] = useState({
    name: '',
    category_id: '',
    description: '',
    price: '',
    stock_quantity: 0,
    image_url: '',
  });

  const loadData = async () => {
    try {
      const [prodRes, catRes] = await Promise.all([
        api.get('/products?limit=100'),
        api.get('/categories'),
      ]);
      setProducts(prodRes.data.products || []);
      setCategories(catRes.data || []);
    } catch (err) {
      console.error('Failed to load products', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleOpenModal = (prod = null) => {
    if (prod) {
      setEditingProd(prod);
      setFormData({
        name: prod.name,
        category_id: prod.category_id,
        description: prod.description || '',
        price: prod.price,
        stock_quantity: prod.stock_quantity,
        image_url: prod.image_url || '',
      });
    } else {
      setEditingProd(null);
      setFormData({
        name: '',
        category_id: categories[0]?.category_id || '',
        description: '',
        price: '',
        stock_quantity: 10,
        image_url: '',
      });
    }
    setModalOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingProd) {
        await api.put(`/products/${editingProd.product_id}`, formData);
      } else {
        await api.post('/products', formData);
      }
      setModalOpen(false);
      loadData();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to save product.');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this product?')) return;
    try {
      await api.delete(`/products/${id}`);
      loadData();
    } catch (err) {
      alert(err.response?.data?.error || 'Delete failed.');
    }
  };

  return (
    <div className="home-page min-h-screen flex selection:bg-rose-100 selection:text-rose-600">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">Inventory Management</span>
            <h1 className="font-bold text-3xl text-ink-900 mt-1" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Products Catalog
            </h1>
            <p className="text-sm text-ink-600">Add, edit, and track stock for all boutique gift items</p>
          </div>
          <button
            onClick={() => handleOpenModal()}
            className="home-btn-primary py-2.5 px-5 text-sm"
          >
            + Add New Product
          </button>
        </div>

        {loading ? (
          <div className="bg-white border border-cream-300 rounded-2xl h-96 animate-pulse" />
        ) : (
          <div className="bg-white border border-cream-300 rounded-2xl overflow-hidden shadow-sm">
            <table className="w-full text-sm text-left">
              <thead className="bg-cream-100 border-b border-cream-300 text-ink-900 font-semibold uppercase text-xs">
                <tr>
                  <th className="px-6 py-4">Product</th>
                  <th className="px-6 py-4">Category</th>
                  <th className="px-6 py-4">Price</th>
                  <th className="px-6 py-4">Stock</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-cream-200">
                {products.map((p) => (
                  <tr key={p.product_id} className="hover:bg-cream-50 transition-colors">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <img
                          src={p.image_url || 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=600&q=80'}
                          alt=""
                          className="w-10 h-10 object-cover rounded-xl bg-cream-100 border border-cream-300 shrink-0"
                        />
                        <div>
                          <p className="font-semibold text-ink-900 text-sm">{p.name}</p>
                          <p className="text-xs text-ink-600 line-clamp-1">{p.description}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className="bg-rose-50 border border-rose-200 text-rose-600 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                        {p.category_name}
                      </span>
                    </td>
                    <td className="px-6 py-4 font-bold text-rose-500">GH₵{parseFloat(p.price).toFixed(2)}</td>
                    <td className="px-6 py-4">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        p.stock_quantity <= 3 ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                      }`}>
                        {p.stock_quantity}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right space-x-2">
                      <button onClick={() => handleOpenModal(p)} className="home-btn-secondary py-1 px-3 text-xs">
                        Edit
                      </button>
                      <button onClick={() => handleDelete(p.product_id)} className="px-3 py-1 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                        Delete
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Modal */}
        {modalOpen && (
          <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={() => setModalOpen(false)}>
            <div className="bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 w-full max-w-md shadow-2xl animate-slide-up" onClick={(e) => e.stopPropagation()}>
              <h3 className="font-bold text-xl text-ink-900 mb-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                {editingProd ? 'Edit Product' : 'Add New Product'}
              </h3>

              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Product Name</label>
                  <input
                    type="text"
                    required
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Category</label>
                  <select
                    value={formData.category_id}
                    onChange={(e) => setFormData({ ...formData, category_id: parseInt(e.target.value) })}
                    className="w-full px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white"
                  >
                    {categories.map((c) => (
                      <option key={c.category_id} value={c.category_id}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Price (GH₵)</label>
                    <input
                      type="number"
                      step="0.01"
                      required
                      value={formData.price}
                      onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                      className="w-full px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Stock</label>
                    <input
                      type="number"
                      required
                      value={formData.stock_quantity}
                      onChange={(e) => setFormData({ ...formData, stock_quantity: parseInt(e.target.value) })}
                      className="w-full px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Image URL</label>
                  <input
                    type="url"
                    value={formData.image_url}
                    onChange={(e) => setFormData({ ...formData, image_url: e.target.value })}
                    placeholder="https://..."
                    className="w-full px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">Description</label>
                  <textarea
                    rows={3}
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white"
                  />
                </div>

                <div className="flex justify-end gap-3 pt-4 border-t border-cream-300">
                  <button type="button" onClick={() => setModalOpen(false)} className="home-btn-secondary py-2 px-4 text-xs">
                    Cancel
                  </button>
                  <button type="submit" className="home-btn-primary py-2 px-4 text-xs">
                    Save Product
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
