// Tab menu
document.addEventListener('DOMContentLoaded', function () {
    const tabLinks = document.querySelectorAll('.tab-link');

    tabLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Remove active class from all tabs
            tabLinks.forEach(btn => btn.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');

            // Optional: Switch content tabs if needed
            const selectedTab = this.dataset.tab;
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = tab.id === selectedTab ? 'block' : 'none';
            });
        });
    });
});

// Edit icon
document.addEventListener('DOMContentLoaded', function () {
    // Edit icon modal logic
    const editBtn = document.querySelector('.edit-icon-button');
    const modal = document.getElementById('editProfileModal');
    const closeBtn = document.getElementById('closeEditModal');
    const form = document.getElementById('editProfileForm');

    // Prefill form with current values
    function prefillForm() {
        document.getElementById('editFirstName').value = document.querySelector('.info-item label[for="firstname"]')?.nextElementSibling.textContent.trim() || '';
        document.getElementById('editLastName').value = document.querySelector('.info-item label[for="lastname"]')?.nextElementSibling.textContent.trim() || '';
        document.getElementById('editEmail').value = document.querySelector('.info-item label[for="email"]')?.nextElementSibling.textContent.trim() || '';
        document.getElementById('editPhone').value = document.querySelector('.info-item label[for="phone"]')?.nextElementSibling.textContent.trim() || '';
    }

    editBtn.addEventListener('click', function () {
        prefillForm();
        modal.style.display = 'block';
    });

    closeBtn.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        console.log('Form submission started');

        const formData = new FormData(this);
        
        // Debug: Log all form fields and their values
        console.log('Form fields:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // Get all input values
        const firstname = document.getElementById('editFirstName').value.trim();
        const lastname = document.getElementById('editLastName').value.trim();
        const email = document.getElementById('editEmail').value.trim();
        const phone = document.getElementById('editPhone').value.trim();

        console.log('Input values:', {
            firstname,
            lastname,
            email,
            phone
        });

        // Check if any field is empty
        if (!firstname || !lastname || !email || !phone) {
            console.log('Validation failed:', {
                firstname: !firstname,
                lastname: !lastname,
                email: !email,
                phone: !phone
            });
            alert('Please fill in all required fields.');
            return;
        }

        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';

        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update profile.');
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the profile.');
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });
});

// ID verification
document.addEventListener("DOMContentLoaded", function () {
    const verifyBtn = document.getElementById("verifyBtn");
    const statusText = document.getElementById("statusText");

    if (verifyBtn && statusText) {
        verifyBtn.addEventListener("click", function () {
            const isVerified = statusText.classList.contains("verified");

            if (isVerified) {
                statusText.textContent = "Not Verified";
                statusText.classList.remove("verified");
                statusText.classList.add("not-verified");
                verifyBtn.textContent = "Mark as Verified";
            } else {
                statusText.textContent = "Verified";
                statusText.classList.remove("not-verified");
                statusText.classList.add("verified");
                verifyBtn.textContent = "Mark as Not Verified";
            }

            // Optionally send to server via AJAX here
        });
    }

    const idImageInput = document.getElementById("idImage");
    const idPreview = document.getElementById("idPreview");
    const saveIdBtn = document.getElementById("saveIdBtn");
    const idUploadForm = document.getElementById("idUploadForm");

    if (idImageInput && idPreview && saveIdBtn && idUploadForm) {
        idImageInput.addEventListener("change", function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    idPreview.src = e.target.result;
                    idPreview.classList.remove("hidden");
                };
                reader.readAsDataURL(file);
            }
        });

        idUploadForm.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const file = idImageInput.files[0];
            if (!file) {
                alert("Please select an ID image first.");
                return;
            }

            const formData = new FormData(this);

            // Show loading state
            saveIdBtn.disabled = true;
            saveIdBtn.textContent = "Saving...";

            fetch("update_id.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("ID uploaded successfully!");
                    window.location.reload();
                } else {
                    alert(data.message || "Failed to upload ID.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while uploading the ID.");
            })
            .finally(() => {
                saveIdBtn.disabled = false;
                saveIdBtn.textContent = "Save ID";
            });
        });
    }
});

// Notifications 
document.addEventListener('DOMContentLoaded', function() {
    // Function to add new notification
    function addNotification(name, message, bookingDates, date) {
      const container = document.querySelector('.notifications-container');
      const newDate = date || new Date();
      const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                         "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
      
      const notification = document.createElement('div');
      notification.className = 'notification new';
      
      notification.innerHTML = `
        <div class="notification-card">
          <div class="notification-header">
            <p class="notification-greeting">Hi, ${name}!</p>
          </div>
          <div class="notification-body">
            <p class="notification-message">
              ${message}
            </p>
          </div>
          <div class="notification-footer">
            <button class="view-booking-btn">View Booking</button>
          </div>
        </div>
        <div class="date-indicator">
          <div class="notification-dot"></div>
          <div class="date-stack">
            <span class="date-day">${newDate.getDate()}</span>
            <span class="date-month-year">${monthNames[newDate.getMonth()]} ${newDate.getFullYear()}</span>
          </div>
        </div>
      `;
      
      container.insertBefore(notification, container.firstChild);
      
      // Remove 'new' class after 5 seconds
      setTimeout(() => {
        notification.classList.remove('new');
      }, 5000);
    }
    
    // Example usage:
    // addNotification(
    //   "John Doe",
    //   "Good news! Your booking for Toyota Camry from April-28-2025 to April-29-2025 has been approved by our admin.",
    //   new Date()
    // );
  });



