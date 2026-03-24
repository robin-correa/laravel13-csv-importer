<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csv_import_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('business');
            $table->string('category');
            $table->string('transaction_type');
            $table->string('source');
            $table->string('status');
            $table->timestamps();

            $table->index('business');
            $table->index('category');
            $table->index('transaction_type');
            $table->index('source');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
