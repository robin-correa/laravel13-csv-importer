<?php

namespace Tests\Feature;

use App\Models\CsvImport;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validCsv(): UploadedFile
    {
        $path = base_path('tests/fixtures/valid.csv');

        return new UploadedFile($path, 'valid.csv', 'text/csv', null, true);
    }

    public function test_upload_form_renders(): void
    {
        $response = $this->get(route('upload.create'));

        $response->assertStatus(200);
        $response->assertSee('Import Transactions');
        $response->assertSee('csv_file');
    }

    public function test_successful_csv_upload_redirects_with_success(): void
    {
        $response = $this->post(route('upload.store'), [
            'csv_file' => $this->validCsv(),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        $this->assertSame(1, CsvImport::count());
        $this->assertSame(5, Transaction::count());
    }

    public function test_rejects_non_csv_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->post(route('upload.store'), [
            'csv_file' => $file,
        ]);

        $response->assertSessionHasErrors('csv_file');
        $this->assertSame(0, CsvImport::count());
    }

    public function test_rejects_missing_file(): void
    {
        $response = $this->post(route('upload.store'), []);

        $response->assertSessionHasErrors('csv_file');
    }

    public function test_rejects_duplicate_upload(): void
    {
        $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $response = $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $response->assertSessionHasErrors('csv_file');
        $this->assertSame(1, CsvImport::count());
    }

    public function test_transactions_index_renders(): void
    {
        $response = $this->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Transactions');
    }

    public function test_transactions_index_shows_imported_data(): void
    {
        $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $response = $this->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('MONTHLY SERVICE FEE');
        $response->assertSee('STRIPE TRANSFER');
    }

    public function test_filter_by_business(): void
    {
        $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $response = $this->get(route('transactions.index', ['business' => 'Rentals']));

        $response->assertStatus(200);
        $response->assertSee('DEPOSIT - ACH PAYMENT');
        $response->assertDontSee('MONTHLY SERVICE FEE');
    }

    public function test_search_by_description(): void
    {
        $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $response = $this->get(route('transactions.index', ['search' => 'STRIPE']));

        $response->assertStatus(200);
        $response->assertSee('STRIPE TRANSFER');
        $response->assertDontSee('MONTHLY SERVICE FEE');
    }

    public function test_delete_import_removes_transactions(): void
    {
        $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $import = CsvImport::first();
        $this->assertSame(5, Transaction::count());

        $response = $this->delete(route('imports.destroy', $import));

        $response->assertRedirect(route('upload.create'));
        $response->assertSessionHas('success');
        $this->assertSame(0, CsvImport::count());
        $this->assertSame(0, Transaction::count());
    }

    public function test_can_reupload_after_deleting_import(): void
    {
        $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);
        $this->delete(route('imports.destroy', CsvImport::first()));

        $response = $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertSame(1, CsvImport::count());
        $this->assertSame(5, Transaction::count());
    }

    public function test_filter_by_status(): void
    {
        $this->post(route('upload.store'), ['csv_file' => $this->validCsv()]);

        $response = $this->get(route('transactions.index', ['status' => 'Pending']));

        $response->assertStatus(200);
        $response->assertSee('STRIPE TRANSFER');
        $response->assertSee('ATM WITHDRAWAL');
        $response->assertDontSee('MONTHLY SERVICE FEE');
    }
}
