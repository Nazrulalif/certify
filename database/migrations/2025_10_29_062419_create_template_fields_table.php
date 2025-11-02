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
            $table->string('field_type')->default('text'); // text, date, number
            $table->decimal('x', 8, 2)->default(0); // X position
            $table->decimal('y', 8, 2)->default(0); // Y position
            $table->decimal('width', 8, 2)->nullable(); // Width
            $table->decimal('height', 8, 2)->nullable(); // Height
            $table->integer('font_size')->default(16);
            $table->string('font_family')->default('Arial');
            $table->string('color')->default('#000000'); // Hex color
            $table->string('text_align')->default('left'); // left, center, right
            $table->boolean('bold')->default(false);
            $table->boolean('italic')->default(false);
            $table->decimal('rotation', 5, 2)->default(0); // Rotation angle
            $table->softDeletes();
            $table->timestamps();
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
