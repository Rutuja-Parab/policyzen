<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - PolicyZen</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <h1 class="text-2xl font-bold text-gray-900">Advanced Search</h1>
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
                <!-- Search Form -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Search Across All Records</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <input type="text" id="search-query" placeholder="Enter search terms..."
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <select id="search-type" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">All Types</option>
                                    <option value="policies">Policies</option>
                                    <option value="employees">Employees</option>
                                    <option value="students">Students</option>
                                    <option value="vessels">Vessels</option>
                                    <option value="vehicles">Vehicles</option>
                                    <option value="endorsements">Endorsements</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button id="search-btn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="search-results" class="hidden">
                    <!-- Policies Results -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Policies <span id="policies-count" class="text-sm text-gray-500">(0 results)</span></h3>
                        </div>
                        <div id="policies-results" class="p-6">
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-file-alt text-3xl mb-2"></i>
                                <p>No policy results found</p>
                            </div>
                        </div>
                    </div>

                    <!-- Entities Results -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Entities <span id="entities-count" class="text-sm text-gray-500">(0 results)</span></h3>
                        </div>
                        <div id="entities-results" class="p-6">
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-users text-3xl mb-2"></i>
                                <p>No entity results found</p>
                            </div>
                        </div>
                    </div>

                    <!-- Endorsements Results -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Endorsements <span id="endorsements-count" class="text-sm text-gray-500">(0 results)</span></h3>
                        </div>
                        <div id="endorsements-results" class="p-6">
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-edit text-3xl mb-2"></i>
                                <p>No endorsement results found</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Search Yet -->
                <div id="no-search" class="text-center py-16">
                    <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Search PolicyZen</h3>
                    <p class="text-gray-500 mb-6">Enter keywords to search across policies, entities, and endorsements</p>
                    <div class="text-sm text-gray-400">
                        <p class="mb-1">Try searching for:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <span class="bg-gray-100 px-3 py-1 rounded-full">Policy numbers</span>
                            <span class="bg-gray-100 px-3 py-1 rounded-full">Employee names</span>
                            <span class="bg-gray-100 px-3 py-1 rounded-full">Insurance providers</span>
                            <span class="bg-gray-100 px-3 py-1 rounded-full">Entity codes</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.getElementById('search-btn').addEventListener('click', async function() {
            const query = document.getElementById('search-query').value.trim();
            const type = document.getElementById('search-type').value;

            if (!query) {
                alert('Please enter a search term');
                return;
            }

            const btn = document.getElementById('search-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';
            btn.disabled = true;

            try {
                const params = new URLSearchParams({ q: query });
                if (type) params.append('type', type);

                const response = await fetch(`/api/search?${params}`);
                const data = await response.json();

                displayResults(data);
                document.getElementById('search-results').classList.remove('hidden');
                document.getElementById('no-search').classList.add('hidden');

            } catch (error) {
                console.error('Search error:', error);
                alert('Search failed. Please try again.');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        function displayResults(data) {
            // Display policies
            const policiesContainer = document.getElementById('policies-results');
            const policiesCount = document.getElementById('policies-count');

            if (data.policies && data.policies.length > 0) {
                policiesCount.textContent = `(${data.policies.length} results)`;
                policiesContainer.innerHTML = data.policies.map(policy => `
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">${policy.policy_number}</h4>
                                <p class="text-sm text-gray-600">${policy.insurance_type} • ${policy.provider}</p>
                                <p class="text-sm text-gray-500">Premium: $${policy.premium_amount}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full ${policy.status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${policy.status}
                            </span>
                        </div>
                    </div>
                `).join('');
            } else {
                policiesCount.textContent = '(0 results)';
                policiesContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-file-alt text-3xl mb-2"></i>
                        <p>No policy results found</p>
                    </div>
                `;
            }

            // Display entities
            const entitiesContainer = document.getElementById('entities-results');
            const entitiesCount = document.getElementById('entities-count');

            if (data.entities && data.entities.length > 0) {
                entitiesCount.textContent = `(${data.entities.length} results)`;
                entitiesContainer.innerHTML = data.entities.map(entity => `
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">${entity.name || entity.vessel_name || 'N/A'}</h4>
                                <p class="text-sm text-gray-600">${entity.employee_code || entity.student_id || entity.imo_number || entity.registration_number || 'N/A'}</p>
                                <p class="text-sm text-gray-500">${entity.department || entity.course || entity.vessel_type || entity.make || 'N/A'}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full ${entity.status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${entity.status}
                            </span>
                        </div>
                    </div>
                `).join('');
            } else {
                entitiesCount.textContent = '(0 results)';
                entitiesContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-users text-3xl mb-2"></i>
                        <p>No entity results found</p>
                    </div>
                `;
            }

            // Display endorsements
            const endorsementsContainer = document.getElementById('endorsements-results');
            const endorsementsCount = document.getElementById('endorsements-count');

            if (data.endorsements && data.endorsements.length > 0) {
                endorsementsCount.textContent = `(${data.endorsements.length} results)`;
                endorsementsContainer.innerHTML = data.endorsements.map(endorsement => `
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">${endorsement.endorsement_number}</h4>
                                <p class="text-sm text-gray-600">${endorsement.description}</p>
                                <p class="text-sm text-gray-500">Effective: ${new Date(endorsement.effective_date).toLocaleDateString()}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                endorsementsCount.textContent = '(0 results)';
                endorsementsContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-edit text-3xl mb-2"></i>
                        <p>No endorsement results found</p>
                    </div>
                `;
            }
        }

        // Allow search on Enter key
        document.getElementById('search-query').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('search-btn').click();
            }
        });
    </script>

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
</body>
</html>