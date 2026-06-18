/** @type {import('tailwindcss').Config} */
module.exports = {

  // OBLIGATOIRE : liste des fichiers où Tailwind cherche les classes utilisées
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
  ],

  theme: {
    extend: {
      // Couleurs personnalisées du projet
      colors: {
        'color-primary': '#1E40AF',
        'color-accent':  '#F97316',
        'color-dark':    '#0F172A',
      },
      // Police principale
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
      },
    },
  },

  plugins: [],

}