@extends('layouts.app')

@section('title', 'Manage Students - ' . $policy->policy_number)

@section('page-title')
<div class="flex items-center">
    <a href="{{ route('policies.show', $policy) }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Manage Students</span>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-600">Policy: {{ $policy->policy_number }} | {{ $policy->insurance_type }}</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        @if(session('pdf_path'))
        <a href="{{ route('policies.students.endorsement.download', [$policy, session('endorsement')->id]) }}" 
           class="ml-4 text-green-800 underline font-medium">
            <i class="fas fa-download mr-1"></i>Download Endorsement PDF
        </a>
        @endif
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <!-- Policy Summary Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500">Sum Insured</p>
                <p class="text-2xl font-bold text-gray-900">₹{{ number_format($policy->sum_insured, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Premium Amount</p>
                <p class="text-2xl font-bold text-gray-900">₹{{ number_format($policy->premium_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Students Covered</p>
                <p class="text-2xl font-bold text-[#f06e11]">{{ $attachedStudents->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Policy Status</p>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                    {{ $policy->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $policy->status }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <!-- Add Students Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-user-plus text-green-600 mr-2"></i>Add Students
                </h2>
                <p class="text-sm text-gray-500 mt-1">Select students to add to this health policy</p>
            </div>
            <form action="{{ route('policies.students.add', $policy) }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Premium per Student (₹)</label>
                        <input type="number" name="premium_per_student" step="0.01" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-blue-500"
                            placeholder="Enter premium amount">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Students</label>
                        <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto">
                            @forelse($availableStudents as $student)
                            <label class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" 
                                    class="w-4 h-4 text-[#f06e11] border-gray-300 rounded focus:ring-[#2b8bd0]">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->student_id }} | {{ $student->course }}</p>
                                </div>
                            </label>
                            @empty
                            <p class="px-4 py-8 text-center text-gray-500">No available students to add</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <button type="button" onclick="selectAllAvailable()" class="text-sm text-[#f06e11] hover:text-blue-800">
                            Select All
                        </button>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                            <i class="fas fa-plus mr-2"></i>Add Selected Students
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Remove Students Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-user-minus text-red-600 mr-2"></i>Remove Students
                </h2>
                <p class="text-sm text-gray-500 mt-1">Select students to remove from this health policy</p>
            </div>
            <form action="{{ route('policies.students.remove', $policy) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Refund per Student (₹) *</label>
                        <input type="text" name="refund_per_student" step="0.01" min="0" required
                               pattern="^[0-9]+(?:\.[0-9]{1,2})?$"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-blue-500"
                               placeholder="Enter refund amount">
                        <p class="text-xs text-gray-500 mt-1">Enter amount in Indian Rupees (e.g., 2500.00)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Removal Reason *</label>
                        <select name="removal_reason" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-blue-500">
                            <option value="">Select removal reason</option>
                            <option value="Course Completion">Course Completion</option>
                            <option value="Transfer to Another Institute">Transfer to Another Institute</option>
                            <option value="Medical Reasons">Medical Reasons</option>
                            <option value="Discontinuation">Discontinuation</option>
                            <option value="Leave of Absence">Leave of Absence</option>
                            <option value="Academic Issues">Academic Issues</option>
                            <option value="Other">Other (specify in remarks)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                        <select name="document_type" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-blue-500">
                            <option value="REMOVAL_CERTIFICATE">Removal Certificate</option>
                            <option value="MEDICAL_CERTIFICATE">Medical Certificate</option>
                            <option value="TRANSFER_LETTER">Transfer Letter</option>
                            <option value="RESIGNATION_LETTER">Resignation Letter</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Supporting Documents</label>
                        <input type="file" name="endorsement_documents[]" multiple 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Upload supporting documents (PDF, DOC, DOCX, JPG, PNG - Max 10MB each)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currently Covered Students *</label>
                        <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto">
                            @forelse($attachedStudents as $student)
                            <label class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" 
                                    class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->student_id }} | {{ $student->course }}</p>
                                </div>
                            </label>
                            @empty
                            <p class="px-4 py-8 text-center text-gray-500">No students currently covered</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <button type="button" onclick="selectAllAttached()" class="text-sm text-red-600 hover:text-red-800">
                            Select All
                        </button>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                            onclick="return validateRemovalForm()">
                            <i class="fas fa-minus mr-2"></i>Remove Selected Students
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history text-[#f06e11] mr-2"></i>Recent Activity
                </h2>
                <p class="text-sm text-gray-500 mt-1">Transaction history for student additions and removals</p>
            </div>
            <a href="{{ route('policies.students.audit-logs', $policy) }}" class="text-[#f06e11] hover:text-blue-800 text-sm">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endorsement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($auditLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $log->action === 'ADD_STUDENT' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $log->action === 'ADD_STUDENT' ? 'Added' : 'Removed' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $log->metadata['student_name'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-medium {{ $log->transaction_type === 'DEBIT' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $log->transaction_type === 'DEBIT' ? '-' : '+' }}₹{{ number_format($log->amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $log->transaction_type === 'DEBIT' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $log->transaction_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($log->endorsement)
                            <a href="{{ route('policies.students.endorsement.download', [$policy, $log->endorsement_id]) }}" 
                               class="text-[#f06e11] hover:text-blue-800">
                                <i class="fas fa-file-pdf mr-1"></i>{{ $log->metadata['endorsement_number'] ?? 'Download' }}
                            </a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No activity recorded yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($auditLogs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Currency formatting function
function formatCurrency(input) {
    let value = input.value.replace(/[^0-9.]/g, '');
    let parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    if (parts[1] && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    input.value = value;
}

// Real-time validation
function validateCurrency(input) {
    const currencyRegex = /^[0-9]+(?:\.[0-9]{1,2})?$/;
    const isValid = currencyRegex.test(input.value) && parseFloat(input.value) > 0;
    toggleFieldValidation(input, isValid, 'Please enter a valid amount greater than 0');
    return isValid;
}

function toggleFieldValidation(input, isValid, message) {
    const field = input.closest('div');
    let errorDiv = field.querySelector('.field-error');
    
    if (!isValid && input.value) {
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error text-red-500 text-xs mt-1';
            field.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        input.classList.add('border-red-500');
        input.classList.remove('border-green-500');
    } else if (isValid && input.value) {
        if (errorDiv) {
            errorDiv.remove();
        }
        input.classList.remove('border-red-500');
        input.classList.add('border-green-500');
    } else {
        if (errorDiv) {
            errorDiv.remove();
        }
        input.classList.remove('border-red-500', 'border-green-500');
    }
}

// Form validation before submission
function validateRemovalForm() {
    let isValid = true;
    
    // Validate required fields
    const refundField = document.querySelector('input[name="refund_per_student"]');
    const reasonField = document.querySelector('select[name="removal_reason"]');
    const studentCheckboxes = document.querySelector('form[action*="remove"] input[name="student_ids[]"]:checked');
    
    if (!refundField.value || !validateCurrency(refundField)) {
        isValid = false;
        refundField.classList.add('border-red-500');
    }
    
    if (!reasonField.value) {
        isValid = false;
        reasonField.classList.add('border-red-500');
    } else {
        reasonField.classList.remove('border-red-500');
    }
    
    if (!studentCheckboxes) {
        isValid = false;
        alert('Please select at least one student to remove');
    }
    
    if (!isValid) {
        alert('Please fill in all required fields and select students to remove');
    }
    
    return isValid;
}

function selectAllAvailable() {
    document.querySelectorAll('input[name="student_ids[]"]').forEach((checkbox, index) => {
        if (index < document.querySelectorAll('.bg-white:first-of-type input[name="student_ids[]"]').length) {
            checkbox.checked = true;
        }
    });
}

function selectAllAttached() {
    const form = document.querySelector('form[action*="remove"]');
    form.querySelectorAll('input[name="student_ids[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Initialize form validation
document.addEventListener('DOMContentLoaded', function() {
    // Currency field validation
    const refundField = document.querySelector('input[name="refund_per_student"]');
    if (refundField) {
        refundField.addEventListener('input', function() {
            formatCurrency(this);
            validateCurrency(this);
        });
        refundField.addEventListener('blur', function() {
            validateCurrency(this);
        });
    }
    
    // Required field validation
    const reasonField = document.querySelector('select[name="removal_reason"]');
    if (reasonField) {
        reasonField.addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('border-red-500');
                this.classList.add('border-green-500');
            } else {
                this.classList.remove('border-green-500');
            }
        });
    }
});
</script>
@endpush
@endsection
