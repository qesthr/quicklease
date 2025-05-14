// Notification handling functions
class NotificationManager {
    constructor(userId, userRole) {
        this.userId = userId;
        this.userRole = userRole;
        this.notificationContainer = document.querySelector('.notification-container');
        this.notificationList = document.querySelector('.notification-list');
        this.notificationBell = document.querySelector('.notification-bell');
        this.unreadBadge = document.querySelector('.unread-badge');
        this.page = 0;
        this.loading = false;
        this.hasMore = true;
        this.ws = null;
        
        this.initializeNotifications();
        this.initializeWebSocket();
    }

    // Initialize WebSocket connection
    initializeWebSocket() {
        this.ws = new WebSocket('ws://localhost:8080');

        this.ws.onopen = () => {
            console.log('WebSocket connected');
            // Authenticate the connection
            this.ws.send(JSON.stringify({
                type: 'auth',
                userId: this.userId,
                role: this.userRole
            }));
        };

        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.type === 'notification') {
                // Add new notification to the list
                this.handleNewNotification(data.data);
            }
        };

        this.ws.onclose = () => {
            console.log('WebSocket disconnected');
            // Attempt to reconnect after 5 seconds
            setTimeout(() => this.initializeWebSocket(), 5000);
        };

        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }

    // Handle new notification from WebSocket
    handleNewNotification(notification) {
        // Update unread count
        const currentCount = parseInt(this.unreadBadge.textContent || '0');
        this.unreadBadge.textContent = currentCount + 1;
        this.unreadBadge.style.display = 'block';

        // Add notification to the list
        const today = new Date().toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const notificationElement = this.createNotificationElement(notification);
        
        // Check if today's group exists
        let dateGroup = this.notificationList.querySelector('.notification-date-group');
        if (!dateGroup || dateGroup.querySelector('.notification-date').textContent !== today) {
            // Create new date group
            dateGroup = document.createElement('div');
            dateGroup.className = 'notification-date-group';
            dateGroup.innerHTML = `
                <div class="notification-date">${today}</div>
            `;
            this.notificationList.insertBefore(dateGroup, this.notificationList.firstChild);
        }

        // Add notification to the group
        dateGroup.insertAdjacentHTML('afterbegin', notificationElement);

        // Show notification if container is hidden
        if (!this.notificationContainer.classList.contains('show')) {
            // Show temporary popup
            this.showNotificationPopup(notification);
        }
    }

    // Show temporary notification popup
    showNotificationPopup(notification) {
        const popup = document.createElement('div');
        popup.className = 'notification-popup';
        popup.innerHTML = `
            <div class="notification-popup-content">
                <p>${notification.message}</p>
            </div>
        `;

        document.body.appendChild(popup);

        // Add animation class after a small delay
        setTimeout(() => popup.classList.add('show'), 10);

        // Remove popup after 5 seconds
        setTimeout(() => {
            popup.classList.remove('show');
            setTimeout(() => popup.remove(), 300);
        }, 5000);
    }

    // Fetch notifications from server
    async fetchNotifications(page = 0) {
        if (this.loading || (!this.hasMore && page > 0)) return;
        
        this.loading = true;
        try {
            const formData = new FormData();
            formData.append('action', 'fetch');
            formData.append('user_id', this.userId);
            formData.append('role', this.userRole);
            formData.append('page', page);

            const response = await fetch('/quicklease/dashboard/fetch_notification.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                this.updateNotificationUI(data.notifications, data.unread_count, page === 0);
                this.hasMore = data.has_more;
                this.page = page;
            } else {
                console.error('Error fetching notifications:', data.message);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        } finally {
            this.loading = false;
        }
    }

    // Update notification UI
    updateNotificationUI(notifications, unreadCount, replace = true) {
        // Update unread badge
        this.unreadBadge.textContent = unreadCount;
        this.unreadBadge.style.display = unreadCount > 0 ? 'block' : 'none';

        // Handle empty state
        if (Object.keys(notifications).length === 0) {
            if (replace) {
                this.notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
            }
            return;
        }

        // Create notification elements
        const notificationHTML = Object.entries(notifications).map(([date, items]) => {
            const formattedDate = new Date(date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            const notificationItems = items.map(notification => this.createNotificationElement(notification)).join('');

            return `
                <div class="notification-date-group">
                    <div class="notification-date">${formattedDate}</div>
                    ${notificationItems}
                </div>
            `;
        }).join('');

        if (replace) {
            this.notificationList.innerHTML = notificationHTML;
        } else {
            this.notificationList.insertAdjacentHTML('beforeend', notificationHTML);
        }
    }

    // Create notification element
    createNotificationElement(notification) {
        const timeAgo = this.formatTimeAgo(new Date(notification.created_at));
        const priorityClass = `priority-${notification.priority}`;
        
        return `
            <div class="notification-item ${notification.is_read ? 'read' : 'unread'} ${priorityClass}" 
                 data-id="${notification.id}" 
                 onclick="notificationManager.markAsRead(${notification.id})">
                <div class="notification-content">
                    <p>${notification.message}</p>
                    <small>${timeAgo}</small>
                </div>
            </div>
        `;
    }

    // Mark single notification as read
    async markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('notification_id', notificationId);
            formData.append('user_id', this.userId);
            formData.append('role', this.userRole);

            const response = await fetch('/quicklease/dashboard/fetch_notification.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                // Update UI for the specific notification
                const notificationElement = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    notificationElement.classList.add('read');
                }
                // Refresh unread count
                this.fetchNotifications(0);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all notifications as read
    async markAllAsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_all_read');
            formData.append('user_id', this.userId);
            formData.append('role', this.userRole);

            const response = await fetch('/quicklease/dashboard/fetch_notification.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                // Refresh notifications
                this.fetchNotifications(0);
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    // Format date for display
    formatTimeAgo(date) {
        const now = new Date();
        const diff = now - date;
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 60) {
            return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`;
        } else if (hours < 24) {
            return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
        } else if (days < 7) {
            return `${days} day${days !== 1 ? 's' : ''} ago`;
        } else {
            return date.toLocaleDateString();
        }
    }

    // Initialize notifications
    initializeNotifications() {
        // Fetch notifications immediately
        this.fetchNotifications();

        // Set up notification bell click handler
        this.notificationBell.addEventListener('click', () => {
            this.notificationContainer.classList.toggle('show');
            if (this.notificationContainer.classList.contains('show')) {
                this.fetchNotifications(); // Refresh when opening
            }
        });

        // Close notifications when clicking outside
        document.addEventListener('click', (event) => {
            if (!this.notificationBell.contains(event.target) && 
                !this.notificationContainer.contains(event.target)) {
                this.notificationContainer.classList.remove('show');
            }
        });

        // Infinite scroll for notifications
        this.notificationList.addEventListener('scroll', () => {
            if (this.notificationList.scrollHeight - this.notificationList.scrollTop 
                === this.notificationList.clientHeight) {
                this.fetchNotifications(this.page + 1);
            }
        });
    }
} 