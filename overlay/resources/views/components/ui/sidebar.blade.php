@php
    $nav = [
        ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Clients', 'icon' => 'building-office', 'route' => 'clients.index', 'active' => request()->routeIs('clients.*')],
    ];

    $itdoc = [
        ['label' => 'Assets', 'icon' => 'computer-desktop', 'route' => 'assets.index', 'active' => request()->routeIs('assets.*')],
        ['label' => 'Domains & Certs', 'icon' => 'globe-alt', 'route' => 'domains.index', 'active' => request()->routeIs('domains.*')],
    ];

    $tickets = [
        ['label' => 'Tickets', 'icon' => 'ticket', 'route' => 'tickets.index', 'active' => request()->routeIs('tickets.*')],
    ];

    $crm = [
        ['label' => 'Deals', 'icon' => 'funnel', 'route' => 'deals.index', 'active' => request()->routeIs('deals.*')],
    ];

    $billing = [
        ['label' => 'Invoices', 'icon' => 'document-text', 'route' => 'invoices.index', 'active' => request()->routeIs('invoices.*')],
        ['label' => 'Expenses',  'icon' => 'banknotes',    'route' => 'expenses.index',  'active' => request()->routeIs('expenses.*')],
    ];

    $reports = [
        ['label' => 'Overview',  'icon' => 'chart-bar',      'route' => 'reports.index',    'active' => request()->routeIs('reports.index')],
        ['label' => 'Revenue',   'icon' => 'banknotes',      'route' => 'reports.revenue',  'active' => request()->routeIs('reports.revenue')],
        ['label' => 'Tickets',   'icon' => 'ticket',         'route' => 'reports.tickets',  'active' => request()->routeIs('reports.tickets')],
        ['label' => 'Expenses',  'icon' => 'receipt-percent','route' => 'reports.expenses', 'active' => request()->routeIs('reports.expenses')],
    ];

    $comingSoon = [];

    $admin = [
        ['label' => 'Users', 'icon' => 'user-group', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*')],
        ['label' => 'Roles', 'icon' => 'shield-check', 'route' => 'admin.roles.index', 'active' => request()->routeIs('admin.roles.*')],
        ['label' => 'Tags', 'icon' => 'tag', 'route' => 'admin.tags.index', 'active' => request()->routeIs('admin.tags.*')],
        ['label' => 'Categories', 'icon' => 'folder', 'route' => 'admin.categories.index', 'active' => request()->routeIs('admin.categories.*')],
        ['label' => 'Mail Accounts', 'icon' => 'inbox-arrow-down', 'route' => 'admin.mail-accounts.index', 'active' => request()->routeIs('admin.mail-accounts.*')],
        ['label' => 'Pipelines', 'icon' => 'funnel', 'route' => 'admin.pipelines.index', 'active' => request()->routeIs('admin.pipelines.*')],
    ];
@endphp

<!-- Mobile backdrop -->
<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 w-64 transform bg-slate-900 text-slate-200 transition-transform lg:relative lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex h-16 items-center gap-2 px-5 border-b border-slate-800">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 font-bold text-white">IT</div>
        <span class="text-lg font-semibold text-white">ITFlow-Next</span>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
        @foreach ($nav as $item)
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                {{ $item['label'] }}
            </a>
        @endforeach

        <div class="pt-4">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">IT Documentation</p>
            @foreach ($itdoc as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                          {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Support</p>
            @foreach ($tickets as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                          {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">CRM</p>
            @foreach ($crm as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                          {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Billing</p>
            @foreach ($billing as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                          {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Reports</p>
            @foreach ($reports as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                          {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        @can('manage users')
        <div class="pt-4">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Administration</p>
            @foreach ($admin as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                          {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
        @endcan
    </nav>

    <div class="border-t border-slate-800 p-4 text-xs text-slate-500">
        v0.6 · Phase 6 Reports
    </div>
</aside>
