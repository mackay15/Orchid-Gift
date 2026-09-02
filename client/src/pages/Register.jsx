import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const GoogleIcon = () => (
  <svg width="20" height="20" viewBox="0 0 24 24">
    <path
      fill="#4285F4"
      d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"
    />
    <path
      fill="#34A853"
      d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.11-6.72-4.96H1.29v3.15C3.26 21.3 7.31 24 12 24z"
    />
    <path
      fill="#FBBC05"
      d="M5.28 14.24c-.25-.72-.38-1.49-.38-2.24s.13-1.52.38-2.24V6.61H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.39l3.99-3.15z"
    />
    <path
      fill="#EA4335"
      d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.61l3.99 3.15c.95-2.85 3.6-4.96 6.72-4.96z"
    />
  </svg>
);

export default function Register() {
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    full_name: '',
    password: '',
    confirm_password: '',
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [googleLoading, setGoogleLoading] = useState(false);

  const { register, loginWithGoogle } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (formData.password !== formData.confirm_password) {
      setError('Passwords do not match.');
      return;
    }

    setLoading(true);
    try {
      await register({
        username: formData.username,
        email: formData.email,
        full_name: formData.full_name,
        password: formData.password,
      });
      navigate('/shop');
    } catch (err) {
      setError(err.response?.data?.error || 'Registration failed. Try a different username/email.');
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleSignIn = async () => {
    setError('');
    setGoogleLoading(true);
    try {
      const user = await loginWithGoogle({
        email: 'customer@gmail.com',
        full_name: 'Google Customer',
        google_id: 'GOOGLE_USER_1001',
      });
      if (user.role === 'admin') navigate('/admin');
      else if (user.role === 'cashier') navigate('/cashier');
      else navigate('/shop');
    } catch (err) {
      setError(err.response?.data?.error || 'Google registration failed.');
    } finally {
      setGoogleLoading(false);
    }
  };

  return (
    <div className="home-page min-h-screen flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden"
      style={{ background: 'linear-gradient(135deg, #fdf2f5 0%, #F8F5F2 50%, #fce8ed 100%)' }}>

      {/* Decorative background shapes */}
      <div className="absolute -top-12 -left-12 w-64 h-64 rounded-full bg-rose-200/40 blur-3xl pointer-events-none" />
      <div className="absolute -bottom-12 -right-12 w-80 h-80 rounded-full bg-blush-200/50 blur-3xl pointer-events-none" />

      {/* Card container */}
      <div className="w-full max-w-md bg-white border border-cream-300 rounded-2xl p-8 sm:p-10 shadow-xl relative z-10 animate-fade-in">

        {/* Header */}
        <div className="text-center mb-8">
          <Link to="/" className="inline-flex items-center gap-2.5 mb-4 group" aria-label="Back to Homepage">
            <div className="w-11 h-11 rounded-xl overflow-hidden shadow-md group-hover:scale-105 transition-transform">
              <img src="/orchid_logo.png" alt="Orchid Gift" className="w-full h-full object-cover" />
            </div>
            <div className="text-left leading-none">
              <span className="block font-bold text-xl text-ink-900 tracking-tight"
                style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
                Orchid Gift
              </span>
              <span className="block text-xs text-rose-500 font-medium tracking-wide">
                &amp; More
              </span>
            </div>
          </Link>

          <h1 className="font-bold text-2xl text-ink-900 mt-2"
            style={{ fontFamily: "'Playfair Display', Georgia, serif" }}>
            Create Account
          </h1>
          <p className="text-sm text-ink-600 mt-1">Join Orchid Gift &amp; More to enjoy exclusive offers</p>
        </div>

        {/* Error message */}
        {error && (
          <div className="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{error}</span>
          </div>
        )}

        {/* Google SSO Button */}
        <button
          type="button"
          onClick={handleGoogleSignIn}
          disabled={googleLoading}
          className="w-full flex items-center justify-center gap-3 py-3 px-4 rounded-xl border border-cream-300 bg-white hover:bg-cream-100 text-ink-900 font-semibold text-sm shadow-xs transition-all mb-6"
        >
          <GoogleIcon />
          <span>{googleLoading ? 'Connecting to Google...' : 'Continue with Google'}</span>
        </button>

        {/* Divider */}
        <div className="relative flex items-center justify-center mb-6">
          <div className="border-t border-cream-300 w-full" />
          <span className="bg-white px-3 text-xs text-ink-600 font-medium shrink-0 uppercase tracking-wider">
            Or register with email
          </span>
          <div className="border-t border-cream-300 w-full" />
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">
              Full Name
            </label>
            <input
              type="text"
              name="full_name"
              required
              value={formData.full_name}
              onChange={handleChange}
              placeholder="e.g. Jane Doe"
              className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-300 text-sm
                         focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400 focus:bg-white transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">
              Username
            </label>
            <input
              type="text"
              name="username"
              required
              value={formData.username}
              onChange={handleChange}
              placeholder="janedoe"
              className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-300 text-sm
                         focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400 focus:bg-white transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">
              Email Address
            </label>
            <input
              type="email"
              name="email"
              required
              value={formData.email}
              onChange={handleChange}
              placeholder="jane@example.com"
              className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-300 text-sm
                         focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400 focus:bg-white transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">
              Password
            </label>
            <input
              type="password"
              name="password"
              required
              value={formData.password}
              onChange={handleChange}
              placeholder="••••••••"
              className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-300 text-sm
                         focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400 focus:bg-white transition-all"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-ink-900 mb-1.5">
              Confirm Password
            </label>
            <input
              type="password"
              name="confirm_password"
              required
              value={formData.confirm_password}
              onChange={handleChange}
              placeholder="••••••••"
              className="w-full px-4 py-3 rounded-xl bg-cream-100 border border-cream-300 text-ink-900 placeholder-ink-300 text-sm
                         focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400 focus:bg-white transition-all"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="home-btn-primary w-full justify-center py-3.5 mt-2 text-sm"
          >
            {loading ? 'CREATING ACCOUNT...' : 'REGISTER NOW →'}
          </button>
        </form>

        <div className="mt-6 text-center text-sm text-ink-600">
          Already have an account?{' '}
          <Link to="/login" className="text-rose-500 hover:text-rose-600 font-semibold underline underline-offset-4">
            Sign In
          </Link>
        </div>

        <div className="mt-6 text-center">
          <Link to="/" className="text-xs text-ink-600 hover:text-rose-500 transition-colors inline-flex items-center gap-1">
            ← Back to Homepage
          </Link>
        </div>

      </div>
    </div>
  );
}
