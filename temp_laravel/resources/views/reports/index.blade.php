<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - PolicyZen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">PolicyZen</h1>
                </div>

                <nav class="space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    
                    
                    <!-- Entities Dropdown -->
                    <div class="relative">
                        @php
                        $isEntitiesActive = request()->routeIs('entities.employees.*', 'entities.students.*', 'entities.vessels.*', 'entities.vehicles.*', 'entities.index');
                        $isEntitiesOpen = $isEntitiesActive ? 'block' : 'hidden';
                        @endphp
                        <button onclick="toggleEntitiesDropdown()" class="w-full flex items-center justify-between px-4 py-3 {{ $isEntitiesActive ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-users"></i>
                                <span>Entities</span>
                            </div>
                            <i id="entities-chevron" class="fas fa-chevron-down text-xs transition-transform {{ $isEntitiesActive ? 'rotate-180' : '' }}"></i>
                        </button>
                        <div id="entities-dropdown" class="mt-2 ml-4 space-y-1 border-l-2 border-gray-200 pl-4 {{ $isEntitiesOpen }}">
                            <a href="{{ route('entities.employees.index') }}" class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('entities.employees.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                                <i class="fas fa-user-tie text-sm"></i>
                                <span class="text-sm">Employees</span>
                            </a>
                            <a href="{{ route('entities.students.index') }}" class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('entities.students.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                                <i class="fas fa-graduation-cap text-sm"></i>
                                <span class="text-sm">Students</span>
                            </a>
                            <a href="{{ route('entities.vessels.index') }}" class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('entities.vessels.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                                <i class="fas fa-ship text-sm"></i>
                                <span class="text-sm">Vessels</span>
                            </a>
                            <a href="{{ route('entities.vehicles.index') }}" class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('entities.vehicles.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                                <i class="fas fa-car text-sm"></i>
                                <span class="text-sm">Vehicles</span>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('policies.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('policies.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                        <i class="fas fa-file-alt"></i>
                        <span>Policies</span>
                    </a>
                    <a href="{{ route('endorsements.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('endorsements.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                        <i class="fas fa-edit"></i>
                        <span>Endorsements</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('search.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('search.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </a>
                    <!-- Master Dropdown -->
                    <div class="relative">
                        @php
                        $isMasterActive = request()->routeIs('companies.*', 'entities.courses.*');
                        $isMasterOpen = $isMasterActive ? 'block' : 'hidden';
                        @endphp
                        <button onclick="toggleMasterDropdown()" class="w-full flex items-center justify-between px-4 py-3 {{ $isMasterActive ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-cog"></i>
                                <span>Master</span>
                            </div>
                            <i id="master-chevron" class="fas fa-chevron-down text-xs transition-transform {{ $isMasterActive ? 'rotate-180' : '' }}"></i>
                        </button>
                        <div id="master-dropdown" class="mt-2 ml-4 space-y-1 border-l-2 border-gray-200 pl-4 {{ $isMasterOpen }}">
                            <a href="{{ route('companies.index') }}" class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('companies.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                                <i class="fas fa-building text-sm"></i>
                                <span class="text-sm">Companies</span>
                            </a>
                            <a href="{{ route('entities.courses.index') }}" class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('entities.courses.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg">
                                <i class="fas fa-book text-sm"></i>
                                <span class="text-sm">Courses</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- User info and logout -->
            <div class="absolute bottom-0 w-64 p-4">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                        <span class="text-emerald-600 font-semibold text-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Welcome, {{ auth()->user()->name }}</span>
                        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                            <span class="text-emerald-600 font-semibold text-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Policy Summary -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Policy Summary</h3>
                            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Policies</span>
                                <span class="font-semibold">{{ $stats['policies']['total'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Policies</span>
                                <span class="font-semibold text-green-600">{{ $stats['policies']['active'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Expired Policies</span>
                                <span class="font-semibold text-red-600">{{ $stats['policies']['expired'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Premium Analysis -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Premium Analysis</h3>
                            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Premium</span>
                                <span class="font-semibold">${{ number_format($stats['premium']['total'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Average Premium</span>
                                <span class="font-semibold">${{ number_format($stats['premium']['average'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Monthly Revenue</span>
                                <span class="font-semibold">${{ number_format($stats['premium']['monthly'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Entity Overview -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Entity Overview</h3>
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Entities</span>
                                <span class="font-semibold">{{ $stats['entities']['total'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Employees</span>
                                <span class="font-semibold">{{ $stats['entities']['employees'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Students</span>
                                <span class="font-semibold">{{ $stats['entities']['students'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Policy Status Distribution -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Policy Status Distribution</h3>
                        <div class="h-64">
                            <canvas id="policyStatusChart"></canvas>
                        </div>
                    </div>

                    <!-- Premium by Insurance Type -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Premium by Insurance Type</h3>
                        <div class="h-64">
                            <canvas id="premiumTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="mt-8 bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Export Reports</h3>
                    <div class="flex space-x-4">
                        <button onclick="exportReport('pdf')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-download mr-2"></i>Export to PDF
                        </button>
                        <button onclick="exportReport('excel')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-file-excel mr-2"></i>Export to Excel
                        </button>
                        <button onclick="exportReport('csv')" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                            <i class="fas fa-file-csv mr-2"></i>Export to CSV
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        function initializeCharts() {
            // Policy Status Chart
            const policyStatusCtx = document.getElementById('policyStatusChart').getContext('2d');
            const policyStatusData = @json($stats['charts']['policyStatusDistribution']);
            
            new Chart(policyStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: policyStatusData.map(item => item.status),
                    datasets: [{
                        data: policyStatusData.map(item => item.count),
                        backgroundColor: [
                            '#10B981', // Green for Active
                            '#EF4444', // Red for Expired
                            '#F59E0B', // Yellow for Pending
                            '#6B7280'  // Gray for others
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });

            // Premium by Type Chart
            const premiumTypeCtx = document.getElementById('premiumTypeChart').getContext('2d');
            const premiumTypeData = @json($stats['charts']['premiumByType']);
            
            new Chart(premiumTypeCtx, {
                type: 'bar',
                data: {
                    labels: premiumTypeData.map(item => item.insurance_type || 'Unknown'),
                    datasets: [{
                        label: 'Premium Amount',
                        data: premiumTypeData.map(item => parseFloat(item.total_premium) || 0),
                        backgroundColor: '#3B82F6',
                        borderColor: '#1D4ED8',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '
</body>
</html> + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function exportReport(format) {
            window.open(`/reports/export/${format}`, '_blank');
        }

        function toggleMasterDropdown() {
            const dropdown = document.getElementById('master-dropdown');
            const chevron = document.getElementById('master-chevron');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                dropdown.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        }

        function toggleEntitiesDropdown() {
            const dropdown = document.getElementById('entities-dropdown');
            const chevron = document.getElementById('entities-chevron');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                dropdown.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        }
    </script>
</body>
</html>