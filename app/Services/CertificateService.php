<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Template;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateService
{
    /**
     * Generate certificate from registration
     */
    public function generateFromRegistration(Registration $registration, $userId): Certificate
    {
        $event = $registration->event;
        $template = $event->template;

        // Prepare certificate data from registration
        $certificateData = $registration->data;

        return $this->generateCertificate($event, $template, $certificateData, $userId, $registration->id);
    }

    /**
     * Generate certificate from manual data
     */
    public function generateFromManualData(Event $event, array $data, $userId): Certificate
    {
        $template = $event->template;
        return $this->generateCertificate($event, $template, $data, $userId);
    }

    /**
     * Core certificate generation logic
     */
    protected function generateCertificate(Event $event, Template $template, array $data, $userId, $registrationId = null): Certificate
    {
        // Create certificate record
        $certificate = Certificate::create([
            'event_id' => $event->id,
            'registration_id' => $registrationId,
            'data' => $data,
            'generated_by' => $userId,
        ]);

        // Generate QR code
        $qrCodePath = $this->generateQrCode($certificate);

        // Generate PDF
        $pdfPath = $this->generatePdf($certificate, $template, $data, $qrCodePath);

        // Update certificate with file paths
        $certificate->update([
            'qr_code' => $qrCodePath,
            'pdf_path' => $pdfPath,
        ]);

        return $certificate;
    }

    /**
     * Generate QR code for certificate verification
     */
    protected function generateQrCode(Certificate $certificate): string
    {
        $verificationUrl = $certificate->getVerificationUrl();

        // Generate QR code as SVG (no ImageMagick/GD required)
        $qrCode = QrCode::size(200)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($verificationUrl);

        // Save QR code as SVG in public disk
        $directory = 'certificates/qrcodes/' . date('Y/m');
        $filename = $certificate->certificate_number . '.svg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $qrCode);

        return $path;
    }

    /**
     * Generate PDF certificate
     */
    protected function generatePdf(Certificate $certificate, Template $template, array $data, string $qrCodePath): string
    {
        // Get template background - use absolute path
        $backgroundPath = Storage::disk('public')->path($template->background);

        // Get QR code path - use absolute path
        $qrCodeFullPath = Storage::disk('public')->path($qrCodePath);

        // Get original image dimensions
        $imageSize = @getimagesize($backgroundPath);
        $originalWidth = $imageSize ? $imageSize[0] : 1122; // Default A4 landscape at 96 DPI
        $originalHeight = $imageSize ? $imageSize[1] : 794;

        // A4 landscape dimensions in pixels at 96 DPI (DomPDF default)
        // 297mm = 1122px, 210mm = 794px at 96 DPI
        $pdfWidth = 1122;
        $pdfHeight = 794;

        // Calculate scaling factors for each axis independently
        // This stretches the content to fill the PDF page exactly as the template shows
        $scaleX = $pdfWidth / $originalWidth;
        $scaleY = $pdfHeight / $originalHeight;

        // Prepare field data for PDF with scaled positions
        $fields = $template->fields->map(function ($field) use ($data, $scaleX, $scaleY) {
            return [
                'field_name' => $field->field_name,
                'value' => $data[$field->field_name] ?? '',
                'x' => $field->x * $scaleX,
                'y' => $field->y * $scaleY,
                'width' => $field->width * $scaleX,
                'height' => $field->height * $scaleY,
                'font_size' => $field->font_size * min($scaleX, $scaleY), // Use min for font to prevent distortion
                'font_family' => $field->font_family,
                'color' => $field->color,
                'alignment' => $field->alignment,
                'bold' => $field->bold,
                'italic' => $field->italic,
            ];
        });

        // Convert background to base64 for embedding
        $backgroundBase64 = null;
        $backgroundExists = false;
        if (file_exists($backgroundPath)) {
            $imageData = base64_encode(file_get_contents($backgroundPath));
            $imageType = pathinfo($backgroundPath, PATHINFO_EXTENSION);
            $backgroundBase64 = "data:image/{$imageType};base64,{$imageData}";
            $backgroundExists = true;
        }

        // Generate PDF using view
        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'template' => $template,
            'backgroundPath' => $backgroundBase64,
            'backgroundExists' => $backgroundExists,
            'qrCodePath' => $qrCodeFullPath,
            'fields' => $fields,
            'data' => $data,
        ]);

        // Set paper size to match template dimensions (A4 landscape by default)
        $pdf->setPaper('a4', 'landscape');

        // Save PDF in public disk
        $directory = 'certificates/pdfs/' . date('Y/m');
        $filename = $certificate->certificate_number . '.pdf';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Bulk generate certificates from registrations
     */
    public function bulkGenerateFromRegistrations(array $registrationIds, $userId): array
    {
        $certificates = [];
        $errors = [];

        foreach ($registrationIds as $registrationId) {
            try {
                $registration = Registration::findOrFail($registrationId);

                // Check if certificate already exists for this registration
                $existingCertificate = Certificate::where('registration_id', $registrationId)->first();
                if ($existingCertificate) {
                    $errors[] = "Certificate already exists for registration {$registration->id}";
                    continue;
                }

                $certificate = $this->generateFromRegistration($registration, $userId);
                $certificates[] = $certificate;
            } catch (\Exception $e) {
                $errors[] = "Failed to generate certificate for registration {$registrationId}: " . $e->getMessage();
            }
        }

        return [
            'certificates' => $certificates,
            'errors' => $errors,
        ];
    }

    /**
     * Regenerate certificate (useful for template updates)
     */
    public function regenerate(Certificate $certificate): Certificate
    {
        $event = $certificate->event;
        $template = $event->template;
        $data = $certificate->data;

        // Delete old files from public disk
        if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }
        if ($certificate->qr_code && Storage::disk('public')->exists($certificate->qr_code)) {
            Storage::disk('public')->delete($certificate->qr_code);
        }

        // Regenerate QR code
        $qrCodePath = $this->generateQrCode($certificate);

        // Regenerate PDF
        $pdfPath = $this->generatePdf($certificate, $template, $data, $qrCodePath);

        // Update certificate
        $certificate->update([
            'qr_code' => $qrCodePath,
            'pdf_path' => $pdfPath,
        ]);

        return $certificate->fresh();
    }
}
