/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        navy: {
          DEFAULT: '#031833',
          50: '#F0F4F8',
          100: '#D9E2EC',
          200: '#BCCCDC',
          800: '#07244C',
          900: '#031833',
          950: '#020F20',
        },
        accent: {
          DEFAULT: '#FDF506',
          hover: '#E5DC05',
        },
        neutral: {
          surface: '#F8FAFC',
          card: '#FFFFFF',
          border: '#E2E8F0',
          text: '#1E293B',
          muted: '#64748B',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
