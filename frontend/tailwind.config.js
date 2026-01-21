/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Brand Colors (Premium & Trend-Aware)
        brand: {
          primary: '#4F46E5', // Indigo
          secondary: '#7C3AED', // Violet
          accent: '#F59E0B', // Amber/Orange
          dark: '#0F172A', // Slate 900
          surface: '#1E293B', // Slate 800
          'glass-bg': 'rgba(30, 41, 59, 0.7)',
          'glass-dark': 'rgba(15, 23, 42, 0.4)',
          'glass-border': 'rgba(255, 255, 255, 0.1)',
        },
        // Primary Colors
        primary: {
          green: '#34D399',
          'green-hover': '#10B981',
          'green-light': '#D1FAE5',
          'green-bg': '#ECFDF5',
        },
        // Secondary Colors
        secondary: {
          blue: '#3B82F6',
          'blue-light': '#DBEAFE',
        },
        // Accent Colors
        accent: {
          orange: '#F59E0B',
          'orange-light': '#FEF3C7',
          purple: '#A855F7',
          'purple-light': '#F3E8FF',
          pink: '#EC4899',
          red: '#EF4444',
        },
        // Neutral Colors
        neutral: {
          white: '#FFFFFF',
          gray50: '#F9FAFB',
          gray100: '#F3F4F6',
          gray200: '#E5E7EB',
          gray300: '#D1D5DB',
          gray400: '#9CA3AF',
          gray500: '#6B7280',
          gray600: '#4B5563',
          gray700: '#374151',
          gray800: '#1F2937',
          gray900: '#111827',
        },
        // Text Colors
        text: {
          primary: '#111827',
          secondary: '#6B7280',
          tertiary: '#9CA3AF',
          'on-primary': '#FFFFFF',
          success: '#10B981',
          warning: '#F59E0B',
          error: '#EF4444',
        },
        // Border Colors
        border: {
          default: '#E5E7EB',
          light: '#F3F4F6',
          medium: '#D1D5DB',
          focus: '#34D399',
        },
      },
      fontFamily: {
        primary: ["'Inter'", '-apple-system', 'BlinkMacSystemFont', "'Segoe UI'", 'sans-serif'],
        mono: ["'JetBrains Mono'", "'Fira Code'", 'monospace'],
      },
      fontSize: {
        h1: ['24px', { lineHeight: '32px', fontWeight: '700' }],
        h2: ['20px', { lineHeight: '28px', fontWeight: '600' }],
        h3: ['16px', { lineHeight: '24px', fontWeight: '600' }],
        body: ['14px', { lineHeight: '20px', fontWeight: '400' }],
        'body-small': ['12px', { lineHeight: '16px', fontWeight: '400' }],
        caption: ['11px', { lineHeight: '14px', fontWeight: '400' }],
        button: ['14px', { lineHeight: '20px', fontWeight: '500', letterSpacing: '0.01em' }],
      },
      spacing: {
        xs: '4px',
        sm: '8px',
        md: '12px',
        lg: '16px',
        xl: '24px',
        '2xl': '32px',
        '3xl': '48px',
      },
      borderRadius: {
        none: '0px',
        sm: '4px',
        md: '8px',
        lg: '12px',
        xl: '16px',
        '2xl': '24px',
        full: '9999px',
      },
      boxShadow: {
        xs: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        sm: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
        xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
        inner: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
      },
      transitionDuration: {
        fast: '150ms',
        default: '200ms',
        slow: '300ms',
      },
      screens: {
        mobile: '320px',
        tablet: '768px',
        desktop: '1024px',
        wide: '1440px',
      },
    },
  },
  plugins: [],
}

