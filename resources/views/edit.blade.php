<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Migration - Migration Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/material.min.css">

    <!-- CodeMirror JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/css/css.min.js"></script>

    <!-- Addons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/selection/active-line.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen font-sans">

    <div class="container mx-auto my-12 max-w-5xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Editing: {{ $file }}</h1>

        <div id="lint-error" class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 border border-red-200 hidden"></div>
        <div id="lint-success"
            class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-200 hidden">No syntax errors
            detected!</div>

        <form id="edit-form" action="{{ route('migration-guru.update', $file) }}" method="POST">
            @csrf
            <textarea id="code" name="content" class="hidden">{{ $content }}</textarea>
            <div class="flex mt-4">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition mr-2">
                    Save Changes
                </button>
                <button type="button" id="check-syntax"
                    class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition mr-2">
                    Check Syntax
                </button>
                <a href="{{ route('migration-guru.index') }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        const editor = CodeMirror.fromTextArea(document.getElementById("code"), {
            lineNumbers: true,
            mode: "application/x-httpd-php",
            matchBrackets: true,
            styleActiveLine: true,
            theme: "material",
            indentUnit: 4,
            tabSize: 8,
            indentWithTabs: true,
        });

        const cmWrapper = editor.getWrapperElement();
        cmWrapper.style.resize = "vertical";
        cmWrapper.style.overflow = "auto";
        cmWrapper.style.height = "600px";

        const observer = new ResizeObserver(() => editor.refresh());
        observer.observe(cmWrapper);

        document.querySelector("#edit-form").addEventListener("submit", () => {
            document.getElementById("code").value = editor.getValue();
        });

        document.getElementById("check-syntax").addEventListener("click", async () => {
            const code = editor.getValue();
            const response = await fetch("{{ route('migration-guru.lint') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code })
            });
            const result = await response.json();

            const errorDiv = document.getElementById("lint-error");
            const successDiv = document.getElementById("lint-success");

            if (result.valid) {
                successDiv.style.display = "block";
                errorDiv.style.display = "none";
            } else {
                errorDiv.textContent = result.error;
                errorDiv.style.display = "block";
                successDiv.style.display = "none";
            }
        });
    </script>

    <style>
        .CodeMirror {
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            overflow: auto;
        }
    </style>

</body>

</html>