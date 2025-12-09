@extends('layouts.app')

@section('title', 'Employees - PolicyZen')
@section('page-title', 'Employee Management')

@section('header-actions')
<a href="{{ route('entities.employees.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
    <i class="fas fa-plus mr-2"></i>Add Employee
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
    action="{{ route('entities.employees.index') }}"
    search-placeholder="Search employees by name, code, department, or company..."
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
        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
        <select id="department" name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Departments</option>
            @foreach($departments as $department)
            <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>
                {{ $department }}
            </option>
            @endforeach
        </select>
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
        @if($employees->total() > 0)
        Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} employees
        @else
        No employees found
        @endif
    </div>
    
    @if(request()->hasAny(['search', 'status', 'company_id', 'department']))
    <div class="text-sm">
        <a href="{{ route('entities.employees.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-times mr-1"></i>Clear all filters
        </a>
    </div>
    @endif
</div>

@if($employees->count() > 0)
<!-- Bulk Actions -->
<div class="mb-4 flex items-center space-x-4">
    <div class="flex items-center">
        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
        <label for="selectAll" class="ml-2 text-sm text-gray-700">Select All ({{ $employees->total() }} total)</label>
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

<!-- Employees Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAllTable" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'employee_code', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Employee Code
                            @if(request('sort_by') == 'employee_code')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Name
                            @if(request('sort_by') == 'name')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'department', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Department
                            @if(request('sort_by') == 'department')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @else
                                <i class="fas fa-sort ml-1 opacity-50"></i>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort_by' => 'position', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                           class="hover:text-gray-700">
                            Position
                            @if(request('sort_by') == 'position')
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
                @foreach($employees as $employee)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" name="selected_employees[]" value="{{ $employee->id }}" 
                               class="employee-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $employee->employee_code }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $employee->department ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $employee->position ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $employee->company->name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if($employee->status === 'ACTIVE') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $employee->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $employee->created_at->format('M d, Y') }}</div>
                        <div class="text-sm text-gray-500">{{ $employee->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('entities.employees.show', $employee) }}" 
                               class="text-blue-600 hover:text-blue-900 p-1 rounded" 
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('entities.employees.edit', $employee) }}" 
                               class="text-green-600 hover:text-green-900 p-1 rounded" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('entities.employees.destroy', $employee) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this employee?')">
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
@if($employees->hasPages())
<div class="mt-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Page {{ $employees->currentPage() }} of {{ $employees->lastPage() }}
        </div>
        <div class="flex items-center space-x-1">
            {{ $employees->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endif

@else
<!-- Empty State -->
<div class="text-center py-12">
    <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No employees found</h3>
    <p class="text-gray-500 mb-6">
        @if(request()->hasAny(['search', 'status', 'company_id', 'department']))
            No employees match your current filters. Try adjusting your search criteria.
        @else
            Get started by adding your first employee.
        @endif
    </p>
    <div class="flex justify-center space-x-4">
        @if(request()->hasAny(['search', 'status', 'company_id', 'department']))
        <a href="{{ route('entities.employees.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            <i class="fas fa-times mr-2"></i>Clear Filters
        </a>
        @endif
        <a href="{{ route('entities.employees.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Employee
        </a>
    </div>
</div>
@endif

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    const tableCheckbox = document.getElementById('selectAllTable');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    
    if (tableCheckbox) {
        tableCheckbox.checked = this.checked;
    }
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
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
    const selectedEmployees = Array.from(document.querySelectorAll('.employee-checkbox:checked')).map(cb => cb.value);
    const action = document.getElementById('bulkAction').value;
    
    if (selectedEmployees.length === 0) {
        alert('Please select at least one employee.');
        return;
    }
    
    if (!action) {
        alert('Please select an action to perform.');
        return;
    }
    
    let confirmMessage = `Are you sure you want to perform this action on ${selectedEmployees.length} selected employee(s)?`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Create form for bulk action
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("entities.employees.bulk-action") }}';
    
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
    
    // Add selected employee IDs
    selectedEmployees.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'employee_ids[]';
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
