import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.Swal = Swal;

/**
 * Shared SweetAlert confirm dialog, styled to match the panel.
 */
window.yunoConfirm = (opts = {}) =>
    Swal.fire({
        title: opts.title || 'Are you sure?',
        text: opts.text || '',
        icon: opts.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: opts.confirmButtonText || 'Yes',
        cancelButtonText: opts.cancelButtonText || 'Cancel',
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

/**
 * Small corner toast for success/error feedback.
 */
window.yunoToast = (title, icon = 'success') =>
    Swal.fire({
        toast: true,
        position: 'top-end',
        timer: 3500,
        timerProgressBar: true,
        showConfirmButton: false,
        icon,
        title,
    });

// Any <form data-confirm="…"> asks for confirmation via SweetAlert before it
// submits, replacing the browser's native confirm() dialog everywhere.
document.addEventListener(
    'submit',
    (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) return;

        e.preventDefault();
        window
            .yunoConfirm({
                title: form.dataset.confirmTitle || 'Are you sure?',
                text: form.dataset.confirm,
                icon: form.dataset.confirmIcon || 'warning',
                confirmButtonText: form.dataset.confirmButton || 'Yes',
            })
            .then((result) => {
                // form.submit() bypasses this listener, so there is no loop.
                if (result.isConfirmed) form.submit();
            });
    },
    true,
);

// Live server status badge: polls a server's node daemon (its stats endpoint)
// and reflects the real container state instead of the stored DB column.
window.serverStatus = (url) => ({
    state: 'loading',
    labels: { running: 'Running', exited: 'Offline', created: 'Installed (stopped)', restarting: 'Restarting', missing: 'Not installed', loading: 'Loading…', unreachable: 'Node unreachable' },
    get label() {
        return this.labels[this.state] || (this.state ? this.state.charAt(0).toUpperCase() + this.state.slice(1) : 'Unknown');
    },
    get dot() {
        if (this.state === 'running') return 'bg-green-500';
        if (this.state === 'unreachable') return 'bg-red-500';
        if (this.state === 'restarting' || this.state === 'loading') return 'bg-amber-400';
        return 'bg-gray-400';
    },
    get badge() {
        if (this.state === 'running') return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
        if (this.state === 'unreachable') return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
        if (this.state === 'restarting') return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
        return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
    },
    start() {
        this.load();
        setInterval(() => this.load(), 8000);
    },
    async load() {
        try {
            const r = await (await fetch(url)).json();
            this.state = r.state || 'unreachable';
        } catch (e) {
            this.state = 'unreachable';
        }
    },
});

// Live "running" count: polls every server's state and counts the running ones.
window.runningCount = (urls) => ({
    running: 0,
    start() {
        this.load();
        setInterval(() => this.load(), 8000);
    },
    async load() {
        let count = 0;
        await Promise.all((urls || []).map(async (u) => {
            try {
                const r = await (await fetch(u)).json();
                if (r.state === 'running') count++;
            } catch (e) { /* unreachable -> not counted */ }
        }));
        this.running = count;
    },
});

Alpine.start();
