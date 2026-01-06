@extends('layouts.app')

@section('title', 'Audit Logs')

@section('page-title', 'Audit Logs')

@section('content')
    <div class="space-y-6">
        <!-- Header with Actions -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                <p class="mt-1 text-sm text-gray-500">Track all system activities and changes</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="exportLogs()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
                <button onclick="refreshLogs()"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#f06e11] hover:bg-[#f28e1f]">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6">
            <form method="GET" action="{{ route('audit-logs.index') }}"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Search logs..."
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#2b8bd0] focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                    <select name="action" id="action"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#2b8bd0] focus:border-blue-500 sm:text-sm">
                        <option value="">All Actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="entity_type" class="block text-sm font-medium text-gray-700">Entity Type</label>
                    <select name="entity_type" id="entity_type"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#2b8bd0] focus:border-blue-500 sm:text-sm">
                        <option value="">All Types</option>
                        @foreach ($entityTypes as $type)
                            <option value="{{ $type }}" {{ request('entity_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#2b8bd0] focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#2b8bd0] focus:border-blue-500 sm:text-sm">
                </div>
                <div class="lg:col-span-5 flex justify-end space-x-3">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#f06e11] hover:bg-[#f28e1f]">
                        <i class="fas fa-search mr-2"></i>
                        Filter
                    </button>
                    <a href="{{ route('audit-logs.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                                & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Entity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Performed By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $log->action == 'CREATE'
                                        ? 'bg-[#f06e11]/10 text-[#f06e11]'
                                        : ($log->action == 'UPDATE'
                                            ? 'bg-[#2b8bd0]/10 text-[#2b8bd0]'
                                            : ($log->action == 'DELETE'
                                                ? 'bg-[#04315b]/10 text-[#04315b]'
                                                : ($log->action == 'STATUS_CHANGE'
                                                    ? 'bg-[#f28e1f]/10 text-[#f28e1f]'
                                                    : 'bg-gray-100 text-gray-800'))) }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if ($log->entity_type)
                                        <span class="text-gray-900">{{ $log->entity_type }}</span>
                                        @if ($log->entity_id)
                                            <span class="text-gray-500">#{{ $log->entity_id }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->performer->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if ($log->amount)
                                        ${{ number_format($log->amount, 2) }}
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('audit-logs.show', $log) }}"
                                        class="text-[#f06e11] hover:text-blue-900">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No audit logs found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($auditLogs->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $auditLogs->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function refreshLogs() {
                window.location.reload();
            }

            function exportLogs() {
                const params = new URLSearchParams(window.location.search);
                params.set('export', 'csv');

                fetch('{{ route('audit-logs.export') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(Object.fromEntries(params))
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || 'Export functionality needs to be implemented');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Export failed');
                    });
            }
        </script>
    @endpush
@endsection
