import './bootstrap';

function toggleLogoutModal() {
    document.getElementById('logoutModal').classList.toggle('hidden');
    document.getElementById('logoutModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
}

document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    
    toggleButton.addEventListener('click', function() {
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden');
        
        // Update aria-expanded attribute
        const isExpanded = sidebar.classList.contains('-translate-x-full') ? 'false' : 'true';
        toggleButton.setAttribute('aria-expanded', isExpanded);
    });
    
    // Close sidebar when clicking on backdrop
    backdrop.addEventListener('click', function() {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        toggleButton.setAttribute('aria-expanded', 'false');
    });
});