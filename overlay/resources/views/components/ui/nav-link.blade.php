@props(['item'])

<a href="{{ route($item['route']) }}"
   class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold transition-colors
          {{ $item['active'] ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md {{ $item['active'] ? 'bg-slate-950 text-white' : 'bg-white/5 text-slate-400 group-hover:text-white' }}">
        <x-ui.icon :name="$item['icon']" class="h-4 w-4" />
    </span>
    <span class="truncate">{{ $item['label'] }}</span>
</a>
