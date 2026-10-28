<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lenco-backed payments that used to go through app.pikozm.com:
 *  - client_payments: PikoPay "Make a Payment" (payments.html)
 *  - donations:       Digital Impact "Support the Cause"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('purpose');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('status', 20)->default('pending')->index();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('focus_area')->default('general');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
        Schema::dropIfExists('client_payments');
    }
};
