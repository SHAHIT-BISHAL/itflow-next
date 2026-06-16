<div>
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('reports.index') }}" class="text-sm text-brand-600 hover:underline">&larr; Reports</a>
        <select wire:model.live="year" class="rounded-lg border-slate-300 text-sm shadow-sm">
            @foreach($years as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Expenses {{ $year }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">${{ number_format($totalYtd, 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Billable</p>
            <p class="mt-2 text-2xl font-semibold text-green-600">${{ number_format($totalBillable, 2) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Internal</p>
            <p class="mt-2 text-2xl font-semibold text-slate-500">${{ number_format($totalInternal, 2) }}</p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card title="Expenses by Category">
            <canvas id="categoryChart" height="220"></canvas>
        </x-ui.card>

        <x-ui.card title="Monthly Billable vs Internal">
            <canvas id="monthlyChart" height="220"></canvas>
        </x-ui.card>
    </div>

    <x-ui.card title="Category Breakdown" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <th class="py-3 text-left">Category</th>
                        <th class="py-3 text-right">Total</th>
                        <th class="py-3 text-right">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byCategory as $row)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="py-2 font-medium text-slate-700">{{ $row['category'] }}</td>
                        <td class="py-2 text-right">${{ number_format($row['total'], 2) }}</td>
                        <td class="py-2 text-right text-slate-500">
                            {{ $totalYtd > 0 ? number_format(($row['total'] / $totalYtd) * 100, 1) : '0' }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    function initExpenseCharts() {
        ['categoryChart','monthlyChart'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el._chart) { el._chart.destroy(); delete el._chart; }
        });

        const cc = document.getElementById('categoryChart');
        if (cc) {
            const cats = @json($byCategory);
            cc._chart = new Chart(cc, {
                type: 'doughnut',
                data: {
                    labels: cats.map(c => c.category),
                    datasets: [{ data: cats.map(c => c.total), backgroundColor: ['#6366f1','#f59e0b','#22c55e','#ef4444','#3b82f6','#ec4899','#14b8a6'] }]
                },
                options: { responsive: true, plugins: { legend: { position: 'right' } } }
            });
        }

        const mc = document.getElementById('monthlyChart');
        if (mc) {
            const monthly = @json($monthly);
            mc._chart = new Chart(mc, {
                type: 'bar',
                data: {
                    labels: monthly.map(m => m.month),
                    datasets: [
                        { label: 'Billable',  data: monthly.map(m => m.billable),  backgroundColor: 'rgba(34,197,94,0.7)',  borderRadius: 4 },
                        { label: 'Internal',  data: monthly.map(m => m.internal),  backgroundColor: 'rgba(239,68,68,0.5)',  borderRadius: 4 },
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, stacked: true }, x: { stacked: true } } }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initExpenseCharts);
    document.addEventListener('livewire:navigated', initExpenseCharts);
    document.addEventListener('livewire:updated', initExpenseCharts);
</script>
@endpush
