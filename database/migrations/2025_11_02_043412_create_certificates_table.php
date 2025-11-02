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
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignUuid('registration_id')->nullable()->constrained('registrations')->onDelete('set null');
            $table->string('certificate_number');
            $table->json('data'); // stores certificate field data (name, date, etc.)
            $table->string('qr_code')->nullable(); // path to QR code image
            $table->string('pdf_path')->nullable(); // path to generated PDF
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('emailed_at')->nullable();
            $table->foreignUuid('generated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
