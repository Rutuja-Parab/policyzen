@extends('layouts.app')

@section('title', 'Audit Logs - ' . $policy->policy_number)

@section('page-title')
<div class="flex items-center">
    <a href="{{ route('policies.students.index', $policy) }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Audit Logs</span>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-600">Policy: {{ $policy->policy_number }} | Student Transaction History</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $logs->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-plus text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Students Added</p>
                    <p class="text-2xl font-bold text-green-600">{{ $logs->where('action', 'ADD_STUDENT')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-minus text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Students Removed</p>
                    <p class="text-2xl font-bold text-red-600">{{ $logs->where('action', 'REMOVE_STUDENT')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Endorsements</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $logs->unique('endorsement_id')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history text-blue-600 mr-2"></i>Complete Transaction History
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance Before</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endorsement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>{{ $log->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $log->action === 'ADD_STUDENT' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas {{ $log->action === 'ADD_STUDENT' ? 'fa-user-plus' : 'fa-user-minus' }} mr-1"></i>
                                {{ $log->action === 'ADD_STUDENT' ? 'Added' : 'Removed' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $log->metadata['student_name'] ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $log->entity_id }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium {{ $log->transaction_type === 'DEBIT' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $log->transaction_type === 'DEBIT' ? '-' : '+' }}₹{{ number_format($log->amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $log->transaction_type === 'DEBIT' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $log->transaction_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($log->balance_before, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($log->balance_after, 2) }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($log->endorsement)
                            <a href="{{ route('policies.students.endorsement.download', [$policy, $log->endorsement_id]) }}" 
                               class="text-blue-600 hover:text-blue-800 flex items-center">
                                <i class="fas fa-file-pdf mr-1"></i>
                                <span class="truncate max-w-[120px]">{{ $log->metadata['endorsement_number'] ?? 'Download' }}</span>
                            </a>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($log->performer)
                            {{ $log->performer->name }}
                            @else
                            <span class="text-gray-400">System</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                            <p>No audit logs found for this policy</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
