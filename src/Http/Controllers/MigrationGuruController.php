<?php

namespace Nikelioum\MigrationGuru\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Job;

class MigrationGuruController extends Controller
{
    public function index()
    {
        $migrationsPath = database_path('migrations');
        $files = [];

        if (File::isDirectory($migrationsPath)) {
            $allFiles = collect(File::files($migrationsPath))
                ->filter(fn($file) => Str::endsWith($file->getFilename(), '.php'))
                ->sortBy(fn($file) => $file->getFilename());

            $applied = [];
            try {
                $applied = DB::table('migrations')->pluck('migration')->toArray();
            } catch (\Exception $e) {
                // migrations table may not exist yet
            }

            foreach ($allFiles as $file) {
                $name = $file->getFilename();
                $migrationName = pathinfo($name, PATHINFO_FILENAME);
                $files[] = [
                    'file' => $name,
                    'name' => $migrationName,
                    'applied' => in_array($migrationName, $applied),
                ];
            }
        }

        return view('migration-guru::index', compact('files'));
    }

    public function create()
    {
        return view('migration-guru::create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'migration_name' => 'required|string',
            'table' => 'nullable|string',
            'create' => 'nullable|in:on', // Corrected validation rule
            'fields' => 'nullable|array',
        ]);

        $name = trim($request->input('migration_name'));
        $table = trim($request->input('table'));
        $isCreate = $request->has('create');
        $fields = $request->input('fields', []);

        // Backend validations for fields
        $autoIncrementCount = 0;
        foreach ($fields as $i => $field) {
            $fieldName = $field['name'] ?? null;
            $fieldType = $field['type'] ?? 'string';
            $autoIncrement = !empty($field['auto_increment']);

            if (!$fieldName) {
                return back()->withInput()->withErrors(["fields.$i.name" => "Field name is required"]);
            }

            if ($fieldType === 'string' && empty($field['length'])) {
                $fields[$i]['length'] = 255; // default length
            }

            if ($autoIncrement) {
                if ($fieldType !== 'integer') {
                    return back()->withInput()->withErrors(["fields.$i.auto_increment" => "Auto increment is only allowed for integer fields"]);
                }
                $autoIncrementCount++;
            }
        }

        if ($autoIncrementCount > 1) {
            return back()->withInput()->withErrors(['fields' => "Only one auto-increment field is allowed"]);
        }

        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_' . Str::snake($name) . '.php';

        if ($isCreate && $table) {
            $up = "            \$table->id();\n";

            foreach ($fields as $field) {
                $fieldName = $field['name'];
                $fieldType = $field['type'];
                $nullable = !empty($field['nullable']) ? '->nullable()' : '';
                $default = isset($field['default']) && $field['default'] !== '' ? "->default('{$field['default']}')" : '';
                $autoIncrement = !empty($field['auto_increment']) ? '->autoIncrement()' : '';
                $primary = !empty($field['primary']) ? '->primary()' : '';
                $unique = !empty($field['unique']) ? '->unique()' : '';
                $index = !empty($field['index']) ? '->index()' : '';
                $comment = isset($field['comment']) && $field['comment'] !== '' ? "->comment('{$field['comment']}')" : '';
                $lengthCode = $fieldType === 'string' ? ', ' . $field['length'] : '';

                $up .= "            \$table->{$fieldType}('{$fieldName}'{$lengthCode}){$nullable}{$default}{$autoIncrement}{$primary}{$unique}{$index}{$comment};\n";
            }

            $up .= "            \$table->timestamps();";
            $up = "Schema::create('$table', function (\\Illuminate\\Database\\Schema\\Blueprint \$table) {\n$up\n        });";
            $down = "Schema::dropIfExists('$table');";
        } else {
            $up = "// TODO: implement up()";
            $down = "// TODO: implement down()";
        }

        $content = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $up
    }

    public function down()
    {
        $down
    }
};
PHP;

        $path = database_path('migrations/' . $fileName);
        File::put($path, $content);

        // LOG
        $this->logAction('create', ['file' => $fileName], 'ok', 'Migration created');

        return redirect()->route('migration-guru.index')->with('status', "Migration created: $fileName");
    }

    public function run(Request $request)
    {
        $request->validate(['file' => 'required|string']);
        $file = $request->input('file');

        try {
            Artisan::call('migrate', [
                '--path' => 'database/migrations/' . $file,
                '--force' => true,
            ]);
            $output = Artisan::output();

            // LOG
            $this->logAction('run', ['file' => $file], 'ok', trim($output));

            return redirect()->route('migration-guru.index')->with('status', "Run OK: " . trim($output));
        } catch (\Exception $e) {

            // LOG
            $this->logAction('run', ['file' => $file], 'error', $e->getMessage());

            return redirect()->route('migration-guru.index')->with('error', 'Run failed: ' . $e->getMessage());
        }
    }

    public function bulkRun(Request $request)
    {
        $request->validate([
            'selected' => 'required|array'
        ]);

        $files = $request->input('selected');

        $messages = [];
        foreach ($files as $file) {
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/' . $file,
                    '--force' => true,
                ]);
                $messages[] = "Run OK: $file";

                // LOG
                $this->logAction('bulk_run', ['file' => $file], 'ok', 'Run OK');
            } catch (\Exception $e) {
                $messages[] = "Run failed ($file): " . $e->getMessage();

                // LOG
                $this->logAction('bulk_run', ['file' => $file], 'error', $e->getMessage());
            }
        }

        return redirect()->route('migration-guru.index')->with('status', implode("\n", $messages));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['selected' => 'required|array']);
        $files = $request->input('selected');

        $messages = [];
        foreach ($files as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            $applied = DB::table('migrations')->pluck('migration')->toArray();
            $wasApplied = in_array($migrationName, $applied);

            try {
                if ($wasApplied) {
                    Artisan::call('migrate:rollback', [
                        '--path' => 'database/migrations/' . $file,
                        '--force' => true,
                    ]);

                    // LOG
                    $this->logAction('rollback', ['file' => $file], 'ok', 'Rollback before delete');
                }
                File::delete(database_path('migrations/' . $file));
                $messages[] = "Deleted: $file";

                // LOG
                $this->logAction('delete', ['file' => $file], 'ok', 'File deleted');
            } catch (\Exception $e) {
                $messages[] = "Delete failed ($file): " . $e->getMessage();

                // LOG
                $this->logAction('delete', ['file' => $file], 'error', $e->getMessage());
            }
        }

        return redirect()->route('migration-guru.index')->with('status', implode("\n", $messages));
    }

    public function migrateAll(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            // LOG
            $this->logAction('migrate_all', [], 'ok', trim($output));

            return redirect()->route('migration-guru.index')->with('status', "Migrate OK: " . trim($output));
        } catch (\Exception $e) {

            // LOG
            $this->logAction('migrate_all', [], 'error', $e->getMessage());

            return redirect()->route('migration-guru.index')->with('error', 'Migrate failed: ' . $e->getMessage());
        }
    }

    public function fresh(Request $request)
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $output = Artisan::output();

            // LOG
            $this->logAction('fresh', [], 'ok', trim($output));

            return redirect()->route('migration-guru.index')->with('status', "Fresh OK: " . trim($output));
        } catch (\Exception $e) {

            // LOG
            $this->logAction('fresh', [], 'error', $e->getMessage());

            return redirect()->route('migration-guru.index')->with('error', 'Fresh failed: ' . $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $request->validate(['file' => 'required|string']);
        $file = $request->input('file');
        $migrationName = pathinfo($file, PATHINFO_FILENAME);

        $applied = DB::table('migrations')->pluck('migration')->toArray();
        $wasApplied = in_array($migrationName, $applied);

        try {
            if ($wasApplied) {
                Artisan::call('migrate:rollback', [
                    '--path' => 'database/migrations/' . $file,
                    '--force' => true,
                ]);

                // LOG
                $this->logAction('rollback', ['file' => $file], 'ok', 'Rollback before delete');
            }
            File::delete(database_path('migrations/' . $file));

            // LOG
            $this->logAction('delete', ['file' => $file], 'ok', 'File deleted');

            return redirect()->route('migration-guru.index')->with('status', "Migration deleted: $file");
        } catch (\Exception $e) {

            // LOG
            $this->logAction('delete', ['file' => $file], 'error', $e->getMessage());

            return redirect()->route('migration-guru.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function edit($file)
    {
        $path = database_path('migrations/' . $file);

        if (!File::exists($path)) {
            return redirect()->route('migration-guru.index')
                ->with('error', "Migration file not found: $file");
        }

        $content = File::get($path);

        return view('migration-guru::edit', [
            'file' => $file,
            'content' => $content,
        ]);
    }

    public function update(Request $request, $file)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $path = database_path('migrations/' . $file);

        if (!File::exists($path)) {
            return redirect()->route('migration-guru.index')
                ->with('error', "Migration file not found: $file");
        }

        try {
            File::put($path, $request->input('content'));

            // LOG
            $this->logAction('update', ['file' => $file], 'ok', 'File updated');

            return redirect()->route('migration-guru.index')
                ->with('status', "Migration updated: $file");
        } catch (\Exception $e) {

            // LOG
            $this->logAction('update', ['file' => $file], 'error', $e->getMessage());

            return redirect()->route('migration-guru.index')
                ->with('error', "Update failed ($file): " . $e->getMessage());
        }
    }

    public function lint(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = $request->input('code');

        // Save temporarily
        $tmpFile = tempnam(sys_get_temp_dir(), 'php');
        file_put_contents($tmpFile, $code);

        // Run PHP lint
        $output = shell_exec("php -l " . escapeshellarg($tmpFile) . " 2>&1");
        unlink($tmpFile);

        if (strpos($output, 'No syntax errors detected') !== false) {
            // LOG
            $this->logAction('lint', [], 'ok', 'Syntax OK');

            return response()->json(['valid' => true]);
        }

        // LOG
        $this->logAction('lint', [], 'error', $output);

        return response()->json(['valid' => false, 'error' => $output]);
    }

    public function history()
    {
        $logs = DB::table('migration_guru_logs')
            ->orderByDesc('executed_at')
            ->orderByDesc('id')
            ->simplePaginate(50);

        return view('migration-guru::history', compact('logs'));
    }

    /**
     * Minimal, model-free logger.
     */
    private function logAction(string $action, array $context = [], string $status = 'ok', ?string $message = null): void
    {
        try {
            DB::table('migration_guru_logs')->insert([
                'action'         => $action,
                'file'           => $context['file'] ?? null,
                'migration_name' => $context['migration_name']
                    ?? (isset($context['file']) ? pathinfo($context['file'], PATHINFO_FILENAME) : null),
                'status'         => $status,
                'message'        => $message,
                'user_id'        => Auth::id(),
                'ip'             => request()->ip(),
                'executed_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            // never break UX because of logging
        }
    }

    // ====================================================================================================
    // NEW Seeder Methods
    // ====================================================================================================

    /**
     * Handles the 'Migrate Fresh & Seed' button.
     * Wipes and re-migrates the entire database, then runs all seeders.
     */
    public function freshSeed(Request $request)
    {
        try {
            // Execute the 'migrate:fresh --seed' command
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            $output = Artisan::output();

            // Log the successful action
            $this->logAction('fresh_seed', [], 'ok', trim($output));

            return redirect()->route('migration-guru.index')->with('status', "Fresh & Seed OK: " . trim($output));
        } catch (\Exception $e) {

            // Log the error
            $this->logAction('fresh_seed', [], 'error', $e->getMessage());

            return redirect()->route('migration-guru.index')->with('error', 'Fresh & Seed failed: ' . $e->getMessage());
        }
    }

    /**
     * Displays the form for creating a new seeder.
     */
    public function createSeeder()
    {
        return view('migration-guru::create-seeder');
    }

    /**
     * Handles the submission of the 'create seeder' form.
     * Generates the seeder and its related files (model, factory) from stubs,
     * then immediately runs the generated seeder.
     */
    public function storeSeeder(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'seeder_type' => 'required|in:users,jobs'
        ]);

        $type = $request->input('seeder_type');
        $messages = [];
        $seederClassName = ''; // To store the class name of the seeder we create

        try {
            // === STEP 1: Generate the files ===
            if ($type === 'users') {
                $messages = array_merge($messages, $this->generateUserSeeder());
                $seederClassName = 'UserSeeder';
            }

            if ($type === 'jobs') {
                $messages = array_merge($messages, $this->generateJobSeeder());
                $seederClassName = 'JobSeeder';
            }

            // Log the file generation part of the action
            $this->logAction('create_seeder', ['seeder_type' => $type], 'ok', implode("\n", $messages));

            // === STEP 2: Run the newly created seeder ===
            try {
                // Execute the specific seeder
                Artisan::call('db:seed', [
                    '--class' => $seederClassName,
                    '--force' => true
                ]);

                $output = Artisan::output();
                $messages[] = "\nSeeder Executed Successfully:";
                $messages[] = trim($output);

                // Log the seeder execution
                $this->logAction('run_seeder', ['seeder_class' => $seederClassName], 'ok', trim($output));

            } catch (\Exception $e) {
                // If the seeder fails, report it back to the user
                $this->logAction('run_seeder', ['seeder_class' => $seederClassName], 'error', $e->getMessage());
                return redirect()->back()->with('error', "Files were created, but the seeder failed to run:\n" . $e->getMessage());
            }

            // If everything was successful, return with a success message
            return redirect()->route('migration-guru.index')->with('status', implode("\n", $messages));

        } catch (\Exception $e) {
            // Catch errors during file generation
            $this->logAction('create_seeder', ['seeder_type' => $type], 'error', $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate seeder files: ' . $e->getMessage());
        }
    }

    /**
     * Generates the User seeder, factory, and updates DatabaseSeeder from stubs.
     * @return array An array of status messages.
     */
    private function generateUserSeeder(): array
    {
        $messages = [];

        // 1. Create UserFactory from stub
        $factoryPath = database_path('factories/UserFactory.php');
        if (!File::exists($factoryPath)) {
            $stub = File::get(__DIR__ . '/../../stubs/user.factory.stub');
            File::ensureDirectoryExists(database_path('factories'));
            File::put($factoryPath, $stub);
            $messages[] = 'UserFactory.php created.';
        } else {
            $messages[] = 'UserFactory.php already exists, skipping.';
        }

        // 2. Create UserSeeder from stub
        $seederPath = database_path('seeders/UserSeeder.php');
        if (!File::exists($seederPath)) {
            $stub = File::get(__DIR__ . '/../../stubs/user.seeder.stub');
            File::ensureDirectoryExists(database_path('seeders'));
            File::put($seederPath, $stub);
            $messages[] = 'UserSeeder.php created.';

            // 3. Update DatabaseSeeder.php to include the new seeder
            $this->updateDatabaseSeeder('UserSeeder');
            $messages[] = 'DatabaseSeeder.php updated for UserSeeder.';
        } else {
            $messages[] = 'UserSeeder.php already exists, skipping.';
        }

        return $messages;
    }

    /**
     * Generates the Job model, factory, seeder, and updates DatabaseSeeder from stubs.
     * @return array An array of status messages.
     */
    private function generateJobSeeder(): array
    {
        $messages = [];

        // 1. Create Job model from stub if it doesn't exist
        $modelPath = app_path('Models/Job.php');
        if (!File::exists($modelPath)) {
            $stub = File::get(__DIR__ . '/../../stubs/job.model.stub');
            File::ensureDirectoryExists(app_path('Models'));
            File::put($modelPath, $stub);
            $messages[] = 'Job model created.';
        } else {
            $messages[] = 'Job model already exists, skipping.';
        }

        // 2. Create JobFactory from stub
        $factoryPath = database_path('factories/JobFactory.php');
        if (!File::exists($factoryPath)) {
            $stub = File::get(__DIR__ . '/../../stubs/job.factory.stub');
            File::ensureDirectoryExists(database_path('factories'));
            File::put($factoryPath, $stub);
            $messages[] = 'JobFactory.php created.';
        } else {
            $messages[] = 'JobFactory.php already exists, skipping.';
        }

        // 3. Create JobSeeder from stub
        $seederPath = database_path('seeders/JobSeeder.php');
        if (!File::exists($seederPath)) {
            $stub = File::get(__DIR__ . '/../../stubs/job.seeder.stub');
            File::ensureDirectoryExists(database_path('seeders'));
            File::put($seederPath, $stub);
            $messages[] = 'JobSeeder.php created.';

            // 4. Update DatabaseSeeder.php to include the new seeder
            $this->updateDatabaseSeeder('JobSeeder');
            $messages[] = 'DatabaseSeeder.php updated for JobSeeder.';
        } else {
            $messages[] = 'JobSeeder.php already exists, skipping.';
        }

        return $messages;
    }

    /**
     * Adds a seeder call to the main DatabaseSeeder.php file if it's not already there.
     * @param string $seederClassName The class name of the seeder to add (e.g., 'UserSeeder').
     */
    private function updateDatabaseSeeder(string $seederClassName): void
    {
        $path = database_path('seeders/DatabaseSeeder.php');
        $seederCall = '$this->call(' . $seederClassName . '::class);';

        if (File::exists($path)) {
            $content = File::get($path);
            // Avoid adding the call if it already exists
            if (!Str::contains($content, $seederCall)) {
                // Find the run() method and insert the call inside it
                $runMethod = 'public function run()';
                $position = strpos($content, $runMethod);
                if ($position !== false) {
                    $bracePosition = strpos($content, '{', $position);
                    if ($bracePosition !== false) {
                        $content = substr_replace($content, "{\n        " . $seederCall, $bracePosition, 1);
                        File::put($path, $content);
                    }
                }
            }
        }
    }

    // Manually Seeding the db

    //Displays the form for inserting a new record.
    public function createRecord()
    {
        return view('migration-guru::insert-record');
    }

    //Handles the submission of the 'insert record' form.
    public function storeRecord(Request $request)
    {
        $request->validate([
            'model_type' => 'required|in:user,job_posting'
        ]);

        $modelType = $request->input('model_type');

        try {
            if ($modelType === 'user') {
                $validated = $request->validate([
                    'fields.name' => 'required|string|max:255',
                    'fields.email' => 'required|string|email|max:255|unique:users,email',
                    'fields.password' => 'required|string|min:8',
                ]);

                $data = $validated['fields'];
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(), // Automatically verify email for convenience
                    'remember_token' => Str::random(10),
                ]);

                $message = "Successfully created User: {$data['email']}";
                $this->logAction('insert_record', ['model' => 'User', 'identifier' => $data['email']], 'ok', $message);
                return redirect()->route('migration-guru.index')->with('status', $message);
            }

            if ($modelType === 'job_posting') {
                // Ensure the Job model exists before trying to use it.
                if (!class_exists(Job::class)) {
                     return redirect()->back()->with('error', 'The Job model does not exist. Please create it first.');
                }

                $validated = $request->validate([
                    'fields.title' => 'required|string|max:255',
                    'fields.company' => 'required|string|max:255',
                    'fields.description' => 'required|string',
                ]);

                $data = $validated['fields'];
                Job::create($data);

                $message = "Successfully created Job Posting: {$data['title']}";
                $this->logAction('insert_record', ['model' => 'Job', 'identifier' => $data['title']], 'ok', $message);
                return redirect()->route('migration-guru.index')->with('status', $message);
            }

        } catch (\Exception $e) {
            $this->logAction('insert_record', ['model' => ucfirst($modelType)], 'error', $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create record: ' . $e->getMessage());
        }

        return redirect()->back()->with('error', 'Invalid model type selected.');
    }
}

