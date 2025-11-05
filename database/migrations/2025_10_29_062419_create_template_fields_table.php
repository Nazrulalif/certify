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
        Schema::create('template_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('templates')->onDelete('cascade');
            $table->string('field_name'); // e.g., 'name', 'event_name', 'date'
            $table->string('field_label')->nullable(); // Display label for forms
            $table->string('field_type')->default('text'); // text, email, date, number, textarea
            $table->boolean('show_in_form')->default(true); // Show in registration form
            $table->boolean('show_in_cert')->default(true); // Show on certificate
            $table->boolean('is_required')->default(false); // Required in registration form
            $table->boolean('is_predefined')->default(false); // Cannot be deleted
            $table->json('position_data')->nullable(); // All positioning/styling data (x, y, fontSize, etc.)
            $table->integer('order')->default(0); // Display order in forms
            // $table->softDeletes();
            $table->timestamps();
            
            // Ensure unique field names per template
            $table->unique(['template_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_fields');
    }
};
