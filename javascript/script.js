document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded and parsed.');

    // Notification functionality
    function toggleNotifications() {
        console.log('Bell icon clicked.');
        const dropdown = document.getElementById('notificationDropdown');
        if (!dropdown) {
            console.error('Notification dropdown not found.');
            return;
        }

        // Toggle dropdown visibility
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';

        // Fetch notifications only when the dropdown is opened
        if (dropdown.style.display === 'block') {
            console.log('Fetching notifications...');
            fetchNotifications();
        }
    }

    function fetchNotifications() {
        fetch('../dashboard/fetch_notification.php')
            .then(response => response.json())
            .then(data => {
                console.log('Notifications fetched:', data);
                if (data.success) {
                    const notificationList = document.getElementById('notificationList');
                    if (!notificationList) {
                        console.error('Notification list element not found.');
                        return;
                    }

                    notificationList.innerHTML = ''; // Clear existing notifications

                    // Populate notifications
                    data.data.forEach(notification => {
                        const li = document.createElement('li');
                        li.textContent = `${notification.message} (Received: ${formatDate(notification.created_at)})`;
                        if (!notification.is_read) {
                            li.classList.add('unread');
                        }
                        notificationList.appendChild(li);
                    });
                } else {
                    console.error('Error fetching notifications:', data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    // Attach toggleNotifications to the bell icon
    const bellIcon = document.querySelector('.bell');
    if (bellIcon) {
        console.log('Bell icon found. Adding event listener.');
        bellIcon.addEventListener('click', toggleNotifications);
    } else {
        console.error('Bell icon not found.');
    }
});

