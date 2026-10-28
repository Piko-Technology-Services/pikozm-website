<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DPO Pay orders (replaces payments/data/orders.json).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('company_ref')->unique();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('ZMW');
            $table->string('service_type', 20)->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 40);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('dpo_token')->nullable()->index();
            $table->string('dpo_trans_ref')->nullable();
            $table->text('error_message')->nullable();
            $table->string('verify_message')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
