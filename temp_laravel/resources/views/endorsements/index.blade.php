@extends('layouts.app')

@section('title', 'Policy Endorsements - PolicyZen')
@section('page-title', 'Policy Endorsements')

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

                @if($endorsements->count() === 0)
                <div class="text-center py-12">
                    <i class="fas fa-edit text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No endorsements found</h3>
                    <p class="text-gray-500 mb-4">Policy endorsements will appear here</p>
                    <a href="{{ route('endorsements.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Create First Endorsement
                    </a>
                </div>
                @else
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-medium text-gray-900">All Endorsements</h2>
                            <span class="text-sm text-gray-600">{{ $endorsements->total() }} total endorsements</span>
                        </div>

                        <div class="space-y-4">
                            @foreach($endorsements as $endorsement)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4 mb-2">
                                            <h3 class="font-semibold text-gray-900">{{ $endorsement->endorsement_number }}</h3>
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                {{ $endorsement->policy->policy_number ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <p class="text-gray-600 mb-2">{{ $endorsement->description }}</p>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span><i class="fas fa-calendar mr-1"></i>Effective: {{ $endorsement->effective_date->format('M d, Y') }}</span>
                                            <span><i class="fas fa-user mr-1"></i>Created by: {{ $endorsement->creator->name ?? 'Unknown' }}</span>
                                            <span><i class="fas fa-clock mr-1"></i>{{ $endorsement->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('endorsements.show', $endorsement) }}" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-200">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('endorsements.edit', $endorsement) }}" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('endorsements.destroy', $endorsement) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this endorsement?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($endorsements->hasPages())
                        <div class="mt-6">
                            {{ $endorsements->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif
@endsection