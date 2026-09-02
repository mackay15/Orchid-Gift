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
    <div className="min-h-screen bg-gray-950 flex text-gray-100">
      <Sidebar />

      <main className="w-full min-w-0 pt-20 lg:pt-8 lg:pl-64 flex-1 p-4 sm:p-6 lg:p-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="font-display font-bold text-3xl text-white">Review Moderation</h1>
            <p className="text-sm text-gray-400">Approve or reject customer product reviews</p>
          </div>

          <div className="flex gap-2">
            {['Pending', 'Approved', 'Rejected', ''].map((st) => (
              <button
                key={st}
                onClick={() => setFilter(st)}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold ${
                  filter === st ? 'bg-orchid-800/40 text-orchid-200 border border-orchid-700/30' : 'bg-white/5 text-gray-400'
                }`}
              >
                {st || 'All'}
              </button>
            ))}
          </div>
        </div>

        {loading ? (
          <div className="glass-card h-64 animate-pulse" />
        ) : reviews.length === 0 ? (
          <div className="glass-card p-12 text-center text-gray-400 text-sm">
            No {filter} reviews found.
          </div>
        ) : (
          <div className="space-y-4">
            {reviews.map((r) => (
              <div key={r.review_id} className="glass-card p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div className="space-y-2">
                  <div className="flex items-center gap-3">
                    <span className="font-semibold text-white text-base">{r.product_name}</span>
                    <StarRating rating={r.rating} />
                    <span className={`badge ${
                      r.status === 'Approved' ? 'badge-success' :
                      r.status === 'Rejected' ? 'badge-danger' : 'badge-warning'
                    }`}>
                      {r.status}
                    </span>
                  </div>
                  <p className="text-sm text-gray-300">"{r.review}"</p>
                  <p className="text-xs text-gray-500">
                    Submitted by <strong className="text-gray-400">{r.customer_name}</strong> on {new Date(r.created_at).toLocaleDateString()}
                  </p>
                </div>

                <div className="flex items-center gap-2">
                  {r.status !== 'Approved' && (
                    <button onClick={() => handleModerate(r.review_id, 'Approved')} className="btn-success py-1.5 px-3 text-xs">
                      Approve
                    </button>
                  )}
                  {r.status !== 'Rejected' && (
                    <button onClick={() => handleModerate(r.review_id, 'Rejected')} className="btn-danger py-1.5 px-3 text-xs">
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
