function filterPermissions(searchTerm) {
    const permissionItems = document.querySelectorAll('.permission-item');
    const categories = document.querySelectorAll('.permission-category');
    const searchLower = searchTerm.toLowerCase();

    categories.forEach(category => {
        let hasVisibleItems = false;
        const items = category.querySelectorAll('.permission-item');

        items.forEach(item => {
            const text = item.dataset.permission.toLowerCase();
            const matches = text.includes(searchLower);
            item.style.display = matches ? 'block' : 'none';
            if (matches) hasVisibleItems = true;
        });

        category.style.display = hasVisibleItems ? 'block' : 'none';
    });
}

function toggleCategory(category) {
    const container = document.querySelector(`[data-category="${category}"]`);
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = anyUnchecked;
    });
}

function selectAllPermissions() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllPermissions() {
    document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    tippy('[data-tippy-content]', {
        placement: 'top',
        arrow: true,
        theme: 'light-border'
    });
});
