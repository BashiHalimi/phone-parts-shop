{{-- Admin Top Navbar --}}
<header class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-gray-200 bg-white px-3 sm:px-6">

    {{-- Left: menu toggle + page title --}}
    <div class="flex min-w-0 items-center gap-3">
        {{-- Mobile menu toggle --}}
        <button
            @click="sidebarOpen = true"
            class="shrink-0 rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 lg:hidden"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <h1 class="truncate text-base font-semibold text-gray-800 sm:text-lg">
            @yield('page-title', 'Dashboard')
        </h1>
    </div>

    {{-- Right: alerts + user menu --}}
    <div class="flex shrink-0 items-center gap-1 sm:gap-2">

        {{-- Alerts bell --}}
        <a href="{{ route('admin.notifications.index') }}"
           class="relative shrink-0 rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
           title="Stock alerts">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>

            @if(($navTotalAlerts ?? 0) > 0)
                <span class="absolute right-1 top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                    {{ $navTotalAlerts > 99 ? '99+' : $navTotalAlerts }}
                </span>
            @endif
        </a>

        {{-- User dropdown --}}
        <div x-data="{ open: false }" class="relative shrink-0">
            <button
                @click="open = !open"
                class="flex items-center gap-2 rounded-md px-1.5 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100 sm:px-2"
            >
                {{-- Avatar --}}
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>

                {{-- Name (hidden on small screens) --}}
                <span class="hidden md:inline">{{ auth()->user()->name }}</span>

                {{-- Role badge (hidden on small screens) --}}
                @if(auth()->user()->role)
                    <span class="hidden rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-indigo-700 md:inline">
                        {{ auth()->user()->role->name }}
                    </span>
                @endif

                {{-- Chevron (hidden on small screens) --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="hidden h-4 w-4 md:inline">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            {{-- Dropdown panel --}}
            <div
                x-show="open"
                @click.outside="open = false"
                x-transition
                class="absolute right-0 z-50 mt-2 w-56 rounded-md border border-gray-200 bg-white py-1 shadow-lg"
                style="display: none;"
            >
                <div class="border-b border-gray-100 px-4 py-2">
                    <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    @if(auth()->user()->role)
                        <span class="mt-1 inline-block rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-indigo-700">
                            {{ auth()->user()->role->name }}
                        </span>
                    @endif
                </div>

                <a href="{{ route('profile.edit') }}"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Profile
                </a>

                <!-- <a href="{{ route('dashboard') }}"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    User Dashboard
                </a> -->

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>