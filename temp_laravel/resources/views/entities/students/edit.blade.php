@extends('layouts.app')

@section('title', 'Edit Student - PolicyZen')
@section('page-title')
<div class="flex items-center">
    <a href="{{ route('entities.students.show', $student) }}" class="text-gray-400 hover:text-gray-600 mr-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <span>Edit Student</span>
</div>
@endsection

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-lg shadow">
    <div class="p-6">
        <form method="POST" action="{{ route('entities.students.update', $student) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Student ID *</label>
                    <input type="text" name="student_id" value="{{ old('student_id', $student->student_id) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $student->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Course (optional)</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id', $selectedCourseId ?? null) == $course->id ? 'selected' : '' }}>
                            {{ $course->course_name }} ({{ $course->course_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company *</label>
                    <select name="company_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $student->company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" required 
                           pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                           value="{{ old('email', $student->email) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                           placeholder="student@example.com">
                    <p class="text-xs text-gray-500 mt-1">Enter a valid email address (e.g., student@company.com)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                    <input type="tel" name="phone" required 
                           pattern="^\+91[0-9]{10}$"
                           value="{{ old('phone', $student->phone) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                           placeholder="+919876543210"
                           maxlength="13">
                    <p class="text-xs text-gray-500 mt-1">Enter 10-digit mobile number with +91 prefix (e.g., +919876543210)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $student->dob ? $student->dob->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                    <input type="number" name="age" value="{{ old('age', $student->age) }}" min="1" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="25">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                    <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $student->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $student->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $student->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rank</label>
                    <input type="text" name="rank" value="{{ old('rank', $student->rank) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="1">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="ACTIVE" {{ old('status', $student->status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ old('status', $student->status) === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="{{ route('entities.students.show', $student) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update Student
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Phone number formatting function
function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    input.value = value;
}

// Real-time validation functions
function validateEmail(input) {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const isValid = emailRegex.test(input.value);
    toggleFieldValidation(input, isValid, 'Please enter a valid email address');
    return isValid;
}

function validatePhone(input) {
    const phoneRegex = /^\+91[0-9]{10}$/;
    const value = input.value;
    let isValid = false;
    let message = 'Please enter 10 digits';
    
    if (value.startsWith('+91') && phoneRegex.test(value)) {
        isValid = true;
    } else if (value.length === 10 && /^[0-9]{10}$/.test(value)) {
        input.value = '+91' + value;
        isValid = true;
    } else if (value.length < 10) {
        message = 'Phone number must be 10 digits';
    } else if (!value.startsWith('+91')) {
        message = 'Phone number must start with +91';
    }
    
    toggleFieldValidation(input, isValid, message);
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

function validateForm() {
    let isValid = true;
    
    // Validate required fields
    const requiredFields = document.querySelectorAll('input[required], select[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        }
    });
    
    // Validate email
    const emailField = document.querySelector('input[type="email"]');
    if (emailField && emailField.value) {
        if (!validateEmail(emailField)) isValid = false;
    }
    
    // Validate phone
    const phoneField = document.querySelector('input[type="tel"]');
    if (phoneField && phoneField.value) {
        if (!validatePhone(phoneField)) isValid = false;
    }
    
    // Validate student ID format
    const studentIdField = document.querySelector('input[name="student_id"]');
    if (studentIdField) {
        const studentIdRegex = /^[A-Z0-9]{3,20}$/;
        if (!studentIdRegex.test(studentIdField.value)) {
            toggleFieldValidation(studentIdField, false, 'Student ID must be 3-20 characters (A-Z, 0-9 only)');
            isValid = false;
        } else {
            toggleFieldValidation(studentIdField, true, '');
        }
    }
    
    // Validate age
    const ageField = document.querySelector('input[name="age"]');
    if (ageField && ageField.value) {
        const age = parseInt(ageField.value);
        if (age < 1 || age > 100) {
            toggleFieldValidation(ageField, false, 'Age must be between 1 and 100');
            isValid = false;
        } else {
            toggleFieldValidation(ageField, true, '');
        }
    }
    
    return isValid;
}

// Add event listeners for real-time validation
document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value) validateEmail(this);
        });
        field.addEventListener('input', function() {
            if (this.classList.contains('border-red-500')) {
                validateEmail(this);
            }
        });
    });
    
    // Phone validation
    const phoneFields = document.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        field.addEventListener('input', function() {
            formatPhone(this);
            if (this.classList.contains('border-red-500') || this.value.startsWith('+91')) {
                validatePhone(this);
            }
        });
        field.addEventListener('blur', function() {
            if (this.value && !this.value.startsWith('+91')) {
                this.value = '+91' + this.value;
            }
            validatePhone(this);
        });
    });
    
    // Student ID validation
    const studentIdFields = document.querySelectorAll('input[name="student_id"]');
    studentIdFields.forEach(field => {
        field.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        field.addEventListener('blur', function() {
            const studentIdRegex = /^[A-Z0-9]{3,20}$/;
            if (studentIdRegex.test(this.value)) {
                toggleFieldValidation(this, true, '');
            } else {
                toggleFieldValidation(this, false, 'Student ID must be 3-20 characters (A-Z, 0-9 only)');
            }
        });
    });
    
    // Age validation
    const ageFields = document.querySelectorAll('input[name="age"]');
    ageFields.forEach(field => {
        field.addEventListener('input', function() {
            const value = parseInt(this.value);
            if (value >= 1 && value <= 100) {
                toggleFieldValidation(this, true, '');
            }
        });
        field.addEventListener('blur', function() {
            const value = parseInt(this.value);
            if (value >= 1 && value <= 100) {
                toggleFieldValidation(this, true, '');
            } else {
                toggleFieldValidation(this, false, 'Age must be between 1 and 100');
            }
        });
    });
    
    // Form submission validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                alert('Please correct the highlighted errors before submitting');
            }
        });
    });
});
</script>
@endpush
@endsection
