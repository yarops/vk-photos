// Fresh template entry point
import '@/styles/fresh.scss';
import Macy from 'macy';

// Initialize Macy.js for masonry layout
document.addEventListener('DOMContentLoaded', () => {
    const masonryContainers = document.querySelectorAll('.mosaicflow');

    masonryContainers.forEach(container => {
        const macy = Macy({
            container,
            trueOrder: false,
            waitForImages: true,
            margin: 10,
            columns: 3,
            breakAt: {
                1200: 3,
                940: 2,
                520: 1
            }
        });

        // Recalculate on window resize
        window.addEventListener('resize', () => {
            macy.recalculate();
        });

        // Recalculate when images are loaded
        const images = container.querySelectorAll('img');
        images.forEach(img => {
            if (img.complete) {
                macy.recalculate();
            } else {
                img.addEventListener('load', () => {
                    macy.recalculate();
                });
            }
        });
    });
});
