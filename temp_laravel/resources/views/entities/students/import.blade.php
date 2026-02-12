@extends('layouts.app')

@section('title', 'Import Students - PolicyZen')
@section('page-title', 'Import Students from CSV')

@section('header-actions')
    <a href="{{ route('entities.students.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('import_errors') && count(session('import_errors')) > 0)
        <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
                <div class="flex-1">
                    <p class="font-medium mb-2">Import completed with some errors:</p>
                    <ul class="list-disc list-inside text-sm space-y-1 max-h-40 overflow-y-auto">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">CSV Import Instructions</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700 mb-2">Your CSV file should include the following columns:</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li><strong>sr.no</strong> or <strong>sr_no</strong> (optional - will be ignored)</li>
                        <li><strong>student_id</strong> (required) - Unique student identifier</li>
                        <li><strong>name</strong> (required) - Student's full name</li>
                        <li><strong>email</strong> (optional) - Student's email address</li>
                        <li><strong>phone</strong> (optional) - Student's phone number</li>
                        <li><strong>dob</strong> or <strong>date_of_birth</strong> (optional) - Date of birth</li>
                        <li><strong>age</strong> (optional) - Student's age</li>
                        <li><strong>gender</strong> (optional) - Student's gender</li>
                        <li><strong>rank</strong> (optional) - Student's rank</li>
                        <li><strong>batch</strong> (optional) - Student's batch</li>
                        <li><strong>date_of_joining</strong> (optional) - Date of joining (format: YYYY-MM-DD)</li>
                        <li><strong>sum_insured</strong> (optional) - Sum insured amount (e.g., 1000000 for ₹10,00,000)</li>
                        <li><strong>company_name</strong> or <strong>company</strong> (optional) - Company name (will use
                            selected company if not provided)</li>
                    </ul>
                </div>
            </div>

            <form method="POST" action="{{ route('entities.students.import.process') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CSV File *</label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-file-csv text-4xl text-gray-400 mb-2"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="csv_file"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-[#f06e11] hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload CSV file</span>
                                        <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" required
                                            class="sr-only" onchange="updateFileName(this)">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500" id="file-name">CSV, TXT up to 10MB</p>
                            </div>
                        </div>
                        @error('csv_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company *</label>
                            <select name="company_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Default company for all students (can be overridden by CSV
                                company_name column)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                            <select name="course_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent">
                                <option value="">Select Course (optional)</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}"
                                        {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->course_name }} ({{ $course->course_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <a href="{{ route('entities.students.index') }}"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Cancel
                    </a>
                    <button type="submit" class="bg-[#f06e11] text-white px-4 py-2 rounded-lg hover:bg-[#f28e1f]">
                        <i class="fas fa-upload mr-2"></i>Import Students
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sample CSV Format -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Sample CSV Format</h3>
                <a href="{{ route('entities.students.download-template') }}"
                    class="mt-2 sm:mt-0 inline-flex items-center px-3 py-2 border border-[#2b8bd0] text-sm leading-4 font-medium rounded-md text-[#2b8bd0] bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2b8bd0]">
                    <i class="fas fa-download mr-2"></i>Download Sample CSV
                </a>
            </div>
            <div class="bg-gray-50 rounded-lg overflow-x-auto">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-gray-200 whitespace-nowrap">
                            <th class="px-2 py-2 text-left">sr.no</th>
                            <th class="px-2 py-2 text-left">student_id</th>
                            <th class="px-2 py-2 text-left">name</th>
                            <th class="px-2 py-2 text-left">email</th>
                            <th class="px-2 py-2 text-left">phone</th>
                            <th class="px-2 py-2 text-left">dob</th>
                            <th class="px-2 py-2 text-left">age</th>
                            <th class="px-2 py-2 text-left">gender</th>
                            <th class="px-2 py-2 text-left">rank</th>
                            <th class="px-2 py-2 text-left">batch</th>
                            <th class="px-2 py-2 text-left">doj</th>
                            <th class="px-2 py-2 text-left">sum_ins</th>
                            <th class="px-2 py-2 text-left">company</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-2 py-2">1</td>
                            <td class="px-2 py-2">STU001</td>
                            <td class="px-2 py-2">John Doe</td>
                            <td class="px-2 py-2">john@example.com</td>
                            <td class="px-2 py-2">1234567890</td>
                            <td class="px-2 py-2">2000-01-15</td>
                            <td class="px-2 py-2">24</td>
                            <td class="px-2 py-2">Male</td>
                            <td class="px-2 py-2">1</td>
                            <td class="px-2 py-2">2024-25</td>
                            <td class="px-2 py-2">2024-01-01</td>
                            <td class="px-2 py-2">1000000</td>
                            <td class="px-2 py-2">ABC Co.</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-2 py-2">2</td>
                            <td class="px-2 py-2">STU002</td>
                            <td class="px-2 py-2">Jane Smith</td>
                            <td class="px-2 py-2">jane@example.com</td>
                            <td class="px-2 py-2">0987654321</td>
                            <td class="px-2 py-2">2001-05-20</td>
                            <td class="px-2 py-2">23</td>
                            <td class="px-2 py-2">Female</td>
                            <td class="px-2 py-2">2</td>
                            <td class="px-2 py-2">2024-25</td>
                            <td class="px-2 py-2">2024-01-01</td>
                            <td class="px-2 py-2">1000000</td>
                            <td class="px-2 py-2">XYZ Corp</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-3 bg-blue-50 rounded text-xs sm:text-sm">
                <p class="font-medium text-gray-700 mb-1"><i class="fas fa-info-circle mr-1"></i>Column Reference:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 text-gray-600">
                    <span><strong>sr.no</strong> - Serial number</span>
                    <span><strong>student_id</strong> - Student ID</span>
                    <span><strong>name</strong> - Full name</span>
                    <span><strong>email</strong> - Email address</span>
                    <span><strong>phone</strong> - Phone number</span>
                    <span><strong>dob</strong> - Date of birth</span>
                    <span><strong>age</strong> - Age</span>
                    <span><strong>gender</strong> - Gender</span>
                    <span><strong>rank</strong> - Rank</span>
                    <span><strong>batch</strong> - Batch</span>
                    <span><strong>doj</strong> - Date of joining</span>
                    <span><strong>sum_ins</strong> - Sum insured</span>
                    <span><strong>company</strong> - Company name</span>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-600">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Note:</strong> Date of exiting will be set when student leaves the program.
                Sum insured is used for insurance premium calculations.
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateFileName(input) {
                const fileName = input.files[0]?.name || 'CSV, TXT up to 10MB';
                document.getElementById('file-name').textContent = fileName;
            }
        </script>
    @endpush
@endsection
