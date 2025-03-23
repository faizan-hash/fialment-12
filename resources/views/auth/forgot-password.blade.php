<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-100 to-amber-50">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md rounded-xl overflow-hidden">
            <div class="flex justify-center mb-6">
                <h1 class="text-2xl font-bold text-center text-gray-900">
                    <span class="text-amber-500">Feedback</span> System
                </h1>
            </div>
            
            <h2 class="text-2xl font-semibold text-center text-gray-800 mb-4">
                Reset Password
            </h2>
            
            <div class="mb-6 text-center text-sm text-gray-600">
                Enter your email address and we'll send you a password reset link.
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" 
                            class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-500 focus:ring-opacity-50" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition duration-150">
                        Send Password Reset Link
                    </button>
                </div>
            </form>
            
            <p class="mt-8 text-center text-sm text-gray-600">
                Remember your password?
                <a href="{{ route('login') }}" class="font-medium text-amber-600 hover:text-amber-500">
                    Back to login
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
