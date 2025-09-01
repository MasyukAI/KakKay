<div class="relative">
    @if($count > 0)
        <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-6 h-6 pb-2 flex items-center justify-center font-bold border-2 border-white shadow-lg z-10">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</div>
