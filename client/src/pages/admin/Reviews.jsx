import React, { useEffect, useState } from 'react';
import Sidebar from '../../components/Sidebar';
import StarRating from '../../components/StarRating';
import api from '../../api/axios';

export default function AdminReviews() {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('Pending');

  const loadReviews = async () => {
    try {
      const res = await api.get('/reviews', { params: { status: filter || undefined } });
      setReviews(res.data || []);
    } catch (err) {
      console.error('Failed to load reviews', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadReviews();
  }, [filter]);

  const handleModerate = async (review_id, status) => {
    try {
      await api.patch(`/reviews/${review_id}/status`, { status });
      loadReviews();
    } catch (err) {
      alert(err.response?.data?.error || 'Moderation action failed.');
    }
  };

  return (
    <div className="min-h-screen bg-cream-50 flex text-ink-900 font-sans selection:bg-rose-200 selection:text-ink-900">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="font-bold text-3xl text-ink-900" style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
              Review Moderation
            </h1>
            <p className="text-sm text-ink-600 mt-1">Approve or reject customer product reviews</p>
          </div>

          <div className="flex gap-2">
            {['Pending', 'Approved', 'Rejected', ''].map((st) => (
              <button
                key={st}
                onClick={() => setFilter(st)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors ${
                  filter === st 
                    ? 'bg-rose-100 text-rose-700 border border-rose-300' 
                    : 'bg-white text-ink-600 border border-cream-200 hover:bg-cream-100'
                }`}
              >
                {st || 'All'}
              </button>
            ))}
          </div>
        </div>

        {loading ? (
          <div className="bg-white border border-cream-200 rounded-2xl p-6 h-64 animate-pulse shadow-sm" />
        ) : reviews.length === 0 ? (
          <div className="bg-white border border-cream-200 rounded-2xl p-12 text-center text-ink-500 text-sm shadow-sm">
            No {filter} reviews found.
          </div>
        ) : (
          <div className="space-y-4">
            {reviews.map((r) => (
              <div key={r.review_id} className="bg-white border border-cream-200 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div className="space-y-2">
                  <div className="flex items-center gap-3">
                    <span className="font-bold text-ink-900 text-base">{r.product_name}</span>
                    <StarRating rating={r.rating} />
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${
                      r.status === 'Approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                      r.status === 'Rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'
                    }`}>
                      {r.status}
                    </span>
                  </div>
                  <p className="text-sm text-ink-700">"{r.review}"</p>
                  <p className="text-xs text-ink-500">
                    Submitted by <strong className="text-ink-800">{r.customer_name}</strong> on {new Date(r.created_at).toLocaleDateString()}
                  </p>
                </div>

                <div className="flex items-center gap-2">
                  {r.status !== 'Approved' && (
                    <button 
                      onClick={() => handleModerate(r.review_id, 'Approved')} 
                      className="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors"
                    >
                      Approve
                    </button>
                  )}
                  {r.status !== 'Rejected' && (
                    <button 
                      onClick={() => handleModerate(r.review_id, 'Rejected')} 
                      className="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors"
                    >
                      Reject
                    </button>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
