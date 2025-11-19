    /** @type {import('tailwindcss').Config} */
    module.exports = {
    content: [
        "./src/**/*.{html,ts}",
        "./node_modules/@ionic/**/*.{js,ts}"
    ],
    theme: {
        extend: {
        colors: {
            // 🎨 Paleta cálida principal
            background: '#fdf6f0',
            card: '#fff8f2',
            accent: '#d4a373',
            text: '#3e2723',

            // 🔸 Estados
            success: '#81b29a',
            warning: '#f2cc8f',
            danger: '#e07a5f',
            delivered: '#a98467',
            tabla: '#6d4c41',

            // ☕ Tonos personalizados
            'coffee-dark': '#4b3832',
            'coffee-milk': '#cbb89d',
            'bread-gold': '#f4a261',
            'cream-beige': '#fefae0',
            'green-herb': '#a7c957',

            // 🌙 Navbar
            'nav-dark': '#2f3e46',
            'nav-text': '#ffffff',
        },

        borderRadius: {
            xl: '1rem',
            '2xl': '1.5rem',
            '3xl': '2rem',
        },

        boxShadow: {
            card: '0 6px 12px rgba(75, 56, 50, 0.25)',
            glow: '0 0 12px rgba(212, 163, 115, 0.5)',
        },

        fontFamily: {
            sans: ['Poppins', 'sans-serif'],
            display: ['Playfair Display', 'serif'],
        },
        },
    },
    plugins: [],
    };
