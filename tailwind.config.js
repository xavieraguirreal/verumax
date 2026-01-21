/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./certificatum/**/*.php",
    "./sajur/**/*.php",
    "./liberte/**/*.php",
    "./fotosjuan/**/*.php",
    "./includes/**/*.php",
    "./admin/**/*.php",
    "./assets/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'gold': {
          DEFAULT: '#D4AF37',
          light: '#F0D377',
          dark: '#B8941E'
        },
        'metallic-green': {
          DEFAULT: '#2E7D32',
          light: '#4CAF50',
          dark: '#1B5E20'
        },
        'metallic-red': {
          DEFAULT: '#C62828',
          light: '#E53935',
          dark: '#8E0000'
        }
      },
      fontFamily: {
        'sans': ['Inter', 'system-ui', '-apple-system', 'sans-serif']
      }
    }
  },
  plugins: []
}
