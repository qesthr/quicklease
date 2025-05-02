document.addEventListener('DOMContentLoaded', function() {
    // Tab filtering functionality
    const tabs = document.querySelectorAll('.vehicle-tab');
    const cards = document.querySelectorAll('.vehicle-card');
    const showAllBtn = document.querySelector('.show-all-btn');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            const category = this.dataset.category;
            filterCards(category);
        });
    });

    // Show all functionality
    showAllBtn.addEventListener('click', function() {
        tabs.forEach(t => t.classList.remove('active'));
        tabs[0].classList.add('active'); // Activate first tab
        filterCards('all');
    });

    function filterCards(category) {
        cards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Initialize to show all cards
    filterCards('all');
});