        // Modal wiring
        const backdrop = document.getElementById('modalBackdrop');
        const modal    = document.getElementById('includeFailModal');
        const cancel   = document.getElementById('btnCancelModal');
        const tempLink = document.getElementById('tempLink');
        const tempForm = document.getElementById('tempForm');

        function openModal() {
            backdrop.style.display = 'block';
            modal.style.display = 'block';
        }
        function closeModal() {
            backdrop.style.display = 'none';
            modal.style.display = 'none';
        }
        if (cancel) cancel.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
        if (tempLink) tempLink.addEventListener('click', function (e) {
            e.preventDefault();
            // If DB is not connected but user wants to proceed quickly
            tempForm.submit();
        });

        <?php if ($show_modal_include_failed): ?>
        // Show the "include failed or unexpected" modal after a login attempt
        document.addEventListener('DOMContentLoaded', openModal);