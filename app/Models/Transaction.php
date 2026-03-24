<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'csv_import_id',
        'date',
        'description',
        'amount',
        'business',
        'category',
        'transaction_type',
        'source',
        'status',
    ];

    private const array FILTERABLE = [
        'business',
        'category',
        'transaction_type',
        'source',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function csvImport(): BelongsTo
    {
        return $this->belongsTo(CsvImport::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $query->when($term !== '', function ($q) use ($term) {
            $q->where(function ($sub) use ($term) {
                $sub->where('description', 'like', "%{$term}%")
                    ->orWhere('business', 'like', "%{$term}%");
            });
        });
    }

    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        foreach (self::FILTERABLE as $field) {
            $query->when($filters[$field] ?? null, fn ($q, $value) => $q->where($field, $value));
        }

        return $query;
    }

    public static function filterOptions(): array
    {
        return collect(self::FILTERABLE)->mapWithKeys(fn ($field) => [
            $field => static::distinct()->orderBy($field)->pluck($field),
        ])->all();
    }
}
