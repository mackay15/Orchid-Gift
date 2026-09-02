import React, { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import Navbar from '../../components/Navbar';
import Footer from '../../components/Footer';
import ProductCard from '../../components/ProductCard';
import api from '../../api/axios';

export default function Shop() {
  const [searchParams, setSearchParams] = useSearchParams();
  const selectedCat = searchParams.get('category') || '';
  const searchArg = searchParams.get('search') || '';

  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [searchQuery, setSearchQuery] = useState(searchArg);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadData() {
      setLoading(true);
      try {
        const [catRes, prodRes] = await Promise.all([
          api.get('/categories'),
          api.get('/products', {
            params: {
              category_id: selectedCat || undefined,
              search: searchArg || undefined,
              limit: 50,
            },
          }),
        ]);
        setCategories(catRes.data || []);
        setProducts(prodRes.data.products || []);
      } catch (err) {
        console.error('Failed to fetch shop products', err);
      } finally {
        setLoading(false);
      }
    }
    loadData();
  }, [selectedCat, searchArg]);

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    const newParams = new URLSearchParams(searchParams);
    if (searchQuery) newParams.set('search', searchQuery);
    else newParams.delete('search');
    setSearchParams(newParams);
  };

  const handleCategorySelect = (catId) => {
    const newParams = new URLSearchParams(searchParams);
    if (catId) newParams.set('category', catId);
    else newParams.delete('category');
    setSearchParams(newParams);
  };

  return (
    <div className="home-page min-h-screen flex flex-col selection:bg-rose-100 selection:text-rose-600">
      <Navbar />

      <main className="py-12 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto flex-1 w-full">
        {/* Search Header */}
        <div className="mb-8 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-6 bg-white border border-cream-300 rounded-2xl p-6 sm:p-8 shadow-sm">
          <div>
            <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">
              Boutique Catalog
            </span>
            <h1 className="font-bold text-3xl text-ink-900 mt-1" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Explore Our Gifts
            </h1>
            <p className="text-sm text-ink-600 mt-1">Browse luxury flowers, hampers, chocolates, and celebration items</p>
          </div>

          <form onSubmit={handleSearchSubmit} className="flex gap-2 min-w-[300px]">
            <input
              type="text"
              placeholder="Search gifts..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="flex-1 px-4 py-2.5 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-300 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300 focus:bg-white transition-all"
            />
            <button type="submit" className="home-btn-primary py-2.5 px-5 text-sm shrink-0">
              Search
            </button>
          </form>
        </div>

        {/* Filter Pills */}
        <div className="flex items-center gap-2 overflow-x-auto pb-4 mb-8 no-scrollbar">
          <button
            onClick={() => handleCategorySelect('')}
            className={`px-5 py-2.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all ${
              !selectedCat
                ? 'bg-rose-500 text-white shadow-md'
                : 'bg-white border border-cream-300 text-ink-600 hover:text-rose-500 hover:border-rose-300'
            }`}
          >
            All Categories
          </button>
          {categories.map((cat) => (
            <button
              key={cat.category_id}
              onClick={() => handleCategorySelect(cat.category_id.toString())}
              className={`px-5 py-2.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all ${
                selectedCat === cat.category_id.toString()
                  ? 'bg-rose-500 text-white shadow-md'
                  : 'bg-white border border-cream-300 text-ink-600 hover:text-rose-500 hover:border-rose-300'
              }`}
            >
              {cat.name}
            </button>
          ))}
        </div>

        {/* Products Grid */}
        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {[1, 2, 3, 4, 5, 6, 7, 8].map((n) => (
              <div key={n} className="home-product-card h-80 animate-pulse bg-cream-100 border-cream-300" />
            ))}
          </div>
        ) : products.length === 0 ? (
          <div className="bg-white border border-cream-300 rounded-2xl p-12 text-center my-12 shadow-sm">
            <span className="text-4xl">🔍</span>
            <h3 className="font-bold text-xl text-ink-900 mt-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>No Gifts Found</h3>
            <p className="text-ink-600 text-sm mt-1">Try resetting your filters or search keywords.</p>
            <button
              onClick={() => { setSearchQuery(''); setSearchParams({}); }}
              className="home-btn-secondary mt-6 text-xs py-2.5 px-5"
            >
              Clear Filters
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {products.map((p) => (
              <ProductCard key={p.product_id} product={p} />
            ))}
          </div>
        )}
      </main>

      <Footer />
    </div>
  );
}
