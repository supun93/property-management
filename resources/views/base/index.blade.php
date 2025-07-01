@php use Illuminate\Support\Str; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Property Categories' }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex space-x-2">
                        @if($viewData['add'])
                        <a href="#" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm">➕ Add New</a>
                        @endif
                        @if($viewData['trashList'])
                        <a href="{{ url()->current() }}?trash=1" class="text-white bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded text-sm">🗑️ Trashed</a>
                        @endif
                        @if($viewData['export'])
                        <button class="text-white bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded text-sm">📤 Export</button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="main-table" class="w-full text-sm text-left text-gray-700 border rounded">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                @foreach($columns as $col)
                                <th scope="col" class="px-4 py-2" style="text-align: left;">
                                    {{ $col == 'created_at' ? Str::headline('Created On') : Str::headline($col) }}
                                </th>
                                @endforeach
                                <th class="px-4 py-2 text-center" style="text-align: left;">⚙️ Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr class="border-b">
                                @foreach($columns as $col)
                                <td class="px-4 py-2" style="text-align: left;">
                                    @if(isset($columnDisplays[$col]))
                                        {{ call_user_func($columnDisplays[$col]['callback'], $item->{$col}, ...$columnDisplays[$col]['args']) }}
                                    @else
                                        {{ $item->{$col} }}
                                    @endif
                                </td>
                                @endforeach
                                <td class="px-4 py-2 text-center whitespace-nowrap">
                                    @if($viewData['edit'])
                                    <a href="{{ route(Str::kebab(class_basename($model)) . '.edit', $item->id) }}" class="text-indigo-600 hover:underline text-sm">✏️</a>
                                    @endif

                                    @if($viewData['trash'])
                                    <form action="{{ route(Str::kebab(class_basename($model)) . '.trash', $item->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline text-sm" type="submit">🗑️</button>
                                    </form>
                                    @endif

                                    @if($viewData['restore'])
                                    <form action="{{ route(Str::kebab(class_basename($model)) . '.restore', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="text-green-600 hover:underline text-sm" type="submit">♻️</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css" />
    @endpush

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#main-table').DataTable({
                responsive: true,
                language: {
                    search: "🔍 Search:",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
