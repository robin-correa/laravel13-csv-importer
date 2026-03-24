<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsvImport extends Model
{
    protected $fillable = [
        'original_filename',
        'file_hash',
        'row_count',
    ];

    protected function casts(): array
    {
        return [
            'row_count' => 'integer',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
