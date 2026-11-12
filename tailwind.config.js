/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: { DEFAULT: '#0d9488', dark: '#0f766e', bright: '#14b8a6' },
                coral: '#ff6b57',
                ink: '#0f3538',
                cream: '#fbfaf6',
            },
            fontFamily: {
                sans: ['Vazirmatn', 'Tahoma', 'Arial', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
