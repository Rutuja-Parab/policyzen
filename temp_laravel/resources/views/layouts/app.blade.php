<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PolicyZen - Insurance Policy Management')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg fixed h-full">
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
        <div class="flex-1 ml-64">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    <div class="flex items-center space-x-4">
                        @yield('header-actions')
                        <span class="text-sm text-gray-600">Welcome, {{ auth()->user()->name }}</span>
                        <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                            <span class="text-emerald-600 font-semibold text-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
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
    @stack('scripts')
</body>
</html>