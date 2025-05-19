document.addEventListener('DOMContentLoaded', function() {
    // Handle view booking button clicks
    document.querySelectorAll('.view-booking-btn').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            if (bookingId) {
                window.location.href = `client_booking.php?booking_id=${bookingId}`;
            }
        });
    });

    // Mark notification as read when clicked
    document.querySelectorAll('.notification-card').forEach(card => {
        card.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            if (notificationId && this.classList.contains('unread')) {
                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notification_id: notificationId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('unread');
                        
                        // Update notification badge count
                        const badge = document.querySelector('.notification-badge');
                        if (badge) {
                            const currentCount = parseInt(badge.textContent);
                            if (currentCount > 1) {
                                badge.textContent = currentCount - 1;
                            } else {
                                badge.remove();
                            }
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });
}); 