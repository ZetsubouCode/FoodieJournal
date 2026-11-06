export function initializeImageFallbacks(scope = document) {
    scope.querySelectorAll('img.media-image').forEach(image => {
        if (image.closest('[data-visit-card]') || image.dataset.fallbackBound) return;
        image.dataset.fallbackBound = 'true';
        const fail = () => {
            if (!image.isConnected) return;
            const fallback = document.createElement('span');
            fallback.className = image.className + ' photo-fallback';
            fallback.textContent = 'Foto belum dapat dimuat';
            image.replaceWith(fallback);
        };
        image.addEventListener('error', fail, { once: true });
        if (image.complete && !image.naturalWidth) fail();
    });
}
