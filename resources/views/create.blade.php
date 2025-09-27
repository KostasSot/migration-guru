<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Migration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen font-sans">

    <div class="container mx-auto my-12 max-w-4xl">
        <div class="bg-white p-8 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Migration</h1>

            <!-- Errors -->
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('migration-guru.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium mb-1">Migration Name</label>
                    <input type="text" name="migration_name" value="{{ old('migration_name') }}"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                        placeholder="e.g. create_users_table" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Table Name (optional)</label>
                    <input type="text" name="table" value="{{ old('table') }}"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                        placeholder="users">
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="create" class="form-checkbox" {{ old('create') ? 'checked' : '' }}>
                        <span class="ml-2">Generate create table scaffold</span>
                    </label>
                </div>

                <hr class="my-6">

                <h3 class="text-lg font-semibold mb-3">Fields</h3>

                <div id="fields-container" class="space-y-3">

                    <!-- First default field -->
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-2 items-center">
                        <input type="text" name="fields[0][name]" placeholder="Field Name" required
                            class="col-span-1 px-2 py-1 border rounded">
                        <select name="fields[0][type]" class="col-span-1 px-2 py-1 border rounded"
                            onchange="toggleFieldOptions(this)">
                            <option value="string">string</option>
                            <option value="integer">integer</option>
                            <option value="text">text</option>
                            <option value="boolean">boolean</option>
                            <option value="date">date</option>
                            <option value="datetime">datetime</option>
                            <option value="float">float</option>
                            <option value="decimal">decimal</option>
                            <option value="json">json</option>
                        </select>
                        <input type="number" name="fields[0][length]" placeholder="Length" value="255"
                            class="col-span-1 px-2 py-1 border rounded string-length">
                        <label class="col-span-1 flex items-center space-x-1">
                            <input type="checkbox" name="fields[0][nullable]" value="1" class="form-checkbox">
                            <span>Nullable</span>
                        </label>
                        <label class="col-span-1 flex items-center space-x-1">
                            <input type="checkbox" name="fields[0][auto_increment]" value="1"
                                class="form-checkbox ai-checkbox">
                            <span>Auto Increment</span>
                        </label>
                        <button type="button" onclick="removeField(this)"
                            class="col-span-1 px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm">Remove</button>
                    </div>

                </div>

                <button type="button" onclick="addField()"
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add
                    Field</button>

                <div class="mt-6 flex flex-col md:flex-row md:items-center md:space-x-3 space-y-2 md:space-y-0">
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Create</button>
                    <a href="{{ route('migration-guru.index') }}"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let fieldIndex = 1;
        let aiUsed = false;

        function addField() {
            const container = document.getElementById('fields-container');
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-6 gap-2 items-center';
            row.innerHTML = `
        <input type="text" name="fields[${fieldIndex}][name]" placeholder="Field Name" required class="col-span-1 px-2 py-1 border rounded">
        <select name="fields[${fieldIndex}][type]" class="col-span-1 px-2 py-1 border rounded" onchange="toggleFieldOptions(this)">
            <option value="string">string</option>
            <option value="integer">integer</option>
            <option value="text">text</option>
            <option value="boolean">boolean</option>
            <option value="date">date</option>
            <option value="datetime">datetime</option>
            <option value="float">float</option>
            <option value="decimal">decimal</option>
            <option value="json">json</option>
        </select>
        <input type="number" name="fields[${fieldIndex}][length]" placeholder="Length" value="255" class="col-span-1 px-2 py-1 border rounded string-length">
        <label class="col-span-1 flex items-center space-x-1">
            <input type="checkbox" name="fields[${fieldIndex}][nullable]" value="1" class="form-checkbox">
            <span>Nullable</span>
        </label>
        <label class="col-span-1 flex items-center space-x-1">
            <input type="checkbox" name="fields[${fieldIndex}][auto_increment]" value="1" class="form-checkbox ai-checkbox">
            <span>Auto Increment</span>
        </label>
        <button type="button" onclick="removeField(this)" class="col-span-1 px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm">Remove</button>
    `;
            container.appendChild(row);
            toggleFieldOptions(row.querySelector('select'));
            fieldIndex++;
        }

        function removeField(button) {
            const row = button.parentElement;
            const aiCheckbox = row.querySelector('.ai-checkbox');
            if (aiCheckbox && aiCheckbox.checked) aiUsed = false;
            row.remove();
            updateAiCheckboxes();
        }

        function toggleFieldOptions(select) {
            const row = select.parentElement;
            const lengthInput = row.querySelector('.string-length');
            const aiCheckbox = row.querySelector('.ai-checkbox');
            const scaffoldChecked = document.querySelector('input[name="create"]').checked;

            // Show length input only for string
            lengthInput.style.display = select.value === 'string' ? 'inline-block' : 'none';

            // Auto Increment rules
            if (aiCheckbox) {
                if (scaffoldChecked) {
                    aiCheckbox.checked = false;
                    aiCheckbox.disabled = true;
                } else {
                    aiCheckbox.disabled = select.value !== 'integer' || aiUsed;
                }
            }
        }

        function updateAiCheckboxes() {
            document.querySelectorAll('#fields-container .ai-checkbox').forEach(cb => {
                if (!cb.checked) {
                    const typeSelect = cb.closest('div').querySelector('select');
                    cb.disabled = typeSelect.value !== 'integer' || aiUsed;
                }
            });
        }

        document.addEventListener('change', function (e) {
            if (e.target.matches('.ai-checkbox')) {
                aiUsed = document.querySelectorAll('.ai-checkbox:checked').length > 0;
                updateAiCheckboxes();
            }
            if (e.target.matches('input[name="create"]')) {
                document.querySelectorAll('#fields-container select').forEach(toggleFieldOptions);
            }
        });

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#fields-container select').forEach(toggleFieldOptions);
            aiUsed = document.querySelectorAll('.ai-checkbox:checked').length > 0;
            updateAiCheckboxes();
        });
    </script>

</body>

</html>