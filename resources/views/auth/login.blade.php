<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Lockout banner: shown when Laravel throttles the login --}}
    @if($errors->has('email') && str_contains($errors->first('email'), 'seconds'))
        <div id="lockoutBanner"
             class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <div class="flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="font-medium">Too many login attempts</p>
                    <p class="mt-1">
                        Please wait
                        <strong id="lockoutTimer">…</strong>
                        before trying again.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                   href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3" id="loginButton">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    {{-- Lockout JS: live countdown + disabled button --}}
    <script>
        (function () {
            const banner = document.getElementById('lockoutBanner');
            if (!banner) return;

            const timerEl = document.getElementById('lockoutTimer');
            const button  = document.getElementById('loginButton');
            const form    = document.getElementById('loginForm');

            // Extract seconds from the error text
            const errorText = banner.textContent;
            const match = errorText.match(/(\d+)\s+seconds?/i);
            let seconds = match ? parseInt(match[1], 10) : 60;

            function render() {
                if (seconds <= 0) {
                    // Lockout over — clear banner and re-enable button
                    banner.remove();
                    if (button) {
                        button.disabled = false;
                        button.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                    return;
                }

                timerEl.textContent = seconds + ' second' + (seconds === 1 ? '' : 's');
                seconds--;
                setTimeout(render, 1000);
            }

            // Disable button + form submission while locked
            if (button) {
                button.disabled = true;
                button.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (seconds > 0) {
                        e.preventDefault();
                        alert('Please wait for the lockout to expire before trying again.');
                    }
                });
            }

            render();
        })();
    </script>
</x-guest-layout>