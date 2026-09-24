<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') — {{ config('app.name', 'Phone Parts Shop') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-800">

    {{-- Ctrl+K: jump to product search --}}
    <script>
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                window.location.href = '{{ route('admin.products.index') }}';
            }
        });
    </script>

    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        @include('admin.partials.sidebar')

        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-transition.opacity
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            style="display: none;"
        ></div>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Navbar --}}
            @include('admin.partials.navbar')

            {{-- Content --}}
            <main class="flex-1 overflow-y-auto bg-gray-100 p-4 sm:p-6">

                {{-- Success message (auto-dismiss after 4s) --}}
                @if(session('success'))
                    <div x-data="{ show: true }"
                         x-init="setTimeout(() => show = false, 4000)"
                         x-show="show"
                         x-transition.opacity.duration.500ms
                         class="mb-4 flex items-start justify-between gap-3 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                        <div class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0 text-green-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-green-700 hover:text-green-900">&times;</button>
                    </div>
                @endif

                {{-- Error message (auto-dismiss after 6s) --}}
                @if(session('error'))
                    <div x-data="{ show: true }"
                         x-init="setTimeout(() => show = false, 6000)"
                         x-show="show"
                         x-transition.opacity.duration.500ms
                         class="mb-4 flex items-start justify-between gap-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                        <div class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0 text-red-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-red-700 hover:text-red-900">&times;</button>
                    </div>
                @endif

                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="border-t border-gray-200 bg-white px-6 py-3 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </footer>
        </div>
    </div>

</body>
</html>