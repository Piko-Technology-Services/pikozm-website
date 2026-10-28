<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments catalog (replaces payments/includes/products.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('note')->nullable();
            $table->decimal('price', 12, 2)->nullable(); // null when pricing_type = custom
            $table->string('service_type', 20);           // DPO service type code
            $table->string('pricing_type', 10)->default('fixed'); // fixed | custom
            $table->string('category', 20);              // subscription | one-time
            $table->string('billing_cycle', 20)->nullable(); // month | year
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
