@extends('layouts.app')

@section('title', 'Student Details - PolicyZen')
@section('page-title')
    <div class="flex items-center">
        <a href="{{ route('entities.students.index') }}" class="text-gray-400 hover:text-gray-600 mr-3 transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span>Student Details</span>
    </div>
@endsection

@section('header-actions')
    <a href="{{ route('entities.students.edit', $student) }}"
        class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f] transition-colors flex items-center">
        <i class="fas fa-edit mr-2"></i>Edit Student
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-[#04315b] to-[#2b8bd0] p-6 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">{{ $student->name }}</h2>
                            <p class="text-white/80 text-sm">{{ $student->student_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                    <span
                        class="px-3 py-1 text-sm rounded-full font-medium
                        @if ($student->status === 'ACTIVE') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        <i
                            class="fas fa-circle text-xs mr-1
                            @if ($student->status === 'ACTIVE') text-green-500
                            @else text-red-500 @endif"></i>
                        {{ $student->status }}
                    </span>
                </div>

                <!-- Quick Info -->
                <div class="p-6 space-y-4">
                    @if ($student->course)
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Course</p>
                                <p class="font-medium text-gray-900">{{ $student->course }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($student->company)
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-building text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Company</p>
                                <p class="font-medium text-gray-900">{{ $student->company->name }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($student->rank)
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-tie text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Rank</p>
                                <p class="font-medium text-gray-900">{{ $student->rank }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($student->batch)
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Batch</p>
                                <p class="font-medium text-gray-900">{{ $student->batch }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="p-6 bg-gray-50 border-t border-gray-100 space-y-3">
                    <a href="{{ route('entities.students.edit', $student) }}"
                        class="w-full bg-[#f06e11] text-white px-4 py-3 rounded-lg hover:bg-[#f28e1f] transition-colors flex items-center justify-center">
                        <i class="fas fa-edit mr-2"></i>Edit Student
                    </a>
                    <form method="POST" action="{{ route('entities.students.destroy', $student) }}"
                        onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full bg-white text-red-600 px-4 py-3 rounded-lg border border-red-200 hover:bg-red-50 transition-colors flex items-center justify-center">
                            <i class="fas fa-trash mr-2"></i>Delete Student
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="bg-white rounded-xl shadow-lg mt-6 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Information</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-600"><i class="fas fa-calendar-alt mr-2 text-gray-400"></i>Created</span>
                        <span
                            class="font-medium text-gray-900">{{ $student->created_at ? $student->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600"><i class="fas fa-clock mr-2 text-gray-400"></i>Last Updated</span>
                        <span
                            class="font-medium text-gray-900">{{ $student->updated_at ? $student->updated_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Exit Details (Prominent) -->
            @if ($student->date_of_exiting || ($student->premiums && $student->premiums->whereNotNull('date_of_exit')->count() > 0))
                <div class="bg-white rounded-xl shadow-lg border-l-4 border-red-500">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center bg-red-50">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-sign-out-alt text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Exit Details</h3>
                            <p class="text-sm text-gray-600">Student exit information</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if ($student->date_of_exiting)
                                <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                                    <label class="block text-xs font-medium text-red-700 uppercase tracking-wide mb-1">Exit
                                        Date</label>
                                    <p class="text-gray-900 font-medium text-lg">
                                        {{ $student->date_of_exiting->format('M d, Y') }}</p>
                                </div>
                            @endif

                            @if ($student->status === 'INACTIVE')
                                <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                                    <label
                                        class="block text-xs font-medium text-red-700 uppercase tracking-wide mb-1">Status</label>
                                    <p class="text-gray-900 font-medium text-lg">Exited</p>
                                </div>
                            @endif

                            @if ($student->premiums && $student->premiums->whereNotNull('date_of_exit')->count() > 0)
                                @php $exitPremium = $student->premiums->whereNotNull('date_of_exit')->first(); @endphp
                                <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                                    <label
                                        class="block text-xs font-medium text-red-700 uppercase tracking-wide mb-1">Policy
                                        Exit Date</label>
                                    <p class="text-gray-900 font-medium">{{ $exitPremium->date_of_exit->format('M d, Y') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-user text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Full
                                Name</label>
                            <p class="text-gray-900 font-medium">{{ $student->name }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Student
                                ID</label>
                            <p class="text-gray-900 font-medium">{{ $student->student_id }}</p>
                        </div>

                        @if ($student->email)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Email</label>
                                <p class="text-gray-900">{{ $student->email }}</p>
                            </div>
                        @endif

                        @if ($student->phone)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Phone</label>
                                <p class="text-gray-900">{{ $student->phone }}</p>
                            </div>
                        @endif

                        @if ($student->dob)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Date of
                                    Birth</label>
                                <p class="text-gray-900">{{ $student->dob->format('M d, Y') }}</p>
                            </div>
                        @endif

                        @if ($student->age)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Age</label>
                                <p class="text-gray-900">{{ $student->age }} years</p>
                            </div>
                        @endif

                        @if ($student->gender)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Gender</label>
                                <p class="text-gray-900">{{ $student->gender }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Academic & Employment Information -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center">
                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-graduation-cap text-teal-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Academic & Employment Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if ($student->course)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Course</label>
                                <p class="text-gray-900 font-medium">{{ $student->course }}</p>
                            </div>
                        @endif

                        @if ($student->rank)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Rank</label>
                                <p class="text-gray-900 font-medium">{{ $student->rank }}</p>
                            </div>
                        @endif

                        @if ($student->batch)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Batch</label>
                                <p class="text-gray-900 font-medium">{{ $student->batch }}</p>
                            </div>
                        @endif

                        @if ($student->company)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label
                                    class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Company</label>
                                <p class="text-gray-900 font-medium">{{ $student->company->name }}</p>
                            </div>
                        @endif

                        @if ($student->date_of_joining)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Date of
                                    Joining</label>
                                <p class="text-gray-900">{{ $student->date_of_joining->format('M d, Y') }}</p>
                            </div>
                        @endif

                        @if ($student->date_of_exiting)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Date of
                                    Exiting</label>
                                <p class="text-gray-900">{{ $student->date_of_exiting->format('M d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Policy & Premium Information -->
            @if ($student->premiums && $student->premiums->count() > 0)
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-contract text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Policy & Premium Details</h3>
                    </div>
                    <div class="p-6">
                        @foreach ($student->premiums as $premium)
                            <div class="mb-6 last:mb-0">
                                @if ($premium->policy)
                                    <div
                                        class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-6 border border-green-100 mb-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <h4 class="font-semibold text-gray-900 text-lg">
                                                    {{ $premium->policy->policy_number }}</h4>
                                                <p class="text-sm text-gray-600">{{ $premium->policy->insurance_type }} -
                                                    {{ $premium->policy->provider }}</p>
                                            </div>
                                            <span
                                                class="px-3 py-1 text-sm rounded-full font-medium
                                                @if ($premium->policy->status === 'ACTIVE') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $premium->policy->status }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Start Date</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $premium->policy->start_date->format('M d, Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">End Date</p>
                                                <p class="font-medium text-gray-900">
                                                    {{ $premium->policy->end_date->format('M d, Y') }}</p>
                                            </div>
                                            @if ($premium->sum_insured)
                                                <div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Sum Insured
                                                    </p>
                                                    <p class="font-medium text-gray-900">
                                                        ₹{{ number_format($premium->sum_insured, 2) }}</p>
                                                </div>
                                            @endif
                                            @if ($premium->rate)
                                                <div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Rate</p>
                                                    <p class="font-medium text-gray-900">{{ $premium->rate }}%</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Premium Breakdown -->
                                    <div class="bg-gray-50 rounded-lg p-6">
                                        <h5 class="font-medium text-gray-900 mb-4">Premium Breakdown</h5>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-600">Annual Premium</span>
                                                <span
                                                    class="font-medium text-gray-900">₹{{ number_format($premium->annual_premium, 2) }}</span>
                                            </div>
                                            @if ($premium->pro_rata_days)
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">Pro Rata Days</span>
                                                    <span class="font-medium text-gray-900">{{ $premium->pro_rata_days }}
                                                        days</span>
                                                </div>
                                            @endif
                                            @if ($premium->prorata_premium)
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">Pro Rata Premium</span>
                                                    <span
                                                        class="font-medium text-gray-900">₹{{ number_format($premium->prorata_premium, 2) }}</span>
                                                </div>
                                            @endif
                                            @if ($premium->gst_rate)
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">GST ({{ $premium->gst_rate }}%)</span>
                                                    <span
                                                        class="font-medium text-gray-900">₹{{ number_format($premium->gst_amount, 2) }}</span>
                                                </div>
                                            @endif
                                            @if ($premium->final_premium)
                                                <div
                                                    class="flex justify-between items-center pt-3 border-t border-gray-200">
                                                    <span class="text-lg font-semibold text-gray-900">Final Premium</span>
                                                    <span
                                                        class="text-lg font-bold text-green-600">₹{{ number_format($premium->final_premium, 2) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gray-50 rounded-lg p-6">
                                        <p class="text-gray-600">Premium record found but policy information not available.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- No Policy Info -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-contract text-gray-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Policy & Premium Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="text-center py-8">
                            <i class="fas fa-file-contract text-gray-300 text-5xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Policy Information</h3>
                            <p class="text-gray-500 mb-4">This student does not have any policy or premium information yet.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
