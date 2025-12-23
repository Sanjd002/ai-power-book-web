<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
            <!-- Logo / Title -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-indigo-600">Readora</h1>
                <p class="text-gray-600 mt-2">Login to continue your journey</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" 
                                  type="email" name="email" :value="old('email')" 
                                  required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full"
                                  type="password" name="password" required 
                                  autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" 
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                               name="remember">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between mt-6">
                    @if (Route::has('password.request'))
                        <a class="text-sm text-indigo-600 hover:text-indigo-800" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif

                    <x-primary-button>
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>

            <!-- Sign Up Link -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Don’t have an account?
                    <a href="{{ route('register') }}" class="text-indigo-600 font-medium hover:underline">
                        Sign Up
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
