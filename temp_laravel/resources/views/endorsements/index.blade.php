@extends('layouts.app')

@section('title', 'Policy Endorsements - PolicyZen')

@section('page-title')
<div class="flex items-center">
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Policy Endorsements</span>
</div>
@endsection

@section('header-actions')
<a href="{{ route('endorsements.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-plus mr-2"></i>Create Endorsement
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
    action="{{ route('endorsements.index') }}"
    search-placeholder="Search endorsements by number, description, policy, or creator..."
    :date-range="true"
>
    <div>
        <label for="policy_id" class="block text-sm font-medium text-gray-700 mb-1">Policy</label>
        <select id="policy_id" name="policy_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Policies</option>
            @foreach($policies as $policy)
            <option value="{{ $policy->id }}" {{ request('policy_id') == $policy->id ? 'selected' : '' }}>
                {{ $policy->policy_number }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="description_keyword" class="block text-sm font-medium text-gray-700 mb-1">Description Contains</label>
        <input type="text" id="description_keyword" name="description_keyword" value="{{ request('description_keyword') }}" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
               placeholder="Enter keyword...">
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
        @if($endorsements->total() > 0)
        Showing {{ $endorsements->firstItem() ?? 0 }} to {{ $endorsements->lastItem() ?? 0 }} of {{ $endorsements->total() }} endorsements
        @else
        No endorsements found
        @endif
    </div>
    
    @if(request()->hasAny(['search', 'policy_id', 'date_from', 'date_to', 'description_keyword']))
    <div class="text-sm">
        <a href="{{ route('endorsements.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-times mr-1"></i>Clear all filters
        </a>
    </div>
    @endif
</div>

@if($endorsements->count() > 0)
<!-- Bulk Actions -->
<div class="mb-4 flex items-center space-x-4">
    <div class="flex items-center">
        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
        <label for="selectAll" class="ml-2 text-sm text-gray-700">Select All ({{ $endorsements->total() }} total)</label>
    </div>
    
    <div class="flex items-center space-x-2">
        <select id="bulkAction" class="text-sm border border-gray-300 rounded px-3 py-1">
            <option value="">Bulk Actions</option>
            <option value="export">Export Selected</option>
            <option value="delete">Delete Selected</option>
        </select>
        <button onclick="performBulkAction()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
            Apply
        </button>
    </div>
</div>

<!-- Endorsements Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAllTable" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'endorsement_number', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Endorsement Number
                            @if(request('sort_by') == 'endorsement_number')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Policy
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'effective_date', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Effective Date
                            @if(request('sort_by') == 'effective_date')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($endorsements as $endorsement)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" name="selected_endorsements[]" value="{{ $endorsement->id }}" 
                               class="endorsement-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $endorsement->endorsement_number }}</div>
                        @if($endorsement->policy)
                        <div class="text-sm text-gray-500">{{ $endorsement->policy->provider }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($endorsement->policy)
                        <div class="text-sm font-medium text-gray-900">{{ $endorsement->policy->policy_number }}</div>
                        <div class="text-sm text-gray-500">{{ $endorsement->policy->insurance_type }}</div>
                        @else
                        <div class="text-sm text-gray-500">N/A</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $endorsement->description }}">
                            {{ Str::limit($endorsement->description, 80) }}
                        </div>
                        @if(strlen($endorsement->description) > 80)
                        <button onclick="showFullDescription('{{ $endorsement->id }}')" 
                                class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                            Read more
                        </button>
                        <div id="full-description-{{ $endorsement->id }}" class="hidden text-sm text-gray-700 mt-2 p-3 bg-gray-50 rounded border">
                            {{ $endorsement->description }}
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($endorsement->effective_date)->format('M d, Y') }}</div>
                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($endorsement->effective_date)->diffForHumans() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $endorsement->created_at->format('M d, Y') }}</div>
                        <div class="text-sm text-gray-500">{{ $endorsement->created_at->diffForHumans() }}</div>
                        @if($endorsement->creator)
                        <div class="text-xs text-gray-400">by {{ $endorsement->creator->name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('endorsements.show', $endorsement) }}" 
                               class="text-blue-600 hover:text-blue-900 p-1 rounded" 
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('endorsements.edit', $endorsement) }}" 
                               class="text-green-600 hover:text-green-900 p-1 rounded" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('endorsements.destroy', $endorsement) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this endorsement?')">
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
@if($endorsements->hasPages())
<div class="mt-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Page {{ $endorsements->currentPage() }} of {{ $endorsements->lastPage() }}
        </div>
        <div class="flex items-center space-x-1">
            {{ $endorsements->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endif

@else
<!-- Empty State -->
<div class="text-center py-12">
    <i class="fas fa-edit text-gray-300 text-6xl mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No endorsements found</h3>
    <p class="text-gray-500 mb-6">
        @if(request()->hasAny(['search', 'policy_id', 'date_from', 'date_to', 'description_keyword']))
            No endorsements match your current filters. Try adjusting your search criteria.
        @else
            Policy endorsements will appear here once created.
        @endif
    </p>
    <div class="flex justify-center space-x-4">
        @if(request()->hasAny(['search', 'policy_id', 'date_from', 'date_to', 'description_keyword']))
        <a href="{{ route('endorsements.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            <i class="fas fa-times mr-2"></i>Clear Filters
        </a>
        @endif
        <a href="{{ route('endorsements.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Create Endorsement
        </a>
    </div>
</div>
@endif

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.endorsement-checkbox');
    const tableCheckbox = document.getElementById('selectAllTable');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    
    if (tableCheckbox) {
        tableCheckbox.checked = this.checked;
    }
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.endorsement-checkbox');
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
    const selectedEndorsements = Array.from(document.querySelectorAll('.endorsement-checkbox:checked')).map(cb => cb.value);
    const action = document.getElementById('bulkAction').value;
    
    if (selectedEndorsements.length === 0) {
        alert('Please select at least one endorsement.');
        return;
    }
    
    if (!action) {
        alert('Please select an action to perform.');
        return;
    }
    
    let confirmMessage = `Are you sure you want to perform this action on ${selectedEndorsements.length} selected endorsement(s)?`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    if (action === 'delete') {
        if (!confirm('This will permanently delete the selected endorsements. Are you absolutely sure?')) {
            return;
        }
    }
    
    // Create form for bulk action
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("endorsements.bulk-action") }}';
    
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
    
    // Add selected endorsement IDs
    selectedEndorsements.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'endorsement_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Show full description
function showFullDescription(endorsementId) {
    const fullDesc = document.getElementById('full-description-' + endorsementId);
    const button = event.target;
    
    if (fullDesc.classList.contains('hidden')) {
        fullDesc.classList.remove('hidden');
        button.textContent = 'Show less';
    } else {
        fullDesc.classList.add('hidden');
        button.textContent = 'Read more';
    }
}

// Auto-submit form on per_page change
document.getElementById('per_page').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection