@props(['label', 'route', 'disabled' => false])

<a 
    @if (!$disabled) href="{{ route($route) }}" @endif
    class="group 
           {{ $disabled ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white border-gray-200 text-gray-800 hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700' }}
           border rounded-xl shadow transition-all p-5 flex flex-col justify-center items-center text-center"
>
    <div class="text-lg font-medium">{{ $label }}</div>
</a>
