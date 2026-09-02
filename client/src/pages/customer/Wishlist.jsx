import React, { useEffect, useState } from 'react';
import Navbar from '../../components/Navbar';
import Footer from '../../components/Footer';
import ProductCard from '../../components/ProductCard';
import api from '../../api/axios';

export default function Wishlist() {
  const [wishlist, setWishlist] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchWishlist() {
      try {
        const res = await api.get('/wishlist');
        setWishlist(res.data);
      } catch (err) {
        console.error('Failed to fetch wishlist', err);
      } finally {
        setLoading(false);
      }
    }
    fetchWishlist();
  }, []);

  return (
    <div className="home-page min-h-screen flex flex-col selection:bg-rose-100 selection:text-rose-600">
      <Navbar />

      <main className="py-12 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto flex-1 w-full">
        <span className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase">
          Saved Items
        </span>
        <h1 className="font-bold text-3xl sm:text-4xl text-ink-900 mt-1 mb-2" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
          My Saved Wishlist
        </h1>
        <p className="text-sm text-ink-600 mb-8">Quickly access items you saved for upcoming special occasions</p>

        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {[1, 2, 3, 4].map((n) => (
              <div key={n} className="home-product-card h-80 animate-pulse bg-cream-100 border-cream-300" />
            ))}
          </div>
        ) : wishlist.length === 0 ? (
          <div className="bg-white border border-cream-300 rounded-2xl p-12 text-center my-8 shadow-sm">
            <span className="text-4xl">❤️</span>
            <h3 className="font-bold text-xl text-ink-900 mt-4" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>Your Wishlist is Empty</h3>
            <p className="text-ink-600 text-sm mt-1">Save gifts as you browse the shop to find them here easily.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {wishlist.map((item) => (
              <ProductCard
                key={item.wishlist_id}
                product={{
                  product_id: item.product_id,
                  name: item.name,
                  price: item.price,
                  image_url: item.image_url,
                  stock_quantity: item.stock_quantity,
                }}
              />
            ))}
          </div>
        )}
      </main>

      <Footer />
    </div>
  );
}
