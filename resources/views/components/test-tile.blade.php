@props(['label', 'route'])

<a href="{{ route($route) }}"
   class="group bg-white border border-gray-200 rounded-xl shadow hover:shadow-md transition-all p-5 flex flex-col justify-center items-center text-center hover:bg-indigo-50 hover:border-indigo-300">
    <div class="text-lg font-medium text-gray-800 group-hover:text-indigo-700">{{ $label }}</div>
</a>
