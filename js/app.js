/**
 * Shared JS helpers (loaded on every page).
 */
const App = {
    url(path) {
        return window.APP.baseUrl.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
    },

    csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    },

    async post(path, data) {
        const res = await fetch(App.url(path), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': App.csrf() },
            body: JSON.stringify(data),
            credentials: 'same-origin',
        });
        let body = {};
        try { body = await res.json(); } catch (_) { /* non-JSON error */ }
        return { ok: res.ok, status: res.status, body };
    },

    peso(amount) {
        return '₱' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    /**
     * Wire up a simple chat box that talks to api/ai.php.
     * Messages are inserted with textContent (never innerHTML) so AI output can't inject HTML.
     */
    chat({ form, input, log, mode }) {
        const formEl = document.querySelector(form);
        const inputEl = document.querySelector(input);
        const logEl = document.querySelector(log);
        if (!formEl || !inputEl || !logEl) return;

        const add = (text, who) => {
            const div = document.createElement('div');
            div.className = 'chat-msg chat-' + who;
            div.textContent = text;
            logEl.appendChild(div);
            logEl.scrollTop = logEl.scrollHeight;
            return div;
        };

        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = inputEl.value.trim();
            if (!message) return;

            add(message, 'user');
            inputEl.value = '';
            inputEl.disabled = true;
            const thinking = add('Thinking…', 'bot');

            try {
                const { body } = await App.post('api/ai.php', { message, mode });
                thinking.textContent = body.reply || body.error || 'Something went wrong.';
            } catch (_) {
                thinking.textContent = 'Could not reach the assistant.';
            } finally {
                inputEl.disabled = false;
                inputEl.focus();
            }
        });
    },
};

