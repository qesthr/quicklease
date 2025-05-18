document.addEventListener('DOMContentLoaded', function() {
    // ======================
    // Vehicle Filtering System
    // ======================
    const tabs = document.querySelectorAll('.vehicle-tab');
    const cards = document.querySelectorAll('.vehicle-card');
    const showAllBtn = document.querySelector('.show-all-btn');
    const vehicleCount = document.querySelector('.vehicle-count');

    // Initialize filter system
    function initFilterSystem() {
        // Set click handlers for tabs
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Filter cards
                const category = this.dataset.category;
                filterCards(category);
                updateVehicleCount(category);
            });
        });

        // Show all button
        showAllBtn.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            tabs[0].classList.add('active');
            filterCards('all');
            updateVehicleCount('all');
        });

        // Initial filter (show all)
        filterCards('all');
        if (vehicleCount) updateVehicleCount('all');
    }

    // Filter cards by category
    function filterCards(category) {
        let visibleCount = 0;
        
        cards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Update show all button count if needed
        if (category === 'all' && showAllBtn) {
            showAllBtn.textContent = `Show all (${cards.length} models) →`;
        }
    }

    // Update vehicle count display
    function updateVehicleCount(category) {
        if (!vehicleCount) return;
        
        const count = category === 'all' 
            ? cards.length 
            : document.querySelectorAll(`.vehicle-card[data-category="${category}"]`).length;
        
        vehicleCount.textContent = `${count} vehicles available`;
    }

    // ======================
    // Mobile Menu Toggle
    // ======================
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navbarNav = document.querySelector('.navbar-nav');

    if (mobileMenuBtn && navbarNav) {
        mobileMenuBtn.addEventListener('click', function() {
            navbarNav.classList.toggle('active');
            this.classList.toggle('active');
        });
    }

    // ======================
    // Search Form Validation
    // ======================
    const searchForm = document.querySelector('.search-form');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const locationSelect = this.querySelector('select');
            const startDate = this.querySelector('input[type="date"]:first-of-type');
            const endDate = this.querySelector('input[type="date"]:last-of-type');
            
            // Simple validation
            if (locationSelect.value === 'Choose Location') {
                alert('Please select a location');
                e.preventDefault();
                return;
            }
            
            if (!startDate.value || !endDate.value) {
                alert('Please select both dates');
                e.preventDefault();
                return;
            }
            
            if (new Date(endDate.value) < new Date(startDate.value)) {
                alert('Return date must be after departure date');
                e.preventDefault();
            }
        });
    }

    // ======================
    // Smooth Scrolling
    // ======================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ======================
    // Initialize Systems
    // ======================
    initFilterSystem();
});