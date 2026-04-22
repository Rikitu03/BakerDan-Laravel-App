/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
        display: ['Urbanist', 'sans-serif'],
      },
      colors: {
        primary: {
          50: '#FEF7F3',
          100: '#FDEEE7',
          200: '#FAD5C4',
          300: '#F7BCA1',
          400: '#F1895B',
          500: '#C9876C',
          600: '#B8765B',
          700: '#9F5E45',
          800: '#86472F',
          900: '#6D3019',
        },
      },
    },
  },
  plugins: [],
}
