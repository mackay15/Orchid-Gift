import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Sidebar from '../../components/Sidebar';
import api from '../../api/axios';

export default function CashierPOS() {
  const navigate = useNavigate();

  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCat, setSelectedCat] = useState('');
  const [search, setSearch] = useState('');

  // Customer selection & quick-add states
  const [customers, setCustomers] = useState([]);
  const [selectedCustomerId, setSelectedCustomerId] = useState('');
  const [custModalOpen, setCustModalOpen] = useState(false);
  const [newCustForm, setNewCustForm] = useState({ full_name: '', username: '', email: '', password: 'customer123' });
  const [custSubmitting, setCustSubmitting] = useState(false);

  const [cart, setCart] = useState([]);
  const [paymentMethod, setPaymentMethod] = useState('Cash');
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  const loadPOSData = async () => {
    try {
      const [prodRes, catRes, custRes] = await Promise.all([
        api.get('/products?limit=100'),
        api.get('/categories'),
        api.get('/cashier/customers'),
      ]);
      setProducts(prodRes.data.products || []);
      setCategories(catRes.data || []);
      setCustomers(custRes.data || []);
    } catch (err) {
      console.error('Failed to load POS data', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadPOSData();
  }, []);

  const handleQuickRegisterCustomer = async (e) => {
    e.preventDefault();
    setCustSubmitting(true);
    try {
      const res = await api.post('/auth/register', {
        ...newCustForm,
        role: 'customer',
      });
      alert('Walk-in customer registered successfully!');
      setCustModalOpen(false);
      setNewCustForm({ full_name: '', username: '', email: '', password: 'customer123' });
      await loadPOSData();
      if (res.data?.user?.user_id) {
        setSelectedCustomerId(res.data.user.user_id.toString());
      }
    } catch (err) {
      alert(err.response?.data?.error || 'Registration failed.');
    } finally {
      setCustSubmitting(false);
    }
  };

  const addToCart = (p) => {
    if (p.stock_quantity <= 0) return;
    setCart((prev) => {
      const existing = prev.find((i) => i.product_id === p.product_id);
      if (existing) {
        return prev.map((i) =>
          i.product_id === p.product_id
            ? { ...i, quantity: Math.min(i.quantity + 1, p.stock_quantity) }
            : i
        );
      }
      return [...prev, { ...p, quantity: 1 }];
    });
  };

  const updateCartQty = (id, qty) => {
    if (qty <= 0) {
      setCart((prev) => prev.filter((i) => i.product_id !== id));
      return;
    }
    setCart((prev) => prev.map((i) => (i.product_id === id ? { ...i, quantity: qty } : i)));
  };

  const cartTotal = cart.reduce((sum, i) => sum + parseFloat(i.price) * i.quantity, 0);

  const handleCheckout = async () => {
    if (!cart.length) return;
    setSubmitting(true);
    try {
      const res = await api.post('/cashier/order', {
        items: cart.map((i) => ({ product_id: i.product_id, quantity: i.quantity })),
        payment_method: paymentMethod,
        customer_id: selectedCustomerId ? parseInt(selectedCustomerId) : null,
      });

      const order = res.data;
      setCart([]);
      navigate(`/cashier/receipt/${order.order_id}`);
    } catch (err) {
      alert(err.response?.data?.error || 'POS checkout failed.');
    } finally {
      setSubmitting(false);
    }
  };

  const filteredProducts = products.filter((p) => {
    const matchCat = !selectedCat || p.category_id.toString() === selectedCat;
    const matchSearch =
      !search ||
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      p.category_name?.toLowerCase().includes(search.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-16 lg:pt-0 lg:pl-64 flex-1 flex flex-col lg:flex-row h-auto lg:h-screen overflow-y-auto lg:overflow-hidden">
        {/* Left: Product Grid & Search */}
        <div className="flex-1 p-4 sm:p-6 flex flex-col h-auto lg:h-full overflow-visible lg:overflow-hidden border-b lg:border-b-0 lg:border-r border-white/10">
          <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-6">
            <h1 className="font-display font-bold text-2xl text-white">Walk-in POS Terminal</h1>
            <input
              type="text"
              placeholder="Quick search products..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="input-orchid py-2 px-4 text-xs w-full sm:w-64"
            />
          </div>

          {/* Category Filter */}
          <div className="flex gap-2 overflow-x-auto pb-3 mb-4 shrink-0 no-scrollbar">
            <button
              onClick={() => setSelectedCat('')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap ${
                !selectedCat ? 'bg-orchid-gradient text-white' : 'bg-white/5 text-gray-400'
              }`}
            >
              All Items
            </button>
            {categories.map((c) => (
              <button
                key={c.category_id}
                onClick={() => setSelectedCat(c.category_id.toString())}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap ${
                  selectedCat === c.category_id.toString() ? 'bg-orchid-gradient text-white' : 'bg-white/5 text-gray-400'
                }`}
              >
                {c.name}
              </button>
            ))}
          </div>

          {/* Products Grid */}
          {loading ? (
            <div className="grid grid-cols-3 gap-4 animate-pulse flex-1" />
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4 overflow-y-auto pr-1 flex-1">
              {filteredProducts.map((p) => {
                const isOut = p.stock_quantity <= 0;
                return (
                  <button
                    key={p.product_id}
                    disabled={isOut}
                    onClick={() => addToCart(p)}
                    className="glass-card-hover p-3 text-left flex flex-col justify-between h-40 disabled:opacity-50 disabled:cursor-not-allowed group"
                  >
                    <div>
                      <span className="badge-orchid text-[10px] mb-1 inline-block">{p.category_name}</span>
                      <h4 className="font-semibold text-sm text-white line-clamp-1 group-hover:text-orchid-300">
                        {p.name}
                      </h4>
                    </div>
                    <div className="flex items-center justify-between border-t border-white/10 pt-2">
                      <span className="font-bold text-sm text-orchid-300">GH₵{parseFloat(p.price).toFixed(2)}</span>
                      <span className={`text-[10px] font-semibold ${isOut ? 'text-red-400' : 'text-emerald-400'}`}>
                        {isOut ? 'Out' : `${p.stock_quantity} left`}
                      </span>
                    </div>
                  </button>
                );
              })}
            </div>
          )}
        </div>

        {/* Right: Current Order Cart */}
        <div className="w-full lg:w-96 p-4 sm:p-6 flex flex-col h-auto lg:h-full bg-gray-900/40">
          <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
            <h2 className="font-display font-bold text-lg text-white">Current Order</h2>
            <button onClick={() => setCart([])} className="text-xs text-red-400 hover:underline">
              Clear
            </button>
          </div>

          {/* Cart Items List */}
          <div className="flex-1 overflow-y-auto space-y-3 pr-1">
            {cart.length === 0 ? (
              <div className="h-full flex items-center justify-center text-gray-500 text-xs text-center">
                Click products to add to current walk-in order
              </div>
            ) : (
              cart.map((item) => (
                <div key={item.product_id} className="p-3 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between text-sm">
                  <div className="min-w-0 flex-1 pr-2">
                    <p className="text-white font-medium truncate">{item.name}</p>
                    <p className="text-xs text-orchid-300">GH₵{(parseFloat(item.price) * item.quantity).toFixed(2)}</p>
                  </div>
                  <div className="flex items-center gap-1.5 bg-white/10 rounded-lg px-2 py-1">
                    <button onClick={() => updateCartQty(item.product_id, item.quantity - 1)} className="text-xs font-bold px-1 text-gray-300">-</button>
                    <span className="text-xs font-bold text-white px-1">{item.quantity}</span>
                    <button onClick={() => updateCartQty(item.product_id, item.quantity + 1)} className="text-xs font-bold px-1 text-gray-300">+</button>
                  </div>
                </div>
              ))
            )}
          </div>

          {/* Customer Selection & Checkout */}
          <div className="pt-4 border-t border-white/10 space-y-4">
            {/* Customer Selector */}
            <div>
              <div className="flex justify-between items-center mb-1">
                <label className="input-label text-xs">Customer Account</label>
                <button
                  type="button"
                  onClick={() => setCustModalOpen(true)}
                  className="text-[11px] text-orchid-400 hover:text-orchid-300 font-semibold"
                >
                  + New Customer
                </button>
              </div>
              <select
                value={selectedCustomerId}
                onChange={(e) => setSelectedCustomerId(e.target.value)}
                className="input-orchid py-2 text-xs bg-gray-900 border-white/15"
              >
                <option value="">Walk-in Guest (Guest Checkout)</option>
                {customers.map((c) => (
                  <option key={c.user_id} value={c.user_id}>
                    {c.full_name} (@{c.username})
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="input-label text-xs">Payment Method</label>
              <div className="grid grid-cols-3 gap-2">
                {['Cash', 'Card', 'Mobile Money'].map((m) => (
                  <button
                    key={m}
                    type="button"
                    onClick={() => setPaymentMethod(m)}
                    className={`py-2 text-xs font-semibold rounded-lg border transition-all ${
                      paymentMethod === m
                        ? 'bg-orchid-800/60 border-orchid-500 text-white'
                        : 'bg-white/5 border-white/10 text-gray-400'
                    }`}
                  >
                    {m}
                  </button>
                ))}
              </div>
            </div>

            <div className="flex justify-between items-center text-xl font-display font-bold text-white">
              <span>Total:</span>
              <span className="text-orchid-300">GH₵{cartTotal.toFixed(2)}</span>
            </div>

            <button
              disabled={submitting || cart.length === 0}
              onClick={handleCheckout}
              className="btn-orchid w-full py-3 text-base font-semibold"
            >
              {submitting ? 'Processing...' : 'Complete Walk-in Sale'}
            </button>
          </div>
        </div>
      </main>

      {/* Quick Customer Register Modal */}
      {custModalOpen && (
        <div className="modal-overlay" onClick={() => setCustModalOpen(false)}>
          <div className="modal-box max-w-sm" onClick={(e) => e.stopPropagation()}>
            <h3 className="font-display font-bold text-lg text-white mb-4">Register Walk-in Customer</h3>
            <form onSubmit={handleQuickRegisterCustomer} className="space-y-3">
              <div>
                <label className="input-label text-xs">Full Name</label>
                <input
                  type="text"
                  required
                  value={newCustForm.full_name}
                  onChange={(e) => setNewCustForm({ ...newCustForm, full_name: e.target.value })}
                  className="input-orchid py-2 text-xs"
                />
              </div>
              <div>
                <label className="input-label text-xs">Username</label>
                <input
                  type="text"
                  required
                  value={newCustForm.username}
                  onChange={(e) => setNewCustForm({ ...newCustForm, username: e.target.value.toLowerCase().replace(/\s+/g, '') })}
                  className="input-orchid py-2 text-xs"
                />
              </div>
              <div>
                <label className="input-label text-xs">Email</label>
                <input
                  type="email"
                  required
                  value={newCustForm.email}
                  onChange={(e) => setNewCustForm({ ...newCustForm, email: e.target.value })}
                  className="input-orchid py-2 text-xs"
                />
              </div>
              <div className="flex justify-end gap-2 pt-3">
                <button type="button" onClick={() => setCustModalOpen(false)} className="btn-ghost py-1.5 px-3 text-xs">
                  Cancel
                </button>
                <button type="submit" disabled={custSubmitting} className="btn-orchid py-1.5 px-4 text-xs">
                  {custSubmitting ? 'Saving...' : 'Register & Select'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

