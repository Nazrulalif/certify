<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    use SoftDeletes, HasUuids, HasFactory;

    protected $fillable = [
        'event_id',
        'registration_id',
        'certificate_number',
        'data',
        'qr_code',
        'pdf_path',
        'generated_at',
        'emailed_at',
        'generated_by',
    ];

    protected $casts = [
        'data' => 'array',
        'generated_at' => 'datetime',
        'emailed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getFieldValue(string $fieldName): ?string
    {
        return $this->data[$fieldName] ?? null;
    }

    public function getPdfUrl(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }
        return asset('storage/' . $this->pdf_path);
    }

    public function getQrCodeUrl(): ?string
    {
        if (!$this->qr_code) {
            return null;
        }
        return asset('storage/' . $this->qr_code);
    }

    public function getVerificationUrl(): string
    {
        return route('verify.certificate', $this->certificate_number);
    }

    public static function generateCertificateNumber(): string
    {
        $year = date('Y');

        // Get the last certificate number for this year (including soft deleted)
        // withTrashed() ensures we check ALL certificates to avoid duplicates
        $lastCertificate = self::withTrashed()
            ->whereYear('created_at', $year)
            ->where('certificate_number', 'like', "CERT-{$year}-%")
            ->orderByRaw('CAST(SUBSTRING(certificate_number, 11) AS UNSIGNED) DESC')
            ->first();

        if ($lastCertificate) {
            // Extract the number part from CERT-YYYY-NNNNNN
            preg_match('/CERT-\d{4}-(\d{6})/', $lastCertificate->certificate_number, $matches);
            $number = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        } else {
            $number = 1;
        }

        // Keep trying until we find a unique number (handles race conditions)
        $attempts = 0;
        $maxAttempts = 100;

        do {
            $certificateNumber = sprintf('CERT-%s-%06d', $year, $number);

            // Check if this number exists (including soft deleted)
            $exists = self::withTrashed()
                ->where('certificate_number', $certificateNumber)
                ->exists();

            if (!$exists) {
                return $certificateNumber;
            }

            $number++;
            $attempts++;
        } while ($attempts < $maxAttempts);

        // Fallback: use timestamp-based unique number
        return sprintf('CERT-%s-%06d', $year, time() % 1000000);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
        });

        static::deleting(function ($certificate) {
            // Delete associated files when certificate is deleted (from public disk)
            if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
                Storage::disk('public')->delete($certificate->pdf_path);
            }
            if ($certificate->qr_code && Storage::disk('public')->exists($certificate->qr_code)) {
                Storage::disk('public')->delete($certificate->qr_code);
            }
        });
    }
}
