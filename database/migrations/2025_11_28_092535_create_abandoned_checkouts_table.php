<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('abandoned_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('shopify_checkout_id')->unique();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email');
            $table->decimal('total_price', 10, 2);
            $table->json('line_items')->nullable();
            $table->timestamp('abandoned_at');
            $table->string('recovery_status')->default('pending'); // pending, sent, recovered
            $table->integer('recovery_attempts')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'recovery_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_checkouts');
    }
};
