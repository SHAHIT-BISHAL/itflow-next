<header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-4">
        <button type="button" class="lg:hidden text-slate-500" @click="sidebarOpen = !sidebarOpen">
            <x-ui.icon name="bars-3" class="h-6 w-6" />
        </button>

        <div class="hidden sm:block">
            @livewire('global-search')
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="button" class="relative text-slate-500 hover:text-slate-700">
            <x-ui.icon name="bell" class="h-6 w-6" />
        </button>

        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">
                    {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                </div>
                <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name ?? '' }}</span>
                <x-ui.icon name="chevron-down" class="h-4 w-4 text-slate-400" />
            </button>

            <div x-show="open" @click.outside="open = false" x-cloak
                 class="absolute right-0 z-50 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Log out</button>
                </form>
            </div>
        </div>
    </div>
</header>
