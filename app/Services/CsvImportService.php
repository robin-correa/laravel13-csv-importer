<?php

namespace App\Services;

use App\Exceptions\DuplicateImportException;
use App\Exceptions\InvalidCsvFormatException;
use App\Models\CsvImport;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class CsvImportService
{
    private const array EXPECTED_HEADERS = [
        'date',
        'description',
        'amount',
        'business',
        'category',
        'transaction_type',
        'source',
        'status',
    ];

    public function import(UploadedFile $file): CsvImport
    {
        $hash = hash_file('sha256', $file->getRealPath());

        if (CsvImport::where('file_hash', $hash)->exists()) {
            throw new DuplicateImportException(
                'This file has already been imported.'
            );
        }

        $csv = Reader::createFromPath($file->getRealPath());
        $csv->setHeaderOffset(0);

        $headerMap = $this->normalizeHeaders($csv->getHeader());
        $this->validateHeaders(array_keys($headerMap));

        $rows = $this->parseRecords(iterator_to_array($csv->getRecords()), $headerMap);

        if (count($rows) === 0) {
            throw new InvalidCsvFormatException(
                'The CSV file contains no data rows.'
            );
        }

        return DB::transaction(function () use ($file, $hash, $rows) {
            $now = now();

            $csvImport = CsvImport::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_hash' => $hash,
                'row_count' => count($rows),
            ]);

            $records = array_map(fn (array $row) => array_merge($row, [
                'csv_import_id' => $csvImport->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]), $rows);

            // Chunk to avoid hitting MySQL's max_allowed_packet on large files
            foreach (array_chunk($records, 500) as $chunk) {
                Transaction::insert($chunk);
            }

            return $csvImport;
        });
    }

    /**
     * Maps records to transaction arrays, silently skipping fully blank rows.
     * Partial rows (some fields blank) are imported as-is.
     *
     * @return array<int, array<string, mixed>>
     */
    private function parseRecords(array $records, array $headerMap): array
    {
        $rows = [];

        foreach ($records as $record) {
            $row = $this->mapRecord($record, $headerMap);

            // Skip fully blank rows (e.g. trailing newlines in the file)
            if ($this->isBlankRow($row)) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function isBlankRow(array $row): bool
    {
        return collect($row)->every(fn ($v) => $v === null || trim((string) $v) === '');
    }

    /**
     * Returns a map of normalizedKey => originalKey so records can be
     * accessed by the original header even after case/trim normalization.
     */
    private function normalizeHeaders(array $headers): array
    {
        $map = [];

        foreach ($headers as $header) {
            $normalized = strtolower(trim(str_replace(' ', '_', $header)));
            $map[$normalized] = $header;
        }

        return $map;
    }

    private function validateHeaders(array $normalizedKeys): void
    {
        $missing = array_diff(self::EXPECTED_HEADERS, $normalizedKeys);

        if (! empty($missing)) {
            throw new InvalidCsvFormatException(
                'CSV is missing required columns: '.implode(', ', $missing)
            );
        }
    }

    private function mapRecord(array $record, array $headerMap): array
    {
        $get = fn (string $key) => isset($headerMap[$key]) ? ($record[$headerMap[$key]] ?? null) : null;

        return [
            'date' => $get('date'),
            'description' => $get('description'),
            'amount' => $get('amount'),
            'business' => $get('business'),
            'category' => $get('category'),
            'transaction_type' => $get('transaction_type'),
            'source' => $get('source'),
            'status' => $get('status'),
        ];
    }
}
