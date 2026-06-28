import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

if (typeof Alpine !== 'undefined') {
    Alpine.start();
}