# CSV Transaction Importer

A Laravel 13 application that imports transactions from CSV files, stores them in MySQL, and displays them in a filterable, paginated table styled with Tailwind CSS.

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+ & npm
- MySQL 8.0+

## Setup

```bash
# Clone the repository
git clone https://github.com/<your-username>/laravel13-csv-importer.git
cd laravel13-csv-importer

# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel13_csv_importer_db
DB_USERNAME=root
DB_PASSWORD=
```

Then create the database and run migrations:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS laravel13_csv_importer_db"
php artisan migrate
```

Build frontend assets and start the server:

```bash
npm run build
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000).

## Usage

1. **Upload** -- Navigate to the home page and upload a CSV file with the expected columns. Drag and drop is supported.
2. **View** -- After a successful import, you are redirected to the transactions table.
3. **Filter** -- Use the dropdowns and search bar to filter by business, category, type, source, status, or description/business text.
4. **Per page** -- Change the rows-per-page dropdown (10 / 25 / 50 / 100) and the table updates immediately.
5. **Delete import** -- On the upload page, the Import History section lists all previous imports. Click Delete to remove an import and all its transactions, then re-upload a corrected file.

### Expected CSV Format

```
Date,Description,Amount,Business,Category,Transaction_Type,Source,Status
2026-01-02,MONTHLY SERVICE FEE,-15.00,Bright Fitness,Bank Fees,Expense,Chase,Reviewed
```

Column headers are case-insensitive and whitespace-tolerant (e.g. `transaction type`, `Transaction_Type`, and `TRANSACTION_TYPE` all work).

## Architecture

```
app/
├── Exceptions/
│   ├── DuplicateImportException.php    # Thrown when the same file is uploaded twice
│   └── InvalidCsvFormatException.php   # Thrown for empty files or missing columns
├── Http/
│   ├── Controllers/
│   │   ├── CsvImportController.php     # Handles upload form, store, and delete import
│   │   └── TransactionController.php   # Thin controller for the filterable transaction list
│   └── Requests/
│       └── CsvUploadRequest.php        # Validates file type (csv only) and max size (2MB)
├── Models/
│   ├── CsvImport.php                   # Tracks each uploaded file (hash-based dedup)
│   └── Transaction.php                 # Parsed CSV row; carries scopeSearch, scopeApplyFilters
└── Services/
    └── CsvImportService.php            # Core import logic: hash, normalize, parse, bulk insert
```

### Design Decisions

- **Service Layer** -- `CsvImportService` encapsulates all import logic (hashing, header normalization, CSV parsing, bulk DB writes) keeping both controllers thin.
- **Separate Controllers** -- `CsvImportController` owns the import lifecycle (upload, delete). `TransactionController` owns only the transaction listing. Each controller has a single responsibility.
- **league/csv** -- Lightweight, stream-based CSV parser. Chosen over `maatwebsite/excel` since we only handle CSV and don't need PhpSpreadsheet overhead.
- **Duplicate Detection** -- SHA-256 hash of file contents stored in `csv_imports.file_hash` (unique constraint). Re-uploading the same file is rejected before any rows are parsed.
- **Header Normalization** -- Headers are trimmed, lowercased, and space-to-underscore converted before validation, so `Transaction_Type`, `transaction type`, and `TRANSACTION_TYPE` all import correctly.
- **Bulk Insert** -- All parsed rows are collected and inserted in chunks of 500 via `Transaction::insert()` -- one SQL query per chunk rather than N individual queries.
- **Database Transaction** -- The entire import (CsvImport record + all Transaction rows) is wrapped in `DB::transaction()` for atomicity. A failure mid-import rolls back everything.
- **Blank Row Filtering** -- Fully blank rows (e.g. trailing newlines) are silently skipped. Partial rows with some blank fields are imported as-is.
- **Model Scopes** -- Filter and search logic lives on the `Transaction` model (`scopeSearch`, `scopeApplyFilters`, `filterOptions`) rather than in the controller, keeping queries reusable and the controller lean.
- **Delete Import** -- Each import batch is tracked via `csv_import_id` with `cascadeOnDelete`. Deleting a `CsvImport` record automatically removes all its transactions, allowing users to correct and re-upload a file.
- **File Size Limit** -- Uploads are capped at 2MB. A typical transaction CSV with 10,000 rows is well under 1MB, so this covers real-world usage while preventing accidental large file submissions. Adjust `max:2048` in `CsvUploadRequest` if needed.
- **Multiple Imports** -- Multiple different CSV files can be imported and their transactions accumulate. Exact duplicate files (same SHA-256 hash) are rejected.

### Error Handling

| Scenario | Handled By | Result |
|---|---|---|
| Wrong file type (non-CSV) | `CsvUploadRequest` | Validation error |
| File exceeds 2MB | `CsvUploadRequest` | Validation error |
| Empty file / no data rows | `CsvImportService` | `InvalidCsvFormatException` |
| Missing or unrecognised columns | `CsvImportService` | `InvalidCsvFormatException` |
| Duplicate file upload | `CsvImportService` | `DuplicateImportException` |

## Running Tests

```bash
php artisan test
```

Tests use in-memory SQLite (configured in `phpunit.xml`) so no additional database setup is needed.

- **Unit tests** (`tests/Unit/CsvImportServiceTest.php`) -- 10 tests covering import logic, field mapping, duplicate detection, empty files, bad headers, case-insensitive headers, blank row skipping, and row count integrity.
- **Feature tests** (`tests/Feature/TransactionControllerTest.php`) -- 12 tests covering HTTP endpoints, form validation, duplicate rejection, filtering, search, delete import, and re-upload after deletion.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3
- **Frontend**: Blade, Tailwind CSS v4, Vite 8
- **Database**: MySQL 8
- **CSV Parsing**: [league/csv](https://csv.thephpleague.com/) v9
- **Testing**: PHPUnit 12

---

Built by **Robin Correa**
