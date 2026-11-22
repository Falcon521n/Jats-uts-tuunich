document.querySelectorAll('[data-catalog-filter]').forEach((form) => {
    form.querySelectorAll('select, input[type="checkbox"]').forEach((control) => {
        control.addEventListener('change', () => form.requestSubmit());
    });
});

document.querySelectorAll('[data-product-carousel]').forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
    if (slides.length < 2) {
        return;
    }

    const status = carousel.querySelector('[data-carousel-status]');
    const toggle = carousel.querySelector('[data-carousel-toggle]');
    let activeIndex = 0;
    let timer = null;
    let paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const updateSlides = () => {
        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === activeIndex;
            slide.hidden = false;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
            slide.inert = !isActive;
        });
    };

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;
        updateSlides();
        status.textContent = `${activeIndex + 1} / ${slides.length}`;
    };

    updateSlides();
    const stop = () => {
        window.clearInterval(timer);
        timer = null;
    };

    const start = () => {
        stop();
        if (!paused) {
            timer = window.setInterval(() => showSlide(activeIndex + 1), 5000);
        }
    };

    carousel.querySelector('[data-carousel-previous]').addEventListener('click', () => {
        showSlide(activeIndex - 1);
        start();
    });
    carousel.querySelector('[data-carousel-next]').addEventListener('click', () => {
        showSlide(activeIndex + 1);
        start();
    });
    toggle.addEventListener('click', () => {
        paused = !paused;
        toggle.textContent = paused ? 'Reanudar' : 'Pausar';
        toggle.setAttribute('aria-pressed', String(paused));
        start();
    });

    toggle.textContent = paused ? 'Reanudar' : 'Pausar';
    toggle.setAttribute('aria-pressed', String(paused));
    start();
});
