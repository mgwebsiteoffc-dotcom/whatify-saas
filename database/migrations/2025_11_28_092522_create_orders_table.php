<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('shopify_order_id')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->string('financial_status')->default('pending');
            $table->string('payment_method')->nullable(); // COD, prepaid, etc.
            $table->string('cod_status')->default('pending'); // pending, confirmed, cancelled
            $table->json('line_items')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'cod_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
