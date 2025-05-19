document.addEventListener('DOMContentLoaded', function() {
    // Toggle category visibility for admin view
    document.querySelectorAll('.toggle-category').forEach(button => {
        button.addEventListener('click', function() {
            const section = this.closest('.category-section');
            const content = section.querySelector('.category-content');
            content.classList.toggle('hidden');
            
            if (content.classList.contains('hidden')) {
                this.textContent = 'Show';
            } else {
                this.textContent = 'Hide';
            }
        });
    });
}); 