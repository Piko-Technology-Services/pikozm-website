<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ZamPayroll subscription payments (card via DPO, in-page mobile money via DPO, or manual).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zampayroll_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('plan', 40);
            $table->string('plan_name');
            $table->string('billing_cycle', 10); // monthly | annual
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('ZMW');

            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->unsignedInteger('employees')->nullable();

            $table->string('payment_method', 20); // card | mobile_money | manual
            $table->string('status', 20)->default('pending')->index();
            $table->string('dpo_token')->nullable()->index();
            $table->string('dpo_trans_ref')->nullable();
            $table->string('mobile_network', 20)->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->string('last_result_code', 10)->nullable();
            $table->string('last_message')->nullable();
            $table->string('notified_key')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zampayroll_subscriptions');
    }
};
