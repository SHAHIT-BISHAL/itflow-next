<div>
    {{-- KPI strip --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Revenue MTD</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">${{ number_format($revenueThisMonth, 0) }}</p>
            @if($revenueLastMonth > 0)
                @php $pct = round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100); @endphp
                <p class="text-xs mt-1 {{ $pct >= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $pct >= 0 ? '+' : '' }}{{ $pct }}% vs last month
                </p>
            @endif
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Revenue YTD</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">${{ number_format($revenueYtd, 0) }}</p>
            <p class="text-xs mt-1 text-slate-500">Jan–{{ today()->format('M') }} {{ today()->year }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Expenses MTD</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">${{ number_format($expensesThisMonth, 0) }}</p>
            <p class="text-xs mt-1 text-slate-500">Net: ${{ number_format($revenueThisMonth - $expensesThisMonth, 0) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Overdue Invoices</p>
            <p class="mt-2 text-2xl font-semibold {{ $overdueInvoices > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $overdueInvoices }}</p>
            @if($overdueAmount > 0)
                <p class="text-xs mt-1 text-red-500">${{ number_format($overdueAmount, 0) }} outstanding</p>
            @endif
        </x-ui.card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Revenue sparkline --}}
        <x-ui.card title="Revenue – Last 6 Months">
            <canvas id="revenueSparkline" height="120"></canvas>
        </x-ui.card>

        {{-- Tickets + Deals summary --}}
        <x-ui.card title="Operations Summary">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Open Tickets</p>
                        <p class="text-xs text-slate-500">{{ $closedThisMonth }} resolved this month</p>
                    </div>
                    <span class="text-2xl font-semibold text-slate-900">{{ $openTickets }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Open Deals</p>
                        <p class="text-xs text-slate-500">${{ number_format($pipelineValue, 0) }} pipeline value</p>
                    </div>
                    <span class="text-2xl font-semibold text-slate-900">{{ $openDeals }}</span>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Report links --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('reports.revenue') }}" class="block">
            <x-ui.card class="hover:border-brand-300 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                        <x-ui.icon name="banknotes" class="h-5 w-5 text-green-600" />
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">Revenue Report</p>
                        <p class="text-xs text-slate-500">Monthly revenue vs expenses</p>
                    </div>
                </div>
            </x-ui.card>
        </a>
        <a href="{{ route('reports.tickets') }}" class="block">
            <x-ui.card class="hover:border-brand-300 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                        <x-ui.icon name="ticket" class="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">Ticket Report</p>
                        <p class="text-xs text-slate-500">Status, priority & volume trends</p>
                    </div>
                </div>
            </x-ui.card>
        </a>
        <a href="{{ route('reports.expenses') }}" class="block">
            <x-ui.card class="hover:border-brand-300 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100">
                        <x-ui.icon name="receipt-percent" class="h-5 w-5 text-orange-600" />
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">Expense Report</p>
                        <p class="text-xs text-slate-500">By category, billable vs internal</p>
                    </div>
                </div>
            </x-ui.card>
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('livewire:navigated', initCharts);
    document.addEventListener('DOMContentLoaded', initCharts);
    function initCharts() {
        const ctx = document.getElementById('revenueSparkline');
        if (!ctx) return;
        if (ctx._chart) ctx._chart.destroy();
        ctx._chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($last6->pluck('month')),
                datasets: [{
                    label: 'Revenue',
                    data: @json($last6->pluck('revenue')),
                    backgroundColor: 'rgba(99,102,241,0.7)',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } } }
            }
        });
    }
</script>
@endpush
