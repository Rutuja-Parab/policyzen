@extends('layouts.app')

@section('title', 'Vehicles - PolicyZen')
@section('page-title', 'Vehicle Management')

@section('header-actions')
<a href="{{ route('entities.vehicles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-plus mr-2"></i>Add Vehicle
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
    action="{{ route('entities.vehicles.index') }}"
    search-placeholder="Search vehicles by registration number, make, model, or company..."
    :date-range="false"
>
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Statuses</option>
            <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
            <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div>
        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
        <select id="company_id" name="company_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Companies</option>
            @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                {{ $company->name }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="make" class="block text-sm font-medium text-gray-700 mb-1">Make</label>
        <select id="make" name="make" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Makes</option>
            @foreach($makes as $make)
            <option value="{{ $make }}" {{ request('make') == $make ? 'selected' : '' }}>
                {{ $make }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="year_min" class="block text-sm font-medium text-gray-700 mb-1">Min Year</label>
        <input type="number" id="year_min" name="year_min" value="{{ request('year_min') }}" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
               placeholder="2000">
    </div>

    <div>
        <label for="year_max" class="block text-sm font-medium text-gray-700 mb-1">Max Year</label>
        <input type="number" id="year_max" name="year_max" value="{{ request('year_max') }}" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
               placeholder="2024">
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
        @if($vehicles->total() > 0)
        Showing {{ $vehicles->firstItem() ?? 0 }} to {{ $vehicles->lastItem() ?? 0 }} of {{ $vehicles->total() }} vehicles
        @else
        No vehicles found
        @endif
    </div>
    
    @if(request()->hasAny(['search', 'status', 'company_id', 'make', 'year_min', 'year_max']))
    <div class="text-sm">
        <a href="{{ route('entities.vehicles.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-times mr-1"></i>Clear all filters
        </a>
    </div>
    @endif
</div>

@if($vehicles->count() > 0)
<!-- Bulk Actions -->
<div class="mb-4 flex items-center space-x-4">
    <div class="flex items-center">
        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
        <label for="selectAll" class="ml-2 text-sm text-gray-700">Select All ({{ $vehicles->total() }} total)</label>
    </div>
    
    <div class="flex items-center space-x-2">
        <select id="bulkAction" class="text-sm border border-gray-300 rounded px-3 py-1">
            <option value="">Bulk Actions</option>
            <option value="export">Export Selected</option>
            <option value="status_active">Set Status: Active</option>
            <option value="status_inactive">Set Status: Inactive</option>
        </select>
        <button onclick="performBulkAction()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
            Apply
        </button>
    </div>
</div>

<!-- Vehicles Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAllTable" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'registration_number', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Registration Number
                            @if(request('sort_by') == 'registration_number')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'make', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Make
                            @if(request('sort_by') == 'make')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'model', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Model
                            @if(request('sort_by') == 'model')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'year', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Year
                            @if(request('sort_by') == 'year')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Status
                            @if(request('sort_by') == 'status')
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
                @foreach($vehicles as $vehicle)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" name="selected_vehicles[]" value="{{ $vehicle->id }}" 
                               class="vehicle-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $vehicle->registration_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $vehicle->make }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $vehicle->model }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $vehicle->year }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $vehicle->company->name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($vehicle->status === 'ACTIVE') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $vehicle->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('entities.vehicles.show', $vehicle) }}" 
                               class="text-blue-600 hover:text-blue-900 p-1 rounded" 
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('entities.vehicles.edit', $vehicle) }}" 
                               class="text-green-600 hover:text-green-900 p-1 rounded" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('entities.vehicles.destroy', $vehicle) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this vehicle?')">
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
@if($vehicles->hasPages())
<div class="mt-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Page {{ $vehicles->currentPage() }} of {{ $vehicles->lastPage() }}
        </div>
        <div class="flex items-center space-x-1">
            {{ $vehicles->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endif

@else
<!-- Empty State -->
<div class="text-center py-12">
    <i class="fas fa-car text-gray-300 text-6xl mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No vehicles found</h3>
    <p class="text-gray-500 mb-6">
        @if(request()->hasAny(['search', 'status', 'company_id', 'make', 'year_min', 'year_max']))
            No vehicles match your current filters. Try adjusting your search criteria.
        @else
            Get started by adding your first vehicle.
        @endif
    </p>
    <div class="flex justify-center space-x-4">
        @if(request()->hasAny(['search', 'status', 'company_id', 'make', 'year_min', 'year_max']))
        <a href="{{ route('entities.vehicles.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            <i class="fas fa-times mr-2"></i>Clear Filters
        </a>
        @endif
        <a href="{{ route('entities.vehicles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Vehicle
        </a>
    </div>
</div>
@endif

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.vehicle-checkbox');
    const tableCheckbox = document.getElementById('selectAllTable');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    
    if (tableCheckbox) {
        tableCheckbox.checked = this.checked;
    }
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.vehicle-checkbox');
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
    const selectedVehicles = Array.from(document.querySelectorAll('.vehicle-checkbox:checked')).map(cb => cb.value);
    const action = document.getElementById('bulkAction').value;
    
    if (selectedVehicles.length === 0) {
        alert('Please select at least one vehicle.');
        return;
    }
    
    if (!action) {
        alert('Please select an action to perform.');
        return;
    }
    
    let confirmMessage = `Are you sure you want to perform this action on ${selectedVehicles.length} selected vehicle(s)?`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Create form for bulk action
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("entities.vehicles.bulk-action") }}';
    
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
    
    // Add selected vehicle IDs
    selectedVehicles.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'vehicle_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Auto-submit form on per_page change
document.getElementById('per_page').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection