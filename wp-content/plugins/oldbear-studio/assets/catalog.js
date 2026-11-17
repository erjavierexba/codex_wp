(() => {
    const init = (scope = document) => scope.querySelectorAll('[data-ob-catalog]').forEach(root => {
        if (root.dataset.ready) return;
        root.dataset.ready = '1';
        const form = root.querySelector('form'), results = root.querySelector('.ob-results'), status = root.querySelector('[role="status"]');
        let controller, requestId = 0;
        const load = async (page = 1) => {
            controller?.abort();
            controller = new AbortController();
            const current = ++requestId;
            const url = new URL(root.dataset.endpoint, location.href);
            url.searchParams.set('page', page);
            url.searchParams.set('per_page', root.dataset.perPage || '6');
            if (form) for (const [key, value] of new FormData(form)) url.searchParams.set(key.replace(/^ob_/, ''), value);
            results.setAttribute('aria-busy', 'true');
            status.textContent = 'Buscando mundos…';
            try {
                const response = await fetch(url, {signal: controller.signal, credentials: 'same-origin', headers: {'Accept': 'application/json'}});
                if (!response.ok) throw new Error('request');
                const data = await response.json();
                if (current !== requestId) return;
                results.innerHTML = data.html;
                status.textContent = data.total + ' juegos encontrados';
            } catch (error) {
                if (error.name !== 'AbortError' && current === requestId) status.textContent = 'No se pudo actualizar el catálogo. Conservamos los resultados anteriores. Vuelve a intentarlo.';
            } finally {
                if (current === requestId) results.setAttribute('aria-busy', 'false');
            }
        };
        form?.addEventListener('submit', event => { event.preventDefault(); load(); });
        form?.addEventListener('change', event => { if (event.target.matches('select')) load(); });
        form?.addEventListener('reset', () => queueMicrotask(() => { for (const input of form.querySelectorAll('input,select')) input.value = ''; load(); }));
        results.addEventListener('click', event => {
            const link = event.target.closest('[data-ob-page]');
            if (link) { event.preventDefault(); load(Number(link.dataset.obPage)); }
        });
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init()); else init();
    window.addEventListener('elementor/frontend/init', () => {
        window.elementorFrontend?.hooks.addAction('frontend/element_ready/oldbear-catalog.default', scope => init(scope[0]));
    });
})();
