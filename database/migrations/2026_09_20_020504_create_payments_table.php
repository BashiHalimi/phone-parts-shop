<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('payable_type');           // e.g. App\Models\Sale
            $table->unsignedBigInteger('payable_id'); // e.g. sale id

            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank', 'other'])->default('cash');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};