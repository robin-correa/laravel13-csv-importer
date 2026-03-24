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
               class="relative block cursor-pointer rounded-xl border-2 border-dashed border-gray-300 bg-white p-12 text-center transition-colors hover:border-indigo-400 hover:bg-indigo-50/50">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"/>
            </svg>
            <div class="mt-4">
                <span class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Choose file
                </span>
                <input id="csv_file" name="csv_file" type="file" accept=".csv" class="sr-only">
                <span class="ml-3 text-sm text-gray-500">or drag and drop</span>
            </div>
            <p id="file-name" class="mt-3 text-sm font-medium text-indigo-600 hidden"></p>
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
                class="w-full rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Upload &amp; Import
        </button>
    </form>

    @if ($imports->isNotEmpty())
        <div class="mt-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
            <h3 class="text-sm font-semibold text-gray-900">Import History</h3>
            <p class="mt-1 text-xs text-gray-500">Previously imported files. Delete an import to remove its transactions and re-upload.</p>
            <ul class="mt-4 divide-y divide-gray-100">
                @foreach ($imports as $import)
                    <li class="flex items-center justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900">{{ $import->original_filename }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $import->row_count }} {{ Str::plural('row', $import->row_count) }}
                                &middot; {{ $import->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <form action="{{ route('imports.destroy', $import) }}" method="POST"
                              onsubmit="return confirm('Delete this import and all its transactions?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-md px-3 py-1.5 text-xs font-medium text-red-600 ring-1 ring-red-200 transition-colors hover:bg-red-50">
                                Delete
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
        <h3 class="text-sm font-semibold text-gray-900">Expected CSV format</h3>
        <p class="mt-1 text-xs text-gray-500">Your file should contain these column headers:</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (['Date', 'Description', 'Amount', 'Business', 'Category', 'Transaction_Type', 'Source', 'Status'] as $col)
                <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
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
    const fileNameEl = document.getElementById('file-name');

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            fileNameEl.textContent = this.files[0].name;
            fileNameEl.classList.remove('hidden');
        }
    });

    ['dragenter', 'dragover'].forEach(event => {
        dropZone.addEventListener(event, function (e) {
            e.preventDefault();
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        });
    });

    ['dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            fileNameEl.textContent = e.dataTransfer.files[0].name;
            fileNameEl.classList.remove('hidden');
        }
    });
});
</script>
@endsection
