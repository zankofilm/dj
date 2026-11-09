import './bootstrap';
import '../css/app.css';


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sortable-gallery]').forEach((gallery) => {
        let dragged = null;
        gallery.querySelectorAll('[draggable="true"]').forEach((item) => {
            item.addEventListener('dragstart', (event) => {
                dragged = item;
                item.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
            });
            item.addEventListener('dragend', () => {
                item.classList.remove('is-dragging');
                dragged = null;
            });
            item.addEventListener('dragover', (event) => {
                event.preventDefault();
                if (!dragged || dragged === item) return;
                const bounds = item.getBoundingClientRect();
                const after = event.clientY > bounds.top + bounds.height / 2;
                gallery.insertBefore(dragged, after ? item.nextSibling : item);
            });
        });

        gallery.querySelectorAll('[data-move-up]').forEach((button) => {
            button.addEventListener('click', () => {
                const item = button.closest('[data-media-id]');
                const previous = item?.previousElementSibling;
                if (previous) gallery.insertBefore(item, previous);
            });
        });
        gallery.querySelectorAll('[data-move-down]').forEach((button) => {
            button.addEventListener('click', () => {
                const item = button.closest('[data-media-id]');
                const next = item?.nextElementSibling;
                if (next) gallery.insertBefore(next, item);
            });
        });

        const form = gallery.parentElement.querySelector('[data-sort-form]');
        if (form) {
            form.addEventListener('submit', () => {
                const ids = [...gallery.querySelectorAll('[data-media-id]')].map((node) => node.dataset.mediaId);
                const holder = form.querySelector('[data-order-inputs]');
                holder.replaceChildren(...ids.map((id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'media_ids[]';
                    input.value = id;
                    return input;
                }));
            });
        }
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const mainImage = document.getElementById('main-product-image');
    if (!mainImage) return;
    document.querySelectorAll('[data-gallery-image]').forEach((button) => {
        button.addEventListener('click', () => {
            mainImage.src = button.dataset.galleryImage;
            mainImage.alt = button.dataset.galleryAlt || '';
            document.querySelectorAll('[data-gallery-image]').forEach((item) => item.setAttribute('aria-pressed', 'false'));
            button.setAttribute('aria-pressed', 'true');
        });
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const hero = document.querySelector('[data-hero-slider]');
    if (hero) {
        const slides = [...hero.querySelectorAll('.hero-slide')];
        const dots = [...hero.querySelectorAll('[data-hero-dot]')];
        let active = 0;
        let timer = null;
        const show = (index) => {
            active = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle('is-active', i === active));
            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === active);
                dot.setAttribute('aria-current', i === active ? 'true' : 'false');
            });
        };
        const stop = () => { if (timer) window.clearInterval(timer); timer = null; };
        const start = () => {
            stop();
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                timer = window.setInterval(() => show(active + 1), 6000);
            }
        };
        hero.querySelector('[data-hero-prev]')?.addEventListener('click', () => { show(active - 1); start(); });
        hero.querySelector('[data-hero-next]')?.addEventListener('click', () => { show(active + 1); start(); });
        dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.heroDot)); start(); }));
        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', start);
        hero.addEventListener('focusin', stop);
        hero.addEventListener('focusout', start);
        show(0);
        start();
    }

    document.querySelectorAll('[data-product-shelf]').forEach((shelf) => {
        const track = shelf.querySelector('.shelf-track');
        const section = shelf.closest('.product-shelf');
        const previous = section?.querySelector('[data-shelf-prev]');
        const next = section?.querySelector('[data-shelf-next]');
        if (!track) return;
        const step = () => Math.max(220, Math.round(shelf.clientWidth * 0.78));
        previous?.addEventListener('click', () => shelf.scrollBy({left: -step(), behavior: 'smooth'}));
        next?.addEventListener('click', () => shelf.scrollBy({left: step(), behavior: 'smooth'}));

        // The latest shelf deliberately drifts in both directions, reversing at either edge.
        if (shelf.dataset.motion === 'both' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            let direction = 1;
            let paused = false;
            let frame = null;
            let previousTime = 0;
            const tick = (time) => {
                if (!previousTime) previousTime = time;
                const delta = Math.min(time - previousTime, 40);
                previousTime = time;
                if (!paused) {
                    const max = Math.max(0, shelf.scrollWidth - shelf.clientWidth);
                    if (max > 0) {
                        const speed = Math.max(10, Math.min(100, Number(shelf.dataset.speed || 35))) / 35;
                        shelf.scrollLeft += direction * delta * 0.035 * speed;
                        if (shelf.scrollLeft >= max - 1) direction = -1;
                        if (shelf.scrollLeft <= 1) direction = 1;
                    }
                }
                frame = window.requestAnimationFrame(tick);
            };
            shelf.addEventListener('mouseenter', () => { paused = true; });
            shelf.addEventListener('mouseleave', () => { paused = false; });
            shelf.addEventListener('focusin', () => { paused = true; });
            shelf.addEventListener('focusout', () => { paused = false; });
            document.addEventListener('visibilitychange', () => { paused = document.hidden; });
            frame = window.requestAnimationFrame(tick);
            window.addEventListener('pagehide', () => { if (frame) window.cancelAnimationFrame(frame); }, {once:true});
        }
    });
});
