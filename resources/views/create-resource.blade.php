<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resource Generator - Migration Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <div class="container mx-auto my-12 max-w-4xl">
        <div class="bg-white p-8 rounded shadow">
            <h1 class="text-2xl font-bold mb-2 text-gray-800">All-in-One Resource Generator</h1>
            <p class="mb-6 text-gray-600">
                Define your model and its fields here to generate the Model, Migration, and Factory all at once.
            </p>

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

            <form action="{{ route('migration-guru.resource.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="model_name" class="block font-medium mb-1">Model Name</label>
                        <input type="text" name="model_name" id="model_name" value="{{ old('model_name') }}"
                               class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                               placeholder="e.g., Product, BlogPost" required>
                        <p class="text-sm text-gray-500 mt-1">Use singular PascalCase. This will generate table `products`, factory `ProductFactory`, etc.</p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="create_migration" id="create_migration_cb" class="form-checkbox" checked>
                            <span class="ml-2">Create Migration</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="create_factory" id="create_factory_cb" class="form-checkbox" checked>
                            <span class="ml-2">Create Factory</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="create_seeder" id="create_seeder_cb" class="form-checkbox" checked>
                            <span class="ml-2">Create Seeder</span>
                        </label>
                    </div>
                </div>

                <hr class="my-6">

                <h3 class="text-lg font-semibold mb-3">Database Fields</h3>
                <div id="fields-container" class="space-y-3">
                    <!-- Fields will be added here by JavaScript -->
                </div>

                <button type="button" onclick="addField()"
                        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Field</button>

                <div class="mt-8 flex items-center space-x-3">
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">
                        Generate & Run
                    </button>
                    <a href="{{ route('migration-guru.index') }}"
                       class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let fieldIndex = 0;

        function addField() {
            const container = document.getElementById('fields-container');
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-10 gap-2 items-center';
            row.innerHTML = `
                <input type="text" name="fields[${fieldIndex}][name]" placeholder="Field Name" required class="col-span-3 px-2 py-1 border rounded">
                <select name="fields[${fieldIndex}][type]" class="col-span-3 px-2 py-1 border rounded">
                    <option value="string">string</option>
                    <option value="integer">integer</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="float">float</option>
                    <option value="decimal">decimal</option>
                    <option value="json">json</option>
                    <option value="foreignId">foreignId</option>
                </select>
                <label class="col-span-3 flex items-center space-x-1">
                    <input type="checkbox" name="fields[${fieldIndex}][nullable]" value="1" class="form-checkbox">
                    <span>Nullable</span>
                </label>
                <button type="button" onclick="removeField(this)" class="col-span-1 px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition text-sm">Remove</button>
            `;
            container.appendChild(row);
            fieldIndex++;
        }

        function removeField(button) {
            button.parentElement.remove();
        }

        const migrationCheckbox = document.getElementById('create_migration_cb');
        const factoryCheckbox = document.getElementById('create_factory_cb');
        const seederCheckbox = document.getElementById('create_seeder_cb');

        function toggleSeederCheckbox() {
            // Seeder requires both a migration (for the table) and a factory (to create data)
            if (factoryCheckbox.checked && migrationCheckbox.checked) {
                seederCheckbox.disabled = false;
            } else {
                seederCheckbox.disabled = true;
                seederCheckbox.checked = false;
            }
        }

        factoryCheckbox.addEventListener('change', toggleSeederCheckbox);
        migrationCheckbox.addEventListener('change', toggleSeederCheckbox);

        window.addEventListener('DOMContentLoaded', () => {
             @if(!old('fields'))
                addField();
            @else
                const oldFields = @json(old('fields', []));
                oldFields.forEach(fieldData => {
                    addField();
                    const newRow = document.querySelector('#fields-container > div:last-child');
                    newRow.querySelector(`[name$="[name]"]`).value = fieldData.name || '';
                    newRow.querySelector(`[name$="[type]"]`).value = fieldData.type || 'string';
                    if (fieldData.nullable) {
                         newRow.querySelector(`[name$="[nullable]"]`).checked = true;
                    }
                });
            @endif
            toggleSeederCheckbox();
        });
    </script>
</body>
</html>

