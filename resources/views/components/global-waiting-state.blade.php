<script>
    (() => {
        if (window.__globalWaitingStateInstalled) {
            return;
        }

        window.__globalWaitingStateInstalled = true;
        let interactionButton = null;
        const originalFetch = window.fetch.bind(window);

        const startWaiting = (button) => {
            if (!button || button.dataset.disableWaiting === 'true' || button.dataset.waitingActive === 'true') {
                return;
            }

            button.dataset.waitingActive = 'true';
            button.dataset.waitingHtml = button.innerHTML;
            button.dataset.waitingDisabled = button.disabled ? 'true' : 'false';
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.classList.add('cursor-wait', 'opacity-75');
            button.innerHTML = `
                <span class="inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6h3Z"></path>
                    </svg>
                    <span>${button.dataset.waitingLabel || 'Waiting…'}</span>
                </span>
            `;
        };

        const stopWaiting = (button) => {
            if (!button || button.dataset.waitingActive !== 'true') {
                return;
            }

            button.innerHTML = button.dataset.waitingHtml;
            button.disabled = button.dataset.waitingDisabled === 'true';
            button.removeAttribute('aria-busy');
            button.classList.remove('cursor-wait', 'opacity-75');
            delete button.dataset.waitingActive;
            delete button.dataset.waitingHtml;
            delete button.dataset.waitingDisabled;
        };

        document.addEventListener('click', (event) => {
            interactionButton = event.target.closest('button, [role="button"]');
            queueMicrotask(() => {
                interactionButton = null;
            });
        }, true);

        document.addEventListener('submit', (event) => {
            const button = event.submitter || event.target.querySelector('button[type="submit"]');
            startWaiting(button);
        }, true);

        window.fetch = (...args) => {
            const button = interactionButton;

            if (button) {
                startWaiting(button);
            }

            return originalFetch(...args).finally(() => {
                stopWaiting(button);
            });
        };
    })();
</script>
