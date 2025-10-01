<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Migration Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<div class="container mx-auto my-12 max-w-5xl">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Migration Guru</h1>

    <!-- Status Messages -->
    @if(session('status'))
        <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-200">
            {{ session('status') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Actions -->
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('migration-guru.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Create New Migration</a>

        <form action="{{ route('migration-guru.migrateAll') }}" method="POST">
            @csrf
            <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Run All Migrations</button>
        </form>

        <form action="{{ route('migration-guru.fresh') }}" method="POST">
            @csrf
            <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Migrate Fresh</button>
        </form>

        <form action="{{ route('migration-guru.freshSeed') }}" method="POST" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('Are you sure you want to drop all tables, re-migrate, AND run all seeders?')"
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">Migrate Fresh & Seed</button>
        </form>

        <button>
            <a href="{{ route('migration-guru.history') }}"
            class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium
            rounded text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                View History
            </a>
        </button>

        <button>
            <a href="{{ route('migration-guru.seeders.create') }}"
            class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white px-5 py-2.5 me-2 mb-2 border border-blue-500 hover:border-transparent rounded">
                Seeders
            </a>
        </button>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex flex-wrap gap-3 items-end">
        <input type="text" id="search-input" placeholder="Search migration or table..."
               class="px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
        <select id="status-filter" class="px-3 py-2 border rounded">
            <option value="">All Status</option>
            <option value="Applied">Applied</option>
            <option value="Pending">Pending</option>
        </select>
    </div>

    <!-- Bulk Actions + Table inside the same form -->
    <form method="POST" id="bulk-form">
        @csrf
        <div class="mb-4 flex flex-wrap gap-3">
            <button type="submit" formaction="{{ route('migration-guru.bulkRun') }}"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Run Selected</button>
            <button type="submit" formaction="{{ route('migration-guru.bulkDelete') }}"
                    onclick="return confirm('Are you sure you want to delete selected migrations?')"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Delete Selected</button>
        </div>

        <!-- Migrations Table -->
        <div class="overflow-x-auto bg-white shadow rounded">
            <table id="migrations-table" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="select-all" class="form-checkbox">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Migration File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($files as $file)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected[]" value="{{ $file['file'] }}" class="form-checkbox row-checkbox">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap migration-name">{{ $file['file'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap migration-status">
                            @if($file['applied'])
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Applied</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if(!$file['applied'])
                                <form action="{{ route('migration-guru.run') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="file" value="{{ $file['file'] }}">
                                    <button type="submit"
                                            class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition text-sm">Run</button>
                                </form>
                            @else
                                <form action="{{ route('migration-guru.delete') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="file" value="{{ $file['file'] }}">
                                    <button type="submit"
                                            onclick="return confirm('Are you sure you want to delete this migration?')"
                                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm">Delete</button>
                                </form>
                            @endif
                            <!-- Edit button (always available) -->
    <a href="{{ route('migration-guru.edit', $file['file']) }}"
       class="ml-2 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition text-sm">
        Edit
    </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No migrations found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
    // Select all checkboxes
    const selectAll = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => cb.checked = this.checked);
    });

    // Front-end filtering
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const tableRows = document.querySelectorAll('#migrations-table tbody tr');

    function filterRows() {
        const searchValue = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        tableRows.forEach(row => {
            const name = row.querySelector('.migration-name').textContent.toLowerCase();
            const status = row.querySelector('.migration-status span').textContent;

            const matchesSearch = name.includes(searchValue);
            const matchesStatus = statusValue === '' || status === statusValue;

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterRows);
    statusFilter.addEventListener('change', filterRows);
</script>

</body>
</html>
