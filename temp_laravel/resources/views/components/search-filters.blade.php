<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <form method="GET" action="{{ $action ?? request()->url() }}" class="space-y-4">
            <!-- Search Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="{{ $searchPlaceholder ?? 'Search by name, number, description...' }}"
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                @isset($dateRange)
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" 
                           id="date_from" 
                           name="date_from" 
                           value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" 
                           id="date_to" 
                           name="date_to" 
                           value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                </div>
                @endisset

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f] transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </div>

            <!-- Filter Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                {{ $slot }}
                
                <div class="flex items-end space-x-2">
                    <a href="{{ request()->url() }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                    
                    <button type="button" onclick="exportResults()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function exportResults() {
    const url = new URL(window.location.href);
    url.searchParams.set('export', 'csv');
    window.open(url.toString(), '_blank');
}
</script>
