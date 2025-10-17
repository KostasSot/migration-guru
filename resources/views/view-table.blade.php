<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Viewing Table: {{ $tableName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<div class="container mx-auto my-12 max-w-7xl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                Viewing Table: <span class="font-mono bg-gray-200 px-2 py-1 rounded">{{ $tableName }}</span>
            </h1>
            <p class="text-gray-600 mt-1">{{ count($data) }} rows shown (limit 100)</p>
        </div>
        <a href="{{ route('migration-guru.tables.index') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            Back to Tables List
        </a>
    </div>

    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Table Schema</h2>
    <div class="bg-white shadow rounded p-6 mb-8">
        <ul class="divide-y divide-gray-200">
            @foreach($columns as $column)
                <li class="py-2">
                    <span class="font-mono text-gray-900">{{ $column }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Table Data</h2>
    <div class="overflow-x-auto bg-white shadow rounded">
        @if($data->isEmpty())
            <p class="text-center text-gray-500 p-8">This table is empty.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $column)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $column }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($data as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="max-w-xs overflow-x-auto">
                                    {{ Str::limit($cell, 50) }}
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
</body>
</html>
