(() => {
    document.body.classList.add('followed-page');
    const navbar = document.querySelector('.navbar.sticky-top');
    if (!navbar) return;
    const updateOffset = () => {
        const height = Math.ceil(navbar.getBoundingClientRect().height);
        document.documentElement.style.setProperty('--followed-navbar-height', `${height}px`);
        document.body.style.paddingTop = `${height}px`;
    };
    updateOffset();
    if ('ResizeObserver' in window) new ResizeObserver(updateOffset).observe(navbar);
    window.addEventListener('resize', updateOffset);
})();
