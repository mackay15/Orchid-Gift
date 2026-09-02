/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx,ts,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        orchid: {
          50:  '#f5f0ff',
          100: '#ede0ff',
          200: '#dcc6ff',
          300: '#c49bff',
          400: '#a966ff',
          500: '#9133ff',
          600: '#7c17f5',
          700: '#6a0dd9',
          800: '#5a189a',   // Primary brand color
          900: '#490f7a',
          950: '#2d0050',
        },
        lavender: {
          100: '#f3f0ff',
          200: '#e9e3ff',
          300: '#d6ccff',
          400: '#b8a6ff',
          500: '#9b7fff',
          600: '#7c57f5',
          700: '#6236db',
        },
        // ── Homepage light-theme palette ─────────────────────
        rose: {
          50:  '#fdf2f5',
          100: '#fce8ed',
          200: '#f9d0db',
          300: '#f4aabf',
          400: '#ec7b9b',
          500: '#B85C73',   // Primary accent
          600: '#a04f65',
          700: '#8a3f53',
          800: '#6d3041',
          900: '#52232f',
          950: '#2e1219',
        },
        blush: {
          50:  '#fdf5f7',
          100: '#fbeaee',
          200: '#f5d3da',
          300: '#edafbc',
          400: '#D9A6A6',   // Secondary accent
          500: '#c98a8a',
          600: '#b06e6e',
          700: '#935858',
        },
        cream: {
          50:  '#FFFFFF',
          100: '#F8F5F2',   // Light background
          200: '#F0EAE4',
          300: '#E8E2DE',   // Borders
          400: '#ddd5cd',
          500: '#cfc4bb',
        },
        ink: {
          900: '#2B2B2B',   // Primary text
          600: '#6B6B6B',   // Secondary text
          300: '#9B9B9B',
          100: '#c8c8c8',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Outfit', 'Inter', 'system-ui', 'sans-serif'],
        serif: ['Playfair Display', 'Georgia', 'serif'],
      },
      backgroundImage: {
        'orchid-gradient': 'linear-gradient(135deg, #5a189a 0%, #9133ff 50%, #c49bff 100%)',
        'dark-gradient':   'linear-gradient(135deg, #0d0d1a 0%, #1a0a2e 50%, #2d0050 100%)',
        'card-glass':      'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
      },
      boxShadow: {
        'orchid': '0 8px 32px rgba(90, 24, 154, 0.3)',
        'orchid-lg': '0 20px 60px rgba(90, 24, 154, 0.4)',
        'glass': '0 8px 32px rgba(0, 0, 0, 0.2)',
      },
      backdropBlur: {
        xs: '2px',
      },
      animation: {
        'fade-in':      'fadeIn 0.4s ease-in-out',
        'slide-up':     'slideUp 0.4s ease-out',
        'slide-in':     'slideIn 0.3s ease-out',
        'float':        'float 3s ease-in-out infinite',
        'pulse-orchid': 'pulseOrchid 2s ease-in-out infinite',
      },
      keyframes: {
        fadeIn:  { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
        slideUp: { '0%': { transform: 'translateY(20px)', opacity: 0 }, '100%': { transform: 'translateY(0)', opacity: 1 } },
        slideIn: { '0%': { transform: 'translateX(-20px)', opacity: 0 }, '100%': { transform: 'translateX(0)', opacity: 1 } },
        float:   { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-8px)' } },
        pulseOrchid: {
          '0%,100%': { boxShadow: '0 0 0 0 rgba(90,24,154,0.4)' },
          '50%':     { boxShadow: '0 0 0 12px rgba(90,24,154,0)' },
        },
      },
    },
  },
  plugins: [],
}
