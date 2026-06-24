<div>
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Reports</a>
        </div>
        <div class="flex items-center gap-2">
            <svg wire:loading wire:target="year" class="h-4 w-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <select wire:model.live="year" class="rounded-lg border-slate-300 text-sm shadow-sm">
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Summary KPIs --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Revenue {{ $year }}</p>
            <p class="mt-2 text-2xl font-semibold text-green-600">${{ number_format($totalRevenue, 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Expenses {{ $year }}</p>
            <p class="mt-2 text-2xl font-semibold text-red-500">${{ number_format($totalExpenses, 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Net Profit {{ $year }}</p>
            <p class="mt-2 text-2xl font-semibold {{ $netProfit >= 0 ? 'text-slate-900' : 'text-red-600' }}">${{ number_format($netProfit, 2) }}</p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Bar chart --}}
        <x-ui.card title="Monthly Revenue vs Expenses" class="lg:col-span-2">
            <canvas id="revenueChart" height="180"></canvas>
        </x-ui.card>

        {{-- Top clients --}}
        <x-ui.card title="Top Clients by Revenue">
            @forelse($topClients as $i => $client)
                <div class="flex items-center justify-between py-2 {{ $i < count($topClients) - 1 ? 'border-b border-slate-100' : '' }}">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $client['name'] }}</p>
                    <p class="text-sm font-semibold text-slate-900 ml-2">${{ number_format($client['total'], 0) }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No payments recorded.</p>
            @endforelse
        </x-ui.card>
    </div>

    {{-- Monthly table --}}
    <x-ui.card title="Monthly Breakdown" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <th class="py-3 text-left">Month</th>
                        <th class="py-3 text-right">Revenue</th>
                        <th class="py-3 text-right">Expenses</th>
                        <th class="py-3 text-right">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $row)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="py-2 font-medium text-slate-700">{{ $row['month'] }}</td>
                        <td class="py-2 text-right text-green-600">${{ number_format($row['revenue'], 2) }}</td>
                        <td class="py-2 text-right text-red-500">${{ number_format($row['expenses'], 2) }}</td>
                        <td class="py-2 text-right font-medium {{ ($row['revenue'] - $row['expenses']) >= 0 ? 'text-slate-900' : 'text-red-600' }}">
                            ${{ number_format($row['revenue'] - $row['expenses'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 font-semibold">
                        <td class="py-2">Total</td>
                        <td class="py-2 text-right text-green-600">${{ number_format($totalRevenue, 2) }}</td>
                        <td class="py-2 text-right text-red-500">${{ number_format($totalExpenses, 2) }}</td>
                        <td class="py-2 text-right {{ $netProfit >= 0 ? 'text-slate-900' : 'text-red-600' }}">${{ number_format($netProfit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-ui.card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    function initRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;
        if (ctx._chart) ctx._chart.destroy();
        ctx._chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($months->pluck('month')),
                datasets: [
                    { label: 'Revenue',  data: @json($months->pluck('revenue')),  backgroundColor: 'rgba(34,197,94,0.7)',  borderRadius: 4 },
                    { label: 'Expenses', data: @json($months->pluck('expenses')), backgroundColor: 'rgba(239,68,68,0.6)',  borderRadius: 4 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } } }
            }
        });
    }
    document.addEventListener('DOMContentLoaded', initRevenueChart);
    document.addEventListener('livewire:navigated', initRevenueChart);
    document.addEventListener('livewire:updated', initRevenueChart);
</script>
@endpush
