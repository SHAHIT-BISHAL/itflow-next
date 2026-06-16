@props(['item'])

<a href="{{ route($item['route']) }}"
   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
          {{ $item['active'] ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
    {{ $item['label'] }}
</a>
