@php
    $nav = [
        ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Clients', 'icon' => 'building-office', 'route' => 'clients.index', 'active' => request()->routeIs('clients.*'), 'permission' => 'view clients'],
    ];

    $itdoc = [
        ['label' => 'Assets', 'icon' => 'computer-desktop', 'route' => 'assets.index', 'active' => request()->routeIs('assets.*'), 'permission' => 'view assets'],
        ['label' => 'Domains & Certs', 'icon' => 'globe-alt', 'route' => 'domains.index', 'active' => request()->routeIs('domains.*'), 'permission' => 'view domains'],
    ];

    $tickets = [
        ['label' => 'Tickets', 'icon' => 'ticket', 'route' => 'tickets.index', 'active' => request()->routeIs('tickets.*'), 'permission' => 'view tickets'],
    ];

    $crm = [
        ['label' => 'Deals', 'icon' => 'funnel', 'route' => 'deals.index', 'active' => request()->routeIs('deals.*'), 'permission' => 'view deals'],
    ];

    $billing = [
        ['label' => 'Invoices', 'icon' => 'document-text', 'route' => 'invoices.index', 'active' => request()->routeIs('invoices.*'), 'permission' => 'view invoices'],
        ['label' => 'Expenses',  'icon' => 'banknotes',    'route' => 'expenses.index',  'active' => request()->routeIs('expenses.*'), 'permission' => 'view expenses'],
    ];

    $reports = [
        ['label' => 'Overview',  'icon' => 'chart-bar',      'route' => 'reports.index',    'active' => request()->routeIs('reports.index'), 'permission' => 'view reports'],
        ['label' => 'Revenue',   'icon' => 'banknotes',      'route' => 'reports.revenue',  'active' => request()->routeIs('reports.revenue'), 'permission' => 'view reports'],
        ['label' => 'Tickets',   'icon' => 'ticket',         'route' => 'reports.tickets',  'active' => request()->routeIs('reports.tickets'), 'permission' => 'view reports'],
        ['label' => 'Expenses',  'icon' => 'receipt-percent','route' => 'reports.expenses', 'active' => request()->routeIs('reports.expenses'), 'permission' => 'view reports'],
    ];

    $comingSoon = [];

    $admin = [
        ['label' => 'Users', 'icon' => 'user-group', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*'), 'permission' => 'manage users'],
        ['label' => 'Roles', 'icon' => 'shield-check', 'route' => 'admin.roles.index', 'active' => request()->routeIs('admin.roles.*'), 'permission' => 'manage roles'],
        ['label' => 'Tags', 'icon' => 'tag', 'route' => 'admin.tags.index', 'active' => request()->routeIs('admin.tags.*'), 'permission' => 'manage tags'],
        ['label' => 'Categories', 'icon' => 'folder', 'route' => 'admin.categories.index', 'active' => request()->routeIs('admin.categories.*'), 'permission' => 'manage categories'],
        ['label' => 'Mail Accounts', 'icon' => 'inbox-arrow-down', 'route' => 'admin.mail-accounts.index', 'active' => request()->routeIs('admin.mail-accounts.*'), 'permission' => 'manage mail accounts'],
        ['label' => 'Pipelines', 'icon' => 'funnel', 'route' => 'admin.pipelines.index', 'active' => request()->routeIs('admin.pipelines.*'), 'permission' => 'manage pipelines'],
        ['label' => 'Audit Logs', 'icon' => 'shield-check', 'route' => 'admin.audit-logs.index', 'active' => request()->routeIs('admin.audit-logs.*'), 'permission' => 'view audit logs'],
        ['label' => 'Settings', 'icon' => 'folder', 'route' => 'admin.settings.index', 'active' => request()->routeIs('admin.settings.*'), 'permission' => 'manage settings'],
    ];
@endphp

<!-- Mobile backdrop -->
<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 flex w-72 transform flex-col bg-slate-950 text-slate-200 shadow-2xl transition-transform lg:relative lg:translate-x-0 lg:shadow-none"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white text-sm font-black text-slate-950">IT</div>
        <div>
            <p class="text-sm font-semibold text-white">ITFlow-Next</p>
            <p class="text-xs text-slate-400">MSP operations</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach ($nav as $item)
            @if (! isset($item['permission']) || auth()->user()->can($item['permission']))
                <x-ui.nav-link :item="$item" />
            @endif
        @endforeach

        <div class="pt-4">
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">IT Documentation</p>
            @foreach ($itdoc as $item)
                @can($item['permission'])
                    <x-ui.nav-link :item="$item" />
                @endcan
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Support</p>
            @foreach ($tickets as $item)
                @can($item['permission'])
                    <x-ui.nav-link :item="$item" />
                @endcan
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">CRM</p>
            @foreach ($crm as $item)
                @can($item['permission'])
                    <x-ui.nav-link :item="$item" />
                @endcan
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Billing</p>
            @foreach ($billing as $item)
                @can($item['permission'])
                    <x-ui.nav-link :item="$item" />
                @endcan
            @endforeach
        </div>

        <div class="pt-4">
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Reports</p>
            @foreach ($reports as $item)
                @can($item['permission'])
                    <x-ui.nav-link :item="$item" />
                @endcan
            @endforeach
        </div>

        @canany(['manage users', 'manage roles', 'manage tags', 'manage categories', 'manage mail accounts', 'manage pipelines', 'view audit logs', 'manage settings'])
        <div class="pt-4">
            <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Administration</p>
            @foreach ($admin as $item)
                @can($item['permission'])
                    <x-ui.nav-link :item="$item" />
                @endcan
            @endforeach
        </div>
        @endcanany
    </nav>

    <div class="border-t border-white/10 p-4 text-xs text-slate-500">
        v0.6 - Phase 6 Reports
    </div>
</aside>
