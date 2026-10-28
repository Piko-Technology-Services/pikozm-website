<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leads that are still handed off to WhatsApp, but are now also stored so
 * nothing is lost if the visitor never presses "send" in WhatsApp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->json('services')->nullable();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('talent_applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone', 40);
            $table->string('email')->nullable();
            $table->string('skill');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('promo_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->string('tagline');
            $table->json('features');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('promo_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_package_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone', 40);
            $table->string('email')->nullable();
            $table->string('business_name')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_registrations');
        Schema::dropIfExists('promo_packages');
        Schema::dropIfExists('talent_applications');
        Schema::dropIfExists('contact_messages');
    }
};
