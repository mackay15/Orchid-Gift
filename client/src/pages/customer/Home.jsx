import React, { useEffect, useState } from 'react';
import AnnouncementBar from '../../components/home/AnnouncementBar';
import HomeNavbar from '../../components/home/HomeNavbar';
import HeroSection from '../../components/home/HeroSection';
import BenefitsBar from '../../components/home/BenefitsBar';
import CategorySection from '../../components/home/CategorySection';
import BestSellersSection from '../../components/home/BestSellersSection';
import PromoSection from '../../components/home/PromoSection';
import OccasionSection from '../../components/home/OccasionSection';
import TrustSection from '../../components/home/TrustSection';
import NewsletterSection from '../../components/home/NewsletterSection';
import HomeFooter from '../../components/home/HomeFooter';
import api from '../../api/axios';

export default function Home() {
  const [featured, setFeatured] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchData() {
      try {
        const [prodRes, catRes] = await Promise.all([
          api.get('/products?limit=8'),
          api.get('/categories'),
        ]);
        setFeatured(prodRes.data.products || []);
        setCategories(catRes.data || []);
      } catch (err) {
        console.error('Failed to load home data', err);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, []);

  return (
    <div className="home-page min-h-screen flex flex-col selection:bg-rose-100 selection:text-rose-600">
      {/* 1. Top Announcement Bar */}
      <AnnouncementBar />

      {/* 2. Main Navigation Bar */}
      <HomeNavbar />

      {/* Main Content */}
      <main className="flex-1">
        {/* 3. Hero Section */}
        <HeroSection />

        {/* 4. Service / Benefits Bar */}
        <BenefitsBar />

        {/* 5. Shop by Category */}
        <CategorySection apiCategories={categories} />

        {/* 6. Best Sellers / Featured Products */}
        <BestSellersSection products={featured} loading={loading} />

        {/* 7. Promotional Banner Cards */}
        <PromoSection />

        {/* 8. Featured by Occasion */}
        <OccasionSection />

        {/* 9. Trust / Benefits Section */}
        <TrustSection />

        {/* 10. Newsletter Section */}
        <NewsletterSection />
      </main>

      {/* 11. Footer & Bottom Footer */}
      <HomeFooter />
    </div>
  );
}
