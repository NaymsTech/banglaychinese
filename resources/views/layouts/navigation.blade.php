<nav x-data="{ open: false }" class="border-b border-gray-100 bg-white">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ url('/') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Home') }}
                    </x-nav-link>
                    <x-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.index') || request()->routeIs('courses.show')">
                        {{ __('Courses') }}
                    </x-nav-link>
                    <x-nav-link :href="route('study-in-china')" :active="request()->routeIs('study-in-china')">
                        {{ __('Study in China') }}
                    </x-nav-link>
                    <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index') || request()->routeIs('posts.show')">
                        {{ __('Blog') }}
                    </x-nav-link>
                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">
                        {{ __('About') }}
                    </x-nav-link>
                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">
                        {{ __('Contact') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Auth Section -->
            <div class="hidden items-center gap-3 sm:ms-6 sm:flex sm:items-center">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg border border-[#0F5132]/30 px-4 py-2 text-sm font-semibold text-[#0F5132] transition ease-in-out duration-150 hover:border-[#0F5132]/60 hover:bg-[#0F5132]/5">
                        {{ __('Login') }}
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-[#0F5132] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition ease-in-out duration-150 hover:bg-[#0d452c] hover:shadow-md">
                        {{ __('Get Started') }}
                    </a>
                @else
                    <a href="{{ route('dashboard.index') }}" class="inline-flex items-center rounded-lg bg-[#0F5132] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition ease-in-out duration-150 hover:bg-[#0d452c] hover:shadow-md">
                        {{ __('Dashboard') }}
                    </a>
                @endguest
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.index') || request()->routeIs('courses.show')">
                {{ __('Courses') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('study-in-china')" :active="request()->routeIs('study-in-china')">
                {{ __('Study in China') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index') || request()->routeIs('posts.show')">
                {{ __('Blog') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('about')" :active="request()->routeIs('about')">
                {{ __('About') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('contact')" :active="request()->routeIs('contact')">
                {{ __('Contact') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Auth Options -->
        <div class="border-t border-gray-200 pb-1 pt-4">
            @auth
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('dashboard.index')" :active="request()->routeIs('dashboard.index')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                </div>
            @else
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Login') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Get Started') }}
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
