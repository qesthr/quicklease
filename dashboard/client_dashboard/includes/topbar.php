<header class="topbar">
    <h1>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $page_titles = [
            'client_profile_userdetails.php' => 'Profile',
            'client_cars.php' => 'Car Catalogue',
            'client_booking.php' => 'Bookings',
        ];
        echo $page_titles[$current_page] ?? 'Dashboard';
        ?>
    </h1>

    <div class="user-info">
        <div class="notification">
            <i class="fa-regular fa-bell"></i>
        </div>

        <img class="profile-pic"
             src="<?php
                if (isset($user) && is_array($user) && !empty($user['profile_picture'])) {
                    echo '../../uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']);
                } else {
                    echo '../images/profile.jpg';
                }
             ?>"
             alt="Profile Picture">
 
        <div class="user-details">
            <p>Welcome, 
                <strong>
                    <?php 
                    if (isset($user) && is_array($user) && isset($user['firstname'])) {
                        echo htmlspecialchars($user['firstname']);
                    } else {
                        echo "Client";
                    }
                    ?>
                </strong>
            </p>
        </div>
    </div>
</header>

<style>
.search-container {
    margin: 20px 0;
    width: 100%;
    max-width: 600px;
}

.search-form {
    width: 100%;
}

.search-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 15px;
    color: #666;
}

.enhanced-search {
    width: 100%;
    padding: 12px 40px 12px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
}

.enhanced-search:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    outline: none;
}

.clear-search {
    position: absolute;
    right: 15px;
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.clear-search:hover {
    color: #333;
    transform: scale(1.1);
}

.quick-filters {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.filter-chip {
    padding: 6px 12px;
    background: #f0f2f5;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    color: #444;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-chip:hover {
    background: #e8e8e8;
}

.filter-chip.active {
    background: #4CAF50;
    color: white;
    border-color: #4CAF50;
}

@media (max-width: 768px) {
    .search-container {
        max-width: 100%;
    }
    
    .quick-filters {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterChips = document.querySelectorAll('.filter-chip');
    const searchInput = document.querySelector('.enhanced-search');
    
    filterChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            
            // Toggle active state
            this.classList.toggle('active');
            
            // Update search input
            let currentSearch = searchInput.value.toLowerCase();
            if (this.classList.contains('active')) {
                // Add filter to search if not already present
                if (!currentSearch.includes(filterValue.toLowerCase())) {
                    searchInput.value = currentSearch ? `${currentSearch} ${filterValue}` : filterValue;
                }
            } else {
                // Remove filter from search
                searchInput.value = currentSearch.replace(new RegExp(filterValue, 'gi'), '').trim();
            }
            
            // Trigger form submission
            searchInput.form.submit();
        });
    });
});
</script>