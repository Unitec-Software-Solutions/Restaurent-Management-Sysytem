@props([
    'hover' => true,
    'cols' => []
])

<tr class="{{ $hover ? 'table-row-hover' : '' }}">
    @foreach($cols as $col)
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="{{ $col['class'] ?? '' }}">{{ $col['value'] }}</div>
        </td>
    @endforeach
</tr>