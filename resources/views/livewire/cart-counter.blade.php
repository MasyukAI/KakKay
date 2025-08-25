<div>
    @if($count > 0)
        <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center font-bold">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</div>
