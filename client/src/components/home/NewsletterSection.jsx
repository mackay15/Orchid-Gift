import React, { useState } from 'react';

const MailIcon = () => (
  <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#B85C73" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
    <polyline points="22,6 12,13 2,6"/>
  </svg>
);

export default function NewsletterSection() {
  const [email, setEmail] = useState('');
  const [subscribed, setSubscribed] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (email.trim()) {
      setSubscribed(true);
    }
  };

  return (
    <section className="py-16 bg-white" aria-labelledby="newsletter-heading">
      <div className="max-w-[1400px] mx-auto px-6">
        <div className="home-newsletter">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">

            {/* Left – Icon */}
            <div className="flex justify-center lg:justify-end">
              <div className="relative">
                <div className="w-32 h-32 rounded-full flex items-center justify-center"
                  style={{ background: 'rgba(184,92,115,0.12)' }}>
                  <MailIcon />
                </div>
                {/* Decorative rings */}
                <div className="absolute inset-0 rounded-full border border-rose-200 scale-125 opacity-50" aria-hidden="true" />
                <div className="absolute inset-0 rounded-full border border-rose-200 scale-150 opacity-25" aria-hidden="true" />
              </div>
            </div>

            {/* Right – Content */}
            <div>
              <p className="text-rose-500 font-semibold text-xs tracking-[0.2em] uppercase mb-3">
                Stay Connected
              </p>
              <h2 id="newsletter-heading"
                style={{ fontFamily: "'Playfair Display', Georgia, serif" }}
                className="text-3xl font-bold text-ink-900 mb-3">
                Join Our Newsletter
              </h2>
              <p className="text-ink-600 text-sm leading-relaxed mb-6 max-w-md">
                Get exclusive offers, new arrivals, and special updates delivered directly to your inbox. No spam, ever.
              </p>

              {subscribed ? (
                <div className="flex items-center gap-3 p-4 rounded-xl bg-white border border-rose-200">
                  <div className="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-500 text-lg">✓</div>
                  <div>
                    <p className="font-semibold text-ink-900 text-sm">You're subscribed!</p>
                    <p className="text-xs text-ink-600">Thanks for joining — check your inbox.</p>
                  </div>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className="flex flex-col sm:flex-row gap-3">
                  <input
                    id="newsletter-email"
                    type="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Enter your email address"
                    className="flex-1 px-4 py-3.5 rounded-lg border border-cream-300 bg-white text-ink-900 placeholder-ink-300 text-sm
                               focus:outline-none focus:ring-2 focus:ring-rose-300 focus:border-rose-400 transition-all"
                    aria-label="Email address for newsletter"
                  />
                  <button type="submit" id="newsletter-subscribe-btn" className="home-btn-primary shrink-0 text-sm py-3.5 px-6">
                    SUBSCRIBE
                  </button>
                </form>
              )}

              <p className="text-xs text-ink-600 mt-3 flex items-center gap-1.5">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                We respect your privacy. Unsubscribe anytime.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
