<?php

namespace Nikelioum\MigrationGuru\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            'create' => 'nullable|in:on',
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
            $up = "Schema::create('$table', function (\\Illuminate\\Database\\Schema\Blueprint \$table) {\n$up\n        });";
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
            return redirect()->route('migration-guru.index')->with('status', "Run OK: " . trim($output));
        } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                $messages[] = "Run failed ($file): " . $e->getMessage();
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
                }
                File::delete(database_path('migrations/' . $file));
                $messages[] = "Deleted: $file";
            } catch (\Exception $e) {
                $messages[] = "Delete failed ($file): " . $e->getMessage();
            }
        }

        return redirect()->route('migration-guru.index')->with('status', implode("\n", $messages));
    }


    public function migrateAll(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return redirect()->route('migration-guru.index')->with('status', "Migrate OK: " . trim($output));
        } catch (\Exception $e) {
            return redirect()->route('migration-guru.index')->with('error', 'Migrate failed: ' . $e->getMessage());
        }
    }

    public function fresh(Request $request)
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $output = Artisan::output();
            return redirect()->route('migration-guru.index')->with('status', "Fresh OK: " . trim($output));
        } catch (\Exception $e) {
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
            }
            File::delete(database_path('migrations/' . $file));
            return redirect()->route('migration-guru.index')->with('status', "Migration deleted: $file");
        } catch (\Exception $e) {
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
            return redirect()->route('migration-guru.index')
                ->with('status', "Migration updated: $file");
        } catch (\Exception $e) {
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
            return response()->json(['valid' => true]);
        }

        return response()->json(['valid' => false, 'error' => $output]);
    }

}
