<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateImportException;
use App\Exceptions\InvalidCsvFormatException;
use App\Http\Requests\CsvUploadRequest;
use App\Models\CsvImport;
use App\Services\CsvImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CsvImportController extends Controller
{
    public function __construct(
        private readonly CsvImportService $importService,
    ) {}

    public function create(): View
    {
        $imports = CsvImport::latest()->get();

        return view('transactions.upload', compact('imports'));
    }

    public function store(CsvUploadRequest $request): RedirectResponse
    {
        try {
            $import = $this->importService->import($request->file('csv_file'));

            return redirect()
                ->route('transactions.index')
                ->with('success', "Successfully imported {$import->row_count} transactions from {$import->original_filename}.");
        } catch (DuplicateImportException|InvalidCsvFormatException $e) {
            return back()->withErrors(['csv_file' => $e->getMessage()]);
        }
    }

    public function destroy(CsvImport $import): RedirectResponse
    {
        $filename = $import->original_filename;
        $count = $import->row_count;
        $import->delete();

        return redirect()
            ->route('upload.create')
            ->with('success', "Removed {$count} transactions from {$filename}.");
    }
}
