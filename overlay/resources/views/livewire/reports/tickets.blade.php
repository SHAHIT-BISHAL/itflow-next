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
            <p class="text-xs uppercase tracking-wide text-slate-500">Open Tickets</p>
            <p class="mt-2 text-2xl font-semibold text-blue-600">{{ $totalOpen }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Resolved / Closed</p>
            <p class="mt-2 text-2xl font-semibold text-green-600">{{ $totalResolved }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs uppercase tracking-wide text-slate-500">Opened This Month</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $totalThisMonth }}</p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card title="Tickets by Status">
            <canvas id="statusChart" height="200"></canvas>
        </x-ui.card>
        <x-ui.card title="Tickets by Priority">
            <canvas id="priorityChart" height="200"></canvas>
        </x-ui.card>
        <x-ui.card title="Top Clients by Volume">
            @forelse($topClients as $i => $client)
                <div class="flex items-center justify-between py-2 {{ $i < count($topClients) - 1 ? 'border-b border-slate-100' : '' }}">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $client['name'] }}</p>
                    <x-ui.badge color="blue">{{ $client['count'] }}</x-ui.badge>
                </div>
            @empty
                <p class="text-sm text-slate-500">No tickets yet.</p>
            @endforelse
        </x-ui.card>
    </div>

    <x-ui.card title="Monthly Tickets Opened vs Closed ({{ $year }})" class="mt-6">
        <canvas id="monthlyChart" height="140"></canvas>
    </x-ui.card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    const statusColors = { open:'#3b82f6', pending:'#f59e0b', resolved:'#22c55e', closed:'#6b7280', archived:'#d1d5db' };
    const priorityColors = { urgent:'#ef4444', high:'#f97316', medium:'#eab308', low:'#22c55e' };

    function initTicketCharts() {
        ['statusChart','priorityChart','monthlyChart'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el._chart) { el._chart.destroy(); delete el._chart; }
        });

        const sc = document.getElementById('statusChart');
        if (sc) {
            const statuses = @json($byStatus);
            sc._chart = new Chart(sc, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statuses).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                    datasets: [{ data: Object.values(statuses), backgroundColor: Object.keys(statuses).map(s => statusColors[s] || '#6b7280') }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }

        const pc = document.getElementById('priorityChart');
        if (pc) {
            const priorities = @json($byPriority);
            pc._chart = new Chart(pc, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(priorities).map(p => p.charAt(0).toUpperCase() + p.slice(1)),
                    datasets: [{ data: Object.values(priorities), backgroundColor: Object.keys(priorities).map(p => priorityColors[p] || '#6b7280') }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
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
                        { label: 'Opened', data: monthly.map(m => m.opened), backgroundColor: 'rgba(99,102,241,0.7)', borderRadius: 4 },
                        { label: 'Closed', data: monthly.map(m => m.closed), backgroundColor: 'rgba(34,197,94,0.6)', borderRadius: 4 },
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initTicketCharts);
    document.addEventListener('livewire:navigated', initTicketCharts);
    document.addEventListener('livewire:updated', initTicketCharts);
</script>
@endpush
