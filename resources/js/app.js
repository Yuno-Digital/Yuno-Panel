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

Alpine.start();
