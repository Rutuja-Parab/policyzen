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
                    backgroundColor: '#f06e11',
                    borderColor: '#f28e1f',
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
                                return '$' + value.toLocaleString();
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
</script>
