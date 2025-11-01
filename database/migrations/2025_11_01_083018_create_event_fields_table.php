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
        Schema::create('event_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->onDelete('cascade');
            $table->string('field_name'); // internal name (e.g., participant_name)
            $table->string('field_label'); // display label (e.g., "Participant Name")
            $table->string('field_type')->default('text'); // text, email, date, number, select, textarea
            $table->boolean('required')->default(false);
            $table->json('options')->nullable(); // for select/radio/checkbox fields
            $table->integer('order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_fields');
    }
};
