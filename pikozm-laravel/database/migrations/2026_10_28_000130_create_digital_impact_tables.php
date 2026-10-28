<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Piko Digital Impact Programme (PDIP) applications.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('country');
            $table->string('skills');
            $table->string('availability', 20);
            $table->text('message')->nullable();
            $table->string('id_document_path');
            $table->string('resume_path');
            $table->string('status', 20)->default('new')->index();
            $table->timestamps();
        });

        Schema::create('partnership_requests', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('country');
            $table->string('partnership_type', 30);
            $table->string('organization_type', 30);
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->timestamps();
        });

        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('country');
            $table->string('organization_type', 30);
            $table->text('support_needs');
            $table->string('website')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
        Schema::dropIfExists('partnership_requests');
        Schema::dropIfExists('volunteer_applications');
    }
};
