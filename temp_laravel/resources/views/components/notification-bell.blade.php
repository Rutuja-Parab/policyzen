@props(['user' => null])

<div class="relative" x-data="notificationBell()">
    <!-- Notification Bell Button -->
    <button 
        @click="toggleDropdown()" 
        class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2b8bd0] focus:ring-offset-2 rounded-lg transition-colors"
        :class="{ 'text-[#f06e11] bg-blue-50': dropdownOpen }"
    >
        <i class="fas fa-bell text-xl"></i>
        
        <!-- Notification Badge -->
        <span 
            x-show="unreadCount > 0"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium"
            :class="{ 'animate-pulse': hasNewNotifications }"
        ></span>
    </button>

    <!-- Notification Dropdown -->
    <div 
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="closeDropdown()"
        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                <div class="flex items-center space-x-2">
                    <button 
                        @click="markAllAsRead()" 
                        x-show="unreadCount > 0"
                        class="text-xs text-[#f06e11] hover:text-blue-800 font-medium"
                    >
                        Mark all read
                    </button>
                    <a href="{{ route('notifications.index') }}" class="text-xs text-gray-500 hover:text-gray-700">
                        View all
                    </a>
                </div>
            </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-96 overflow-y-auto">
            <div x-show="loading" class="p-4 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin"></i> Loading notifications...
            </div>

            <div x-show="!loading && notifications.length === 0" class="p-4 text-center text-gray-500">
                <i class="fas fa-bell-slash text-2xl mb-2"></i>
                <p>No notifications</p>
            </div>

            <template x-for="notification in notifications" :key="notification.id">
                <div 
                    class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                    :class="{ 'bg-blue-50': !notification.is_read }"
                    @click="markAsRead(notification.id, $event)"
                >
                    <div class="flex items-start space-x-3">
                        <!-- Notification Icon -->
                        <div 
                            class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                            :class="getPriorityColorClass(notification.priority)"
                        >
                            <i :class="notification.icon" class="text-sm"></i>
                        </div>

                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p 
                                    class="text-sm font-medium text-gray-900 truncate"
                                    :class="{ 'font-semibold': !notification.is_read }"
                                    x-text="notification.title"
                                ></p>
                                <span 
                                    class="text-xs text-gray-500 ml-2 flex-shrink-0"
                                    x-text="notification.created_at"
                                ></span>
                            </div>
                            <p 
                                class="text-sm text-gray-600 mt-1 line-clamp-2"
                                x-text="notification.message"
                            ></p>
                            
                            <!-- Priority Badge -->
                            <span 
                                class="inline-block px-2 py-1 text-xs font-medium rounded-full mt-2"
                                :class="getPriorityBadgeClass(notification.priority)"
                                x-text="notification.priority"
                            ></span>
                        </div>

                        <!-- Read/Unread Indicator -->
                        <div 
                            x-show="!notification.is_read"
                            class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"
                        ></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Dropdown Footer -->
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">
                    <span x-text="unreadCount"></span> unread
                </span>
                <a href="{{ route('notifications.index') }}" class="text-[#f06e11] hover:text-blue-800 font-medium">
                    View all notifications
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        dropdownOpen: false,
        notifications: [],
        unreadCount: 0,
        loading: false,
        hasNewNotifications: false,

        async init() {
            await this.loadNotifications();
            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.loadNotifications(true);
            }, 30000);
        },

        async loadNotifications(silent = false) {
            if (!silent) {
                this.loading = true;
            }

            try {
                const response = await fetch('/notifications/recent?limit=10');
                const data = await response.json();
                
                const oldUnreadCount = this.unreadCount;
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;

                // Check for new notifications
                if (this.unreadCount > oldUnreadCount && oldUnreadCount > 0) {
                    this.hasNewNotifications = true;
                    setTimeout(() => {
                        this.hasNewNotifications = false;
                    }, 2000);
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            } finally {
                this.loading = false;
            }
        },

        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
            if (this.dropdownOpen) {
                this.loadNotifications();
            }
        },

        closeDropdown() {
            this.dropdownOpen = false;
        },

        async markAsRead(notificationId, event) {
            // Don't mark as read if clicking on external link
            if (event.target.tagName === 'A' || event.target.closest('a')) {
                return;
            }

            try {
                const response = await fetch(`/notifications/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    // Update local state
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification && !notification.is_read) {
                        notification.is_read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    // Update local state
                    this.notifications.forEach(notification => {
                        notification.is_read = true;
                    });
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },

        getPriorityColorClass(priority) {
            const classes = {
                'CRITICAL': 'bg-red-100 text-red-600',
                'HIGH': 'bg-orange-100 text-orange-600',
                'MEDIUM': 'bg-yellow-100 text-yellow-600',
                'LOW': 'bg-green-100 text-green-600'
            };
            return classes[priority] || 'bg-gray-100 text-gray-600';
        },

        getPriorityBadgeClass(priority) {
            const classes = {
                'CRITICAL': 'bg-red-100 text-red-800',
                'HIGH': 'bg-orange-100 text-orange-800',
                'MEDIUM': 'bg-yellow-100 text-yellow-800',
                'LOW': 'bg-green-100 text-green-800'
            };
            return classes[priority] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
