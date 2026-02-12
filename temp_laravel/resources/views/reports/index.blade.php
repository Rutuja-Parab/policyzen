@extends('layouts.app')

@section('title', 'Reports - PolicyZen')
@section('page-title', 'Reports & Analytics')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Policy Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Policy Summary</h3>
                <i class="fas fa-file-alt text-[#f06e11] text-xl"></i>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Policies</span>
                    <span class="font-semibold">{{ $stats['policies']['total'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Policies</span>
                    <span class="font-semibold text-[#f06e11]">{{ $stats['policies']['active'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Expired Policies</span>
                    <span class="font-semibold text-[#04315b]">{{ $stats['policies']['expired'] }}</span>
                </div>
            </div>
        </div>

        <!-- Premium Analysis -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Premium Analysis</h3>
                <i class="fas fa-dollar-sign text-[#f06e11] text-xl"></i>
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
                <i class="fas fa-users text-[#f06e11] text-xl"></i>
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Entity Type Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Entity Type Distribution</h3>
            <div class="h-64">
                <canvas id="entityTypeChart"></canvas>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Monthly Endorsement Trends</h3>
            <div class="h-64">
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Policy Status Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Policy Status Distribution</h3>
            <div class="h-64">
                <canvas id="policyStatusChart"></canvas>
            </div>
        </div>

        <!-- Companies by Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Companies by Status</h3>
            <div class="h-64">
                <canvas id="companyStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Export Reports</h3>
        <div class="flex space-x-4">
            <button onclick="exportReport('pdf')" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                <i class="fas fa-download mr-2"></i>Export to PDF
            </button>
            <button onclick="exportReport('excel')" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                <i class="fas fa-file-excel mr-2"></i>Export to Excel
            </button>
            <button onclick="exportReport('csv')" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                <i class="fas fa-file-csv mr-2"></i>Export to CSV
            </button>
        </div>
    </div>
@endsection

<script>
    // Initialize charts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Entity Type Distribution Chart
        const entityTypeCtx = document.getElementById('entityTypeChart');
        if (entityTypeCtx) {
            const entityData = @json($stats['charts']['entityDistribution']);

            const entityLabels = Object.keys(entityData).filter(key => entityData[key] > 0);
            const entityValues = entityLabels.map(key => entityData[key] || 0);

            new Chart(entityTypeCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: entityLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                    datasets: [{
                        data: entityValues,
                        backgroundColor: [
                            '#3b82f6', // Blue for employees
                            '#10b981', // Green for students
                            '#f59e0b', // Yellow for vessels
                            '#ef4444', // Red for vehicles
                            '#8b5cf6', // Purple
                            '#ec4899', // Pink
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
        }

        // Monthly Trends Chart
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
        if (monthlyTrendsCtx) {
            const trendsData = @json($stats['charts']['monthlyTrends']);

            const trendLabels = trendsData.map(item => item.month);
            const trendValues = trendsData.map(item => parseFloat(item.premium) || 0);

            new Chart(monthlyTrendsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Premium',
                        data: trendValues,
                        borderColor: '#f06e11',
                        backgroundColor: 'rgba(240, 110, 17, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
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

        // Policy Status Chart
        const policyStatusCtx = document.getElementById('policyStatusChart');
        if (policyStatusCtx) {
            const policyStatusData = @json($stats['charts']['policyStatusDistribution']);

            const policyStatusLabels = policyStatusData.length > 0 ?
                policyStatusData.map(item => item.status) :
                ['No Data'];
            const policyStatusValues = policyStatusData.length > 0 ?
                policyStatusData.map(item => item.count) :
                [1];

            new Chart(policyStatusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: policyStatusLabels,
                    datasets: [{
                        data: policyStatusValues,
                        backgroundColor: [
                            '#f06e11', // Orange for Active
                            '#04315b', // Dark Blue for Expired
                            '#2b8bd0', // Light Blue for Pending
                            '#6B7280' // Gray for others
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
        }

        // Company Status Chart
        const companyStatusCtx = document.getElementById('companyStatusChart');
        if (companyStatusCtx) {
            const companyData = @json($stats['companies']);

            new Chart(companyStatusCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Total Companies', 'Active Companies'],
                    datasets: [{
                        label: 'Count',
                        data: [companyData.total, companyData.active],
                        backgroundColor: ['#3b82f6', '#10b981'],
                        borderColor: ['#2563eb', '#059669'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
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
    }

    function exportReport(format) {
        window.open(`/reports/export/${format}`, '_blank');
    }
</script>
