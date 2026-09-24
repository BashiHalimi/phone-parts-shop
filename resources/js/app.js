import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Prevent double-submit on any form
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form.tagName !== 'FORM') return;

    const button = form.querySelector('button[type="submit"]');
    if (!button) return;

    if (button.disabled) {
        e.preventDefault();
        return;
    }

    setTimeout(() => {
        button.disabled = true;
        button.classList.add('opacity-60', 'cursor-not-allowed');
        const original = button.textContent.trim();
        button.textContent = 'Please wait…';

        setTimeout(() => {
            button.disabled = false;
            button.classList.remove('opacity-60', 'cursor-not-allowed');
            button.textContent = original;
        }, 5000);
    }, 10);
});