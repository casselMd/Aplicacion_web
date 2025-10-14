/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./src/**/*.{html,ts}"],

    theme: {
    extend: {
    colors: {
      background: '#f5ebe0', // fondo general
      card: '#fff8f0', // tarjetas
      accent: '#d4a373', // botones / detalles llamativos
      success: '#81b29a', // completed
      warning: '#f2cc8f', // prepared
      danger: '#e07a5f',  // unpaid
      delivered: '#a98467', // delivered
      text: '#3e2723', // texto principal

      //Colores personalizados para la panadería
        'coffee-dark': '#4b3832', // café intenso
        'coffee-milk': '#d7ccc8', // café con leche
        'bread-gold': '#f4a261', // pan tostado
        'cream-beige': '#fefae0', // crema suave
        'green-herb': '#a7c957', // verde natural
      tabla: '#656565', //cabecera de tabla
    },
    borderRadius: {
        xl: '1rem',
        '2xl': '1.5rem',
        '3xl': '2rem',
    },
    boxShadow: {
        card: '0 6px 12px rgba(75, 56, 50, 0.25)', // sombra suave café
        glow: '0 0 10px rgba(244, 162, 97, 0.4)', // brillo cálido
    },

    fontFamily: {
        sans: ['Poppins', 'sans-serif'],
        display: ['Playfair Display', 'serif'],
    },
    }
},
    plugins: [],
 // plugins: [require('tailwind-scrollbar-hide')],
}

