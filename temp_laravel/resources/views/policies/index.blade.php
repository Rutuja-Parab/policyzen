@extends('layouts.app')

@section('title', 'Policy Management - PolicyZen')
@section('page-title', 'Policy Management')

@section('header-actions')
<a href="{{ route('policies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-plus mr-2"></i>Create Policy
</a>
@endsection

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
    {{ session('error') }}
</div>
@endif

<!-- Search and Filters -->
<x-search-filters 
    action="{{ route('policies.index') }}"
    search-placeholder="Search policies by number, provider, type, or entity..."
    :date-range="true"
>
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Statuses</option>
            @foreach($statusOptions as $status)
            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                {{ ucfirst(str_replace('_', ' ', $status)) }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="insurance_type" class="block text-sm font-medium text-gray-700 mb-1">Insurance Type</label>
        <select id="insurance_type" name="insurance_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Types</option>
            @foreach($insuranceTypes as $type)
            <option value="{{ $type }}" {{ request('insurance_type') == $type ? 'selected' : '' }}>
                {{ ucfirst(strtolower($type)) }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="premium_min" class="block text-sm font-medium text-gray-700 mb-1">Min Premium ($)</label>
        <input type="number" id="premium_min" name="premium_min" value="{{ request('premium_min') }}" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
               placeholder="0">
    </div>

    <div>
        <label for="premium_max" class="block text-sm font-medium text-gray-700 mb-1">Max Premium ($)</label>
        <input type="number" id="premium_max" name="premium_max" value="{{ request('premium_max') }}" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
               placeholder="999999">
    </div>

    <div>
        <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
        <select id="per_page" name="per_page" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
            <option value="20" {{ request('per_page', '20') == '20' ? 'selected' : '' }}>20</option>
            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
        </select>
    </div>
</x-search-filters>

<!-- Results Summary -->
<div class="mb-4 flex items-center justify-between">
    <div class="text-sm text-gray-600">
        @if($policies->total() > 0)
        Showing {{ $policies->firstItem() ?? 0 }} to {{ $policies->lastItem() ?? 0 }} of {{ $policies->total() }} policies
        @else
        No policies found
        @endif
    </div>
    
    @if(request()->hasAny(['search', 'status', 'insurance_type', 'date_from', 'date_to', 'premium_min', 'premium_max']))
    <div class="text-sm">
        <a href="{{ route('policies.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-times mr-1"></i>Clear all filters
        </a>
    </div>
    @endif
</div>

@if($policies->count() > 0)
<!-- Bulk Actions -->
<div class="mb-4 flex items-center space-x-4">
    <div class="flex items-center">
        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
        <label for="selectAll" class="ml-2 text-sm text-gray-700">Select All ({{ $policies->total() }} total)</label>
    </div>
    
    <div class="flex items-center space-x-2">
        <select id="bulkAction" class="text-sm border border-gray-300 rounded px-3 py-1">
            <option value="">Bulk Actions</option>
            <option value="export">Export Selected</option>
            <option value="status_active">Set Status: Active</option>
            <option value="status_expired">Set Status: Expired</option>
            <option value="status_review">Set Status: Under Review</option>
            <option value="status_cancelled">Set Status: Cancelled</option>
            <option value="delete">Delete Selected</option>
        </select>
        <button onclick="performBulkAction()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
            Apply
        </button>
    </div>
</div>

<!-- Policies Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-12 px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAllTable" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'policy_number', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Policy
                            @if(request('sort_by') == 'policy_number')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'insurance_type', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Type
                            @if(request('sort_by') == 'insurance_type')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'provider', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Provider
                            @if(request('sort_by') == 'provider')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'premium_amount', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Premium
                            @if(request('sort_by') == 'premium_amount')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entities</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Created
                            @if(request('sort_by') == 'created_at' || !request('sort_by'))
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="w-20 px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($policies as $policy)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-2 py-3 whitespace-nowrap">
                        <input type="checkbox" name="selected_policies[]" value="{{ $policy->id }}" 
                               class="policy-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $policy->policy_number }}</div>
                        @if($policy->start_date && $policy->end_date)
                        <div class="text-xs text-gray-500">{{ $policy->start_date }} to {{ $policy->end_date }}</div>
                        @endif
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($policy->insurance_type == 'HEALTH') bg-green-100 text-green-800
                            @elseif($policy->insurance_type == 'ACCIDENT') bg-red-100 text-red-800
                            @elseif($policy->insurance_type == 'PROPERTY') bg-blue-100 text-blue-800
                            @elseif($policy->insurance_type == 'VEHICLE') bg-purple-100 text-purple-800
                            @elseif($policy->insurance_type == 'MARINE') bg-indigo-100 text-indigo-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(strtolower($policy->insurance_type)) }}
                        </span>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $policy->provider }}</div>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($policy->status === 'ACTIVE') bg-green-100 text-green-800
                            @elseif($policy->status === 'EXPIRED') bg-red-100 text-red-800
                            @elseif($policy->status === 'UNDER_REVIEW') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ str_replace('_', ' ', $policy->status) }}
                        </span>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${{ number_format($policy->premium_amount, 2) }}</div>
                        <div class="text-xs text-gray-500">Sum: ${{ number_format($policy->sum_insured, 2) }}</div>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $policy->entities->count() }} entities</div>
                        @if($policy->entities->count() > 0)
                        <div class="text-xs text-gray-500 truncate max-w-24">
                            @foreach($policy->entities->take(2) as $entity)
                                {{ $entity->description }}@if(!$loop->last), @endif
                            @endforeach
                            @if($policy->entities->count() > 2)
                                +{{ $policy->entities->count() - 2 }} more
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $policy->created_at->format('M d, Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $policy->created_at->diffForHumans() }}</div>
                        @if($policy->creator)
                        <div class="text-xs text-gray-400">by {{ $policy->creator->name }}</div>
                        @endif
                    </td>
                    <td class="px-2 py-3 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-1">
                            <a href="{{ route('policies.show', $policy) }}" 
                               class="text-blue-600 hover:text-blue-900 p-1 rounded" 
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('policies.edit', $policy) }}" 
                               class="text-green-600 hover:text-green-900 p-1 rounded" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('policies.destroy', $policy) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this policy?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-1 rounded" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($policies->hasPages())
<div class="mt-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Page {{ $policies->currentPage() }} of {{ $policies->lastPage() }}
        </div>
        <div class="flex items-center space-x-1">
            {{ $policies->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endif

@else
<!-- Empty State -->
<div class="text-center py-12">
    <i class="fas fa-file-alt text-gray-300 text-6xl mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No policies found</h3>
    <p class="text-gray-500 mb-6">
        @if(request()->hasAny(['search', 'status', 'insurance_type', 'date_from', 'date_to', 'premium_min', 'premium_max']))
            No policies match your current filters. Try adjusting your search criteria.
        @else
            Create your first insurance policy to get started.
        @endif
    </p>
    <div class="flex justify-center space-x-4">
        @if(request()->hasAny(['search', 'status', 'insurance_type', 'date_from', 'date_to', 'premium_min', 'premium_max']))
        <a href="{{ route('policies.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            <i class="fas fa-times mr-2"></i>Clear Filters
        </a>
        @endif
        <a href="{{ route('policies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create Policy
        </a>
    </div>
</div>
@endif

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.policy-checkbox');
    const tableCheckbox = document.getElementById('selectAllTable');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    
    if (tableCheckbox) {
        tableCheckbox.checked = this.checked;
    }
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.policy-checkbox');
    const mainCheckbox = document.getElementById('selectAll');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    
    if (mainCheckbox) {
        mainCheckbox.checked = this.checked;
    }
});

// Bulk actions
function performBulkAction() {
    const selectedPolicies = Array.from(document.querySelectorAll('.policy-checkbox:checked')).map(cb => cb.value);
    const action = document.getElementById('bulkAction').value;
    
    if (selectedPolicies.length === 0) {
        alert('Please select at least one policy.');
        return;
    }
    
    if (!action) {
        alert('Please select an action to perform.');
        return;
    }
    
    // Handle export action separately
    if (action === 'export') {
        exportSelectedPolicies(selectedPolicies);
        return;
    }
    
    let confirmMessage = `Are you sure you want to perform this action on ${selectedPolicies.length} selected policy(s)?`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Create form for bulk action
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("policies.bulk-action") }}';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add action type
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);
    
    // Add selected policy IDs
    selectedPolicies.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'policy_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Export selected policies
function exportSelectedPolicies(selectedIds) {
    const url = new URL(window.location.href);
    url.searchParams.set('export', 'csv');
    url.searchParams.set('selected_ids', selectedIds.join(','));
    window.open(url.toString(), '_blank');
}

// Auto-submit form on per_page change
document.getElementById('per_page').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection