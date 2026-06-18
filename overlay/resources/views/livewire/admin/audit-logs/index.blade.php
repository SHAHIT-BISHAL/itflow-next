<div class="space-y-5">
    <x-ui.toolbar>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="relative">
                <x-ui.icon name="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search action, description, or IP"
                    class="w-full rounded-md border border-slate-200 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500" />
            </div>

            <select wire:model.live="action"
                class="rounded-md border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                <option value="">All actions</option>
                @foreach ($actions as $availableAction)
                    <option value="{{ $availableAction }}">{{ $availableAction }}</option>
                @endforeach
            </select>
        </div>
    </x-ui.toolbar>

    <x-ui.card>
        <x-ui.table>
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <x-ui.th>Time</x-ui.th>
                    <x-ui.th>Action</x-ui.th>
                    <x-ui.th>Actor</x-ui.th>
                    <x-ui.th>Subject</x-ui.th>
                    <x-ui.th>IP</x-ui.th>
                    <x-ui.th>Context</x-ui.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    @php
                        $actionColor = str_contains($log->action, 'deleted') || str_contains($log->action, 'archived') || str_contains($log->action, 'voided')
                            ? 'red'
                            : (str_contains($log->action, 'created') || str_contains($log->action, 'recorded') ? 'green' : 'blue');
                        $actorLabel = $log->actor?->name
                            ?? ($log->actor_type ? class_basename($log->actor_type) . ' #' . $log->actor_id : 'System');
                        $subjectLabel = $log->subject_type
                            ? class_basename($log->subject_type) . ' #' . $log->subject_id
                            : '-';
                    @endphp
                    <tr class="align-top hover:bg-slate-50">
                        <td class="whitespace-nowrap px-3 py-3 text-xs text-slate-500">
                            {{ $log->created_at?->format('M j, Y H:i') }}
                        </td>
                        <td class="px-3 py-3">
                            <x-ui.badge :color="$actionColor">{{ $log->action }}</x-ui.badge>
                            @if ($log->description)
                                <p class="mt-1 max-w-md text-sm text-slate-600">{{ $log->description }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $actorLabel }}</td>
                        <td class="px-3 py-3 text-sm text-slate-700">{{ $subjectLabel }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-sm text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-slate-500">
                            @if ($log->metadata || $log->before || $log->after)
                                <details class="max-w-sm">
                                    <summary class="cursor-pointer font-semibold text-slate-700">Details</summary>
                                    <pre class="mt-2 max-h-48 overflow-auto rounded-md bg-slate-950 p-3 text-xs text-slate-100">{{ json_encode([
    'metadata' => $log->metadata,
    'before' => $log->before,
    'after' => $log->after,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-slate-500">No audit logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-ui.card>
</div>
