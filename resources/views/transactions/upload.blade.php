@extends('layouts.app')

@section('title', 'Upload CSV')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Import Transactions</h1>
        <p class="mt-2 text-gray-600">Upload a CSV file to import your transaction data.</p>
    </div>

    <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <label for="csv_file" id="drop-zone"
               class="group relative block cursor-pointer rounded-xl border-2 border-dashed border-gray-300 bg-white p-12 text-center transition-all duration-200 hover:border-indigo-400 hover:bg-indigo-50/50 hover:shadow-md">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 transition-colors group-hover:bg-indigo-100">
                <svg class="h-7 w-7 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
            </div>
            <div class="mt-4">
                <span class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors group-hover:bg-indigo-500">
                    Choose file
                </span>
                <input id="csv_file" name="csv_file" type="file" accept=".csv" class="sr-only">
                <span class="ml-3 text-sm text-gray-500">or drag and drop</span>
            </div>
            <div id="file-info" class="mt-3 hidden">
                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <span id="file-name" class="text-sm font-medium text-indigo-700"></span>
                    <span id="file-size" class="text-xs text-indigo-400"></span>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-400">CSV files only, up to 2MB</p>
        </label>

        @error('csv_file')
            <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="flex items-center">
                    <svg class="mr-3 h-5 w-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-700">{{ $message }}</p>
                </div>
            </div>
        @enderror

        <button type="submit"
                class="w-full rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-500 hover:shadow-md active:scale-[0.98] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Upload &amp; Import
        </button>
    </form>

    @if ($imports->isNotEmpty())
        <div class="mt-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-900">Import History</h3>
            </div>
            <p class="mt-1 text-xs text-gray-500">Previously imported files. Delete an import to remove its transactions and re-upload.</p>
            <ul class="mt-4 divide-y divide-gray-100">
                @foreach ($imports as $import)
                    <li class="flex items-center justify-between gap-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $import->original_filename }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $import->row_count }} {{ Str::plural('row', $import->row_count) }}
                                    &middot; {{ $import->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('imports.destroy', $import) }}" method="POST"
                              onsubmit="return confirm('Delete this import and all its transactions?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-md px-3 py-1.5 text-xs font-medium text-red-600 ring-1 ring-red-200 transition-colors hover:bg-red-50 hover:ring-red-300">
                                Delete
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Expected CSV format</h3>
        </div>
        <p class="mt-1 text-xs text-gray-500">Your file should contain these column headers (case-insensitive):</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (['Date', 'Description', 'Amount', 'Business', 'Category', 'Transaction_Type', 'Source', 'Status'] as $col)
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-mono font-medium text-indigo-700 ring-1 ring-inset ring-indigo-200">
                    {{ $col }}
                </span>
            @endforeach
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('csv_file');
    const fileInfo = document.getElementById('file-info');
    const fileNameEl = document.getElementById('file-name');
    const fileSizeEl = document.getElementById('file-size');

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    function showFile(file) {
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = '(' + formatSize(file.size) + ')';
        fileInfo.classList.remove('hidden');
    }

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) showFile(this.files[0]);
    });

    ['dragenter', 'dragover'].forEach(event => {
        dropZone.addEventListener(event, function (e) {
            e.preventDefault();
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50', 'shadow-md');
        });
    });

    ['dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50', 'shadow-md');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            showFile(e.dataTransfer.files[0]);
        }
    });
});
</script>
@endsection
