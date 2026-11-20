/* Same-origin CSRF support and explicit POST actions for the legacy UI. */
(() => {
    'use strict';
    const token = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    window.escapeText = value => String(value ?? '').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character]));
    window.safeLink = value => {
        try { const url = new URL(value || '#', location.href); return ['http:', 'https:'].includes(url.protocol) ? url.href : '#'; }
        catch { return '#'; }
    };
    const actionPaths = /\/(?:giang_vien\/(?:xoa_de_tai|gui_duyet|xu_ly_duyet|xu_ly_xoa_dang_ky|xoa_dang_ky_gv)|lanh_dao\/xu_ly_duyet|sinh_vien\/dang_ky_de_tai|auth\/logout)\.php$/;
    const isAction = url => actionPaths.test(url.pathname) ||
        (/\/thong_bao\.php$/.test(url.pathname) && ['mark_read', 'mark_all_read'].includes(url.searchParams.get('action')));
    window.secureNavigate = function (target) {
        const url = new URL(target, location.href);
        if (url.origin !== location.origin || !isAction(url)) { location.href = url.href; return; }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url.pathname;
        for (const [name, value] of [...url.searchParams, ['csrf_token', token()]]) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = name; input.value = value;
            form.append(input);
        }
        document.body.append(form);
        form.submit();
    };
    document.addEventListener('click', event => {
        const anchor = event.target.closest('a[href]');
        if (!anchor || event.defaultPrevented || event.button !== 0) return;
        const url = new URL(anchor.href, location.href);
        if (url.origin === location.origin && isAction(url)) {
            event.preventDefault(); window.secureNavigate(url.href);
        }
    });
    const originalFetch = window.fetch.bind(window);
    window.fetch = (input, options = {}) => {
        const url = new URL(input instanceof Request ? input.url : input, location.href);
        if (url.origin !== location.origin) return originalFetch(input, options);
        const next = { ...options, headers: new Headers(options.headers || (input instanceof Request ? input.headers : {})) };
        let method = (next.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
        if (method === 'GET' && isAction(url)) {
            next.method = method = 'POST';
            next.body = new URLSearchParams(url.searchParams);
            url.search = '';
            input = url.href;
        }
        if (!['GET', 'HEAD'].includes(method)) next.headers.set('X-CSRF-Token', token());
        return originalFetch(input, next);
    };
    const open = XMLHttpRequest.prototype.open;
    const send = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url, ...args) {
        this.securityUrl = new URL(url, location.href);
        this.securityMethod = method.toUpperCase();
        return open.call(this, method, url, ...args);
    };
    XMLHttpRequest.prototype.send = function (body) {
        if (this.securityUrl?.origin === location.origin && !['GET', 'HEAD'].includes(this.securityMethod)) {
            this.setRequestHeader('X-CSRF-Token', token());
        }
        return send.call(this, body);
    };
})();
