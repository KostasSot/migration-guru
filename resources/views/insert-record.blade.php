<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insert Record - Migration Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <div class="container mx-auto my-12 max-w-2xl">
        <div class="bg-white p-8 rounded shadow">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Insert New Record</h1>

            <p class="mb-6 text-gray-600">
                Select a model and fill out the form to insert a single new record directly into the database.
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
             @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded">
                    {!! nl2br(e(session('error'))) !!}
                </div>
            @endif

            <form action="{{ route('migration-guru.records.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="model_type" class="block font-medium mb-2">Model Type</label>
                    <select name="model_type" id="model_type" required onchange="showFields(this.value)"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
                        <option value="" disabled selected>-- Select a model --</option>
                        <option value="user">User</option>
                        <option value="job_posting">Job Posting</option>
                    </select>
                </div>

                <!-- User Fields -->
                <div id="user-fields" class="hidden space-y-4">
                     <h3 class="text-lg font-semibold border-b pb-2">User Details</h3>
                    <div>
                        <label class="block font-medium mb-1">Name</label>
                        <input type="text" name="fields[name]" value="{{ old('fields.name') }}" class="w-full px-3 py-2 border rounded">
                    </div>
                     <div>
                        <label class="block font-medium mb-1">Email</label>
                        <input type="email" name="fields[email]" value="{{ old('fields.email') }}" class="w-full px-3 py-2 border rounded">
                    </div>
                     <div>
                        <label class="block font-medium mb-1">Password</label>
                        <div class="relative">
                            <input type="password" id="password-input" name="fields[password]" class="w-full px-3 py-2 border rounded pr-10">
                            <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none">
                                <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Job Posting Fields -->
                <div id="job_posting-fields" class="hidden space-y-4">
                     <h3 class="text-lg font-semibold border-b pb-2">Job Posting Details</h3>
                     <div>
                        <label class="block font-medium mb-1">Title</label>
                        <input type="text" name="fields[title]" value="{{ old('fields.title') }}" class="w-full px-3 py-2 border rounded">
                    </div>
                     <div>
                        <label class="block font-medium mb-1">Company</label>
                        <input type="text" name="fields[company]" value="{{ old('fields.company') }}" class="w-full px-3 py-2 border rounded">
                    </div>
                     <div>
                        <label class="block font-medium mb-1">Description</label>
                        <textarea name="fields[description]" class="w-full px-3 py-2 border rounded h-24">{{ old('fields.description') }}</textarea>
                    </div>
                </div>


                <div class="mt-6 flex items-center space-x-3">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Submit
                    </button>
                    <a href="{{ route('migration-guru.index') }}"
                       class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showFields(model) {
            // Hide all fieldsets first
            document.getElementById('user-fields').style.display = 'none';
            document.getElementById('job_posting-fields').style.display = 'none';

            // Disable all inputs to prevent them from being submitted
            document.querySelectorAll('#user-fields input, #user-fields textarea').forEach(el => el.disabled = true);
            document.querySelectorAll('#job_posting-fields input, #job_posting-fields textarea').forEach(el => el.disabled = true);


            if (model) {
                const fieldset = document.getElementById(model + '-fields');
                if (fieldset) {
                    fieldset.style.display = 'block';
                     // Re-enable inputs for the visible fieldset
                    fieldset.querySelectorAll('input, textarea').forEach(el => el.disabled = false);
                }
            }
        }

        // Handle form resubmission with old input
        document.addEventListener('DOMContentLoaded', function() {
            const selectedModel = '{{ old('model_type') }}';
            if (selectedModel) {
                document.getElementById('model_type').value = selectedModel;
                showFields(selectedModel);
            }
        });

        // NEW: Password toggle functionality
        const passwordInput = document.getElementById('password-input');
        const togglePasswordButton = document.getElementById('toggle-password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');

        if (togglePasswordButton) {
            togglePasswordButton.addEventListener('click', function () {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle the eye icons
                eyeOpen.classList.toggle('hidden');
                eyeClosed.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>

