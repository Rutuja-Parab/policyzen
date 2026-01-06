<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PolicyZen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-[#2b8bd0] to-[#04315b] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-6xl w-full bg-white rounded-lg shadow-2xl overflow-hidden">
        <div class="flex flex-col md:flex-row">
            <!-- Left Side -->
            <div
                class="md:w-1/2 bg-gradient-to-br from-[#04315b] to-[#2b8bd0] p-12 text-white flex flex-col justify-center">
                <h1 class="text-4xl font-bold mb-4">Welcome to PolicyZen</h1>
                <p class="text-lg mb-6">Secure Insurance Document Management System</p>
                <ul class="space-y-4">
                    <li class="flex items-center">
                        <i class="fas fa-shield-alt text-[#f06e11] mr-3"></i>
                        Secure Document Storage
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-file-alt text-[#f06e11] mr-3"></i>
                        Policy Management
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-clipboard-check text-[#f06e11] mr-3"></i>
                        Claim Processing
                    </li>
                </ul>
            </div>
            <!-- Right Side -->
            <div class="md:w-1/2 p-12">
                <div class="text-center mb-8">
                    <img src="{{ asset('logo.png') }}" alt="PolicyZen Logo" class="h-40 w-auto mx-auto mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">Sign In</h2>
                    <p class="text-gray-600">Access your insurance documents</p>
                </div>
                <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                    @csrf
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-3 text-gray-400"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent"
                                placeholder="Enter your email">
                        </div>
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                            <input type="password" name="password" required
                                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#2b8bd0] focus:border-transparent"
                                placeholder="Enter your password">
                        </div>
                    </div>
                    <!-- Remember and Forgot -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember"
                                class="h-4 w-4 text-[#2b8bd0] focus:ring-[#2b8bd0] border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Remember me</label>
                        </div>
                        <a href="#" class="text-sm text-[#2b8bd0] hover:text-[#04315b]">Forgot password?</a>
                    </div>
                    <!-- Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-[#f06e11] to-[#f28e1f] text-white py-3 rounded-lg font-medium hover:from-[#f28e1f] hover:to-[#f06e11] transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </form>
                <!-- Demo -->
                <div class="mt-8 bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-[#2b8bd0] mr-2"></i> Demo Credentials
                    </h3>
                    <p class="text-sm"><strong>Email:</strong> admin@policyzen.com</p>
                    <p class="text-sm"><strong>Password:</strong> admin123</p>
                    <p class="text-sm text-gray-600 mt-2">Use these to explore the system</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
