@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('header-actions')
<div class="flex items-center space-x-3">
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                <i class="fas fa-check-double mr-2"></i>Mark All Read
            </button>
        </form>
    @endif
    
    <form method="POST" action="{{ route('notifications.delete-all-read') }}" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
            <i class="fas fa-trash mr-2"></i>Delete Read
        </button>
    </form>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Notifications Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-bell text-2xl text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Notifications</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $notifications->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-envelope text-2xl text-orange-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Unread</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $unreadCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Critical</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $notifications->where('priority', 'CRITICAL')->whereNull('read_at')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar text-2xl text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Policy Alerts</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $notifications->where('type', 'POLICY_EXPIRY_WARNING')->whereNull('read_at')->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                </select>
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority" id="priority" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="CRITICAL" {{ request('priority') === 'CRITICAL' ? 'selected' : '' }}>Critical</option>
                    <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>High</option>
                    <option value="MEDIUM" {{ request('priority') === 'MEDIUM' ? 'selected' : '' }}>Medium</option>
                    <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" id="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="POLICY_EXPIRY_WARNING" {{ request('type') === 'POLICY_EXPIRY_WARNING' ? 'selected' : '' }}>Policy Expiry</option>
                    <option value="ENDORSEMENT_PENDING" {{ request('type') === 'ENDORSEMENT_PENDING' ? 'selected' : '' }}>Endorsement</option>
                    <option value="POLICY_EXPIRED" {{ request('type') === 'POLICY_EXPIRED' ? 'selected' : '' }}>Policy Expired</option>
                </select>
            </div>

            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Search notifications..." 
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full">
            </div>

            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>

            @if(request()->anyFilled(['status', 'priority', 'type', 'search']))
                <div class="flex items-end">
                    <a href="{{ route('notifications.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($notifications->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                    <div class="p-6 hover:bg-gray-50 transition-colors {{ !$notification->isRead() ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                        <div class="flex items-start space-x-4">
                            <!-- Priority Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    @if($notification->priority === 'CRITICAL') bg-red-100 text-red-600
                                    @elseif($notification->priority === 'HIGH') bg-orange-100 text-orange-600
                                    @elseif($notification->priority === 'MEDIUM') bg-yellow-100 text-yellow-600
                                    @else bg-green-100 text-green-600 @endif">
                                    <i class="{{ $notification->icon }}"></i>
                                </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 {{ !$notification->isRead() ? 'font-bold' : '' }}">
                                        {{ $notification->title }}
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full
                                            @if($notification->priority === 'CRITICAL') bg-red-100 text-red-800
                                            @elseif($notification->priority === 'HIGH') bg-orange-100 text-orange-800
                                            @elseif($notification->priority === 'MEDIUM') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ $notification->priority }}
                                        </span>
                                        @if(!$notification->isRead())
                                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        @endif
                                    </div>
                                </div>

                                <p class="mt-2 text-gray-600">{{ $notification->message }}</p>

                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <span>
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                        @if($notification->data && isset($notification->data['policy_number']))
                                            <span>
                                                <i class="fas fa-file-alt mr-1"></i>
                                                Policy: {{ $notification->data['policy_number'] }}
                                            </span>
                                        @endif
                                        @if($notification->data && isset($notification->data['days_until_expiry']))
                                            <span>
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ $notification->data['days_until_expiry'] }} days remaining
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2">
                                        @if($notification->isRead())
                                            <form method="POST" action="{{ route('notifications.mark-unread', $notification) }}" class="inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="text-xs text-gray-500 hover:text-gray-700" title="Mark as unread">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('notifications.mark-read', $notification) }}" class="inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800" title="Mark as read">
                                                    <i class="fas fa-envelope-open"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this notification?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <i class="fas fa-bell-slash text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications found</h3>
                <p class="text-gray-500">
                    @if(request()->anyFilled(['status', 'priority', 'type', 'search']))
                        Try adjusting your filters or
                        <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800">clear all filters</a>.
                    @else
                        You're all caught up! No new notifications at this time.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection