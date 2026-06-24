<header class="flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex items-center gap-4">
        <button type="button" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 lg:hidden" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle navigation menu">
            <x-ui.icon name="bars-3" class="h-5 w-5" />
        </button>

        <div class="hidden sm:block">
            @livewire('global-search')
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="button" class="relative rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800" aria-label="Notifications" title="Notifications">
            <x-ui.icon name="bell" class="h-5 w-5" />
        </button>

        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-md border border-transparent px-2 py-1.5 hover:border-slate-200 hover:bg-slate-50">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-950 text-sm font-semibold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                </div>
                <span class="hidden text-sm font-semibold text-slate-700 sm:block">{{ auth()->user()->name ?? '' }}</span>
                <x-ui.icon name="chevron-down" class="h-4 w-4 text-slate-400" />
            </button>

            <div x-show="open" @click.outside="open = false" x-cloak
                 class="absolute right-0 z-50 mt-2 w-52 rounded-lg border border-slate-200 bg-white p-1 shadow-xl">
                <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">Log out</button>
                </form>
            </div>
        </div>
    </div>
</header>
