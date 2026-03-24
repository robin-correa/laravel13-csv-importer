<?php

namespace Tests\Unit;

use App\Exceptions\DuplicateImportException;
use App\Exceptions\InvalidCsvFormatException;
use App\Models\CsvImport;
use App\Models\Transaction;
use App\Services\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private CsvImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CsvImportService;
    }

    private function fixtureFile(string $filename): UploadedFile
    {
        $path = base_path("tests/fixtures/{$filename}");

        return new UploadedFile($path, $filename, 'text/csv', null, true);
    }

    public function test_imports_valid_csv_and_creates_records(): void
    {
        $import = $this->service->import($this->fixtureFile('valid.csv'));

        $this->assertInstanceOf(CsvImport::class, $import);
        $this->assertSame(5, $import->row_count);
        $this->assertSame('valid.csv', $import->original_filename);
        $this->assertNotEmpty($import->file_hash);
        $this->assertSame(5, Transaction::count());
    }

    public function test_maps_csv_fields_correctly(): void
    {
        $this->service->import($this->fixtureFile('valid.csv'));

        $txn = Transaction::where('description', 'MONTHLY SERVICE FEE')->first();

        $this->assertNotNull($txn);
        $this->assertEquals('2026-01-02', $txn->date->format('Y-m-d'));
        $this->assertEquals(-15.00, (float) $txn->amount);
        $this->assertSame('Bright Fitness', $txn->business);
        $this->assertSame('Bank Fees', $txn->category);
        $this->assertSame('Expense', $txn->transaction_type);
        $this->assertSame('Chase', $txn->source);
        $this->assertSame('Reviewed', $txn->status);
    }

    public function test_income_amount_is_positive(): void
    {
        $this->service->import($this->fixtureFile('valid.csv'));

        $txn = Transaction::where('description', 'DEPOSIT - ACH PAYMENT')->first();

        $this->assertEquals(3200.00, (float) $txn->amount);
    }

    public function test_throws_on_duplicate_file(): void
    {
        $this->service->import($this->fixtureFile('valid.csv'));

        $this->expectException(DuplicateImportException::class);
        $this->expectExceptionMessage('This file has already been imported.');

        $this->service->import($this->fixtureFile('valid.csv'));
    }

    public function test_throws_on_empty_csv(): void
    {
        $this->expectException(InvalidCsvFormatException::class);
        $this->expectExceptionMessage('The CSV file contains no data rows.');

        $this->service->import($this->fixtureFile('empty.csv'));
    }

    public function test_throws_on_bad_headers(): void
    {
        $this->expectException(InvalidCsvFormatException::class);
        $this->expectExceptionMessage('CSV is missing required columns');

        $this->service->import($this->fixtureFile('bad_headers.csv'));
    }

    public function test_accepts_lowercase_headers(): void
    {
        $path = base_path('tests/fixtures/lowercase_headers.csv');
        file_put_contents($path, implode("\n", [
            'date,description,amount,business,category,transaction_type,source,status',
            '2026-01-02,TEST ENTRY,-10.00,Acme,Bank Fees,Expense,Chase,Reviewed',
        ]));

        $file = new UploadedFile($path, 'lowercase_headers.csv', 'text/csv', null, true);
        $import = $this->service->import($file);

        $this->assertSame(1, $import->row_count);
        unlink($path);
    }

    public function test_skips_fully_blank_rows(): void
    {
        $path = base_path('tests/fixtures/blank_rows.csv');
        file_put_contents($path, implode("\n", [
            'Date,Description,Amount,Business,Category,Transaction_Type,Source,Status',
            '2026-01-02,MONTHLY FEE,-15.00,Acme,Bank Fees,Expense,Chase,Reviewed',
            ',,,,,,,,',
            '2026-01-03,STRIPE,-100.00,Acme,Revenue,Income,Stripe,Reviewed',
            '',
        ]));

        $file = new UploadedFile($path, 'blank_rows.csv', 'text/csv', null, true);
        $import = $this->service->import($file);

        $this->assertSame(2, $import->row_count);
        $this->assertSame(2, Transaction::count());
        unlink($path);
    }

    public function test_row_count_matches_actual_transactions(): void
    {
        $import = $this->service->import($this->fixtureFile('valid.csv'));

        $this->assertSame($import->row_count, $import->transactions()->count());
    }

    public function test_csv_import_has_many_transactions(): void
    {
        $import = $this->service->import($this->fixtureFile('valid.csv'));

        $this->assertCount(5, $import->transactions);
        $this->assertContainsOnlyInstancesOf(Transaction::class, $import->transactions);
    }
}
