<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Migration History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <!-- Top bar -->
    <div class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap gap-2 items-center justify-between">
        <nav class="flex items-center gap-2 text-sm">
          <a href="{{ route('migration-guru.index') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600
            dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Home</a>
        </nav>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('migration-guru.create') }}" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2
            dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">New Migration</a>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-6">
        <div class="bg-white p-4 sm:p-6 lg:p-8 rounded shadow">

            <!-- Header + Filters -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Migration History</h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 w-full md:w-auto" id="filters">
                    <select id="filter-action" class="w-full px-3 py-2 border rounded text-sm">
                        <option value="">All actions</option>
                        <option value="create">create</option>
                        <option value="update">update</option>
                        <option value="run">run</option>
                        <option value="rollback">rollback</option>
                        <option value="delete">delete</option>
                        <option value="bulk_run">bulk_run</option>
                        <option value="bulk_delete">bulk_delete</option>
                        <option value="migrate_all">migrate_all</option>
                        <option value="fresh">fresh</option>
                        <option value="lint">lint</option>
                    </select>
                    <select id="filter-status" class="w-full px-3 py-2 border rounded text-sm">
                        <option value="">All statuses</option>
                        <option value="ok">ok</option>
                        <option value="error">error</option>
                    </select>
                    <input id="filter-search" type="text" placeholder="Search message/file…" class="w-full px-3 py-2 border rounded text-sm" />
                    <button id="clear-filters" class="px-3 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">Clear</button>
                </div>
            </div>

            <!-- Mobile list (cards) -->
            <div class="space-y-3 md:hidden" id="mobile-list">
                @forelse($logs as $log)
                <div class="border rounded p-3 log-card"
                     data-action="{{ $log->action }}"
                     data-status="{{ $log->status }}"
                     data-search="{{ strtolower(($log->file ?? '').' '.($log->message ?? '')) }}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-sm font-semibold text-gray-800">{{ $log->action }}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $log->status === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $log->status }}
                        </span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">{{ $log->executed_at }}</div>
                    <div class="mt-2 text-sm text-gray-700"><span class="font-medium">File:</span> {{ $log->file ?? '—' }}</div>
                    <div class="mt-1 text-sm text-gray-700"><span class="font-medium">User:</span> {{ $log->user_id ?? '—' }}</div>
                    <div class="mt-2 text-sm text-gray-700 whitespace-pre-wrap break-words">{{ $log->message }}</div>
                    <div class="mt-2 text-xs text-gray-500"><span class="font-medium">IP:</span> {{ $log->ip }}</div>
                </div>
                @empty
                <div class="text-center text-gray-500 py-10">No logs yet.</div>
                @endforelse
            </div>

            <!-- Desktop table -->
            <div class="hidden md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider p-3 border-b">Time</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider p-3 border-b">Action</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider p-3 border-b">File</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider p-3 border-b">Status</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider p-3 border-b">User</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider p-3 border-b">Message</th>
                                <th class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider p-3 border-b">IP</th>
                            </tr>
                        </thead>
                        <tbody id="log-body">
                            @forelse($logs as $log)
                            <tr class="border-b log-row"
                                data-action="{{ $log->action }}"
                                data-status="{{ $log->status }}"
                                data-search="{{ strtolower(($log->file ?? '').' '.($log->message ?? '')) }}">
                                <td class="p-3 align-top text-sm text-gray-800 whitespace-nowrap">{{ $log->executed_at }}</td>
                                <td class="p-3 align-top text-sm text-gray-800">{{ $log->action }}</td>
                                <td class="p-3 align-top text-sm text-gray-700">{{ $log->file ?? '—' }}</td>
                                <td class="p-3 align-top">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $log->status === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="p-3 align-top text-sm text-gray-700">{{ $log->user_id ?? '—' }}</td>
                                <td class="p-3 align-top text-sm text-gray-700">
                                    <div class="max-w-xl whitespace-pre-wrap break-words">{{ $log->message }}</div>
                                </td>
                                <td class="p-3 align-top text-sm text-gray-700">{{ $log->ip }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">No logs yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            </div>

        </div>
    </div>

    <script>
        const qSel = (s) => document.querySelector(s);
        const qAll = (s) => Array.from(document.querySelectorAll(s));

        const actionSel  = qSel('#filter-action');
        const statusSel  = qSel('#filter-status');
        const searchInp  = qSel('#filter-search');
        const clearBtn   = qSel('#clear-filters');

        function applyFilters() {
            const action = actionSel.value;
            const status = statusSel.value;
            const q = (searchInp.value || '').toLowerCase().trim();

            qAll('.log-row').forEach(tr => {
                const okAction = !action || tr.dataset.action === action;
                const okStatus = !status || tr.dataset.status === status;
                const okSearch = !q || (tr.dataset.search || '').includes(q);
                tr.style.display = (okAction && okStatus && okSearch) ? '' : 'none';
            });

            qAll('.log-card').forEach(card => {
                const okAction = !action || card.dataset.action === action;
                const okStatus = !status || card.dataset.status === status;
                const okSearch = !q || (card.dataset.search || '').includes(q);
                card.style.display = (okAction && okStatus && okSearch) ? '' : 'none';
            });
        }

        actionSel.addEventListener('change', applyFilters);
        statusSel.addEventListener('change', applyFilters);
        searchInp.addEventListener('input', applyFilters);
        clearBtn.addEventListener('click', () => {
            actionSel.value = '';
            statusSel.value = '';
            searchInp.value = '';
            applyFilters();
        });
    </script>

</body>
</html>
