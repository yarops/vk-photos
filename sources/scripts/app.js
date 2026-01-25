// Global Fancybox initialization for all templates
import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
    // Find all unique gallery IDs
    const galleries = document.querySelectorAll('[data-fancybox^="gallery-"]');
    const galleryIds = [...new Set(Array.from(galleries).map(el => el.dataset.fancybox))];

    // Initialize each gallery with Fancybox
    galleryIds.forEach(galleryId => {
        Fancybox.bind(`[data-fancybox="${galleryId}"]`, {
            Toolbar: {
                display: {
                    left: ["infobar"],
                    middle: [],
                    right: ["slideshow", "download", "thumbs", "close"]
                }
            }
        });
    });
});