<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Tables - Migration Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<div class="container mx-auto my-12 max-w-5xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Database Tables</h1>
        <a href="{{ route('migration-guru.index') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            Back to Migrations
        </a>
    </div>

    @if(session('status'))
        <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-200">
            {!! nl2br(e(session('status'))) !!}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 border border-red-200">
            {!! nl2br(e(session('error'))) !!}
        </div>
    @endif

    <div class="bg-white shadow rounded p-8">
        @if(empty($tables))
            <p class="text-center text-gray-500">No tables found in the database.</p>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($tables as $table)
                    <li class="py-4 flex justify-between items-center">
                        <span class="text-lg font-mono text-gray-700">{{ $table }}</span>
                        <a href="{{ route('migration-guru.tables.view', $table) }}"
                           class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition text-sm">
                            View
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>
</body>
</html>
