<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Seeder - Migration Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <div class="container mx-auto my-12 max-w-2xl">
        <div class="bg-white p-8 rounded shadow">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold mb-6 text-gray-800">Create Seeder</h1>
                <a href="{{ route('migration-guru.records.create') }}" class="mb-6 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">
                    Manually Seed
                </a>
            </div>


            <p class="mb-6 text-gray-600">
                Select a pre-defined seeder to generate. This will create the necessary Model, Factory, and Seeder files,
                and update <code>DatabaseSeeder.php</code> to include the new seeder.
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
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('migration-guru.seeders.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="seeder_type" class="block font-medium mb-2">Seeder Type</label>
                    <select name="seeder_type" id="seeder_type" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
                        <option value="" disabled selected>-- Select a seeder --</option>
                        <option value="users">Users Seeder (Creates UserFactory, UserSeeder)</option>
                        <option value="jobs">Jobs Seeder (Creates Job Model, JobFactory, JobSeeder)</option>
                    </select>
                </div>

                <div class="mt-6 flex items-center space-x-3">
                    <button type="submit"
                            class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700 transition">
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

</body>
</html>
