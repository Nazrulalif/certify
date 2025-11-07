<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Registration;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Yajra\DataTables\Facades\DataTables;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $certificates = Certificate::with(['event', 'registration', 'generator'])
                ->whereHas('event', function ($query) {
                    $query->where('created_by', auth()->id());
                })
                ->latest();

            return DataTables::of($certificates)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="' . $row->id . '" />
                            </div>';
                })
                ->addColumn('event', function ($row) {
                    return $row->event ? $row->event->name : '-';
                })
                ->addColumn('recipient', function ($row) {
                    $data = $row->data ?? [];
                    return $data['name'] ?? $data['participant_name'] ?? '-';
                })
                ->addColumn('generated_by', function ($row) {
                    return $row->generator ? $row->generator->name : '-';
                })
                ->addColumn('generated_at', function ($row) {
                    return $row->generated_at ? $row->generated_at->format('Y-m-d H:i') : '-';
                })
                ->addColumn('status', function ($row) {
                    if ($row->emailed_at) {
                        return '<span class="badge badge-success">Emailed</span>';
                    }
                    return '<span class="badge badge-primary">Generated</span>';
                })
                ->addColumn('actions', function ($row) {
                    return view('layouts.partials.action-button.certificates.index', compact('row'))->render();
                })
                ->rawColumns(['checkbox', 'status', 'actions'])
                ->make(true);
        }

        return view('pages.certificates.index');
    }

    public function create()
    {
        $events = Event::with('template.formFields')
            ->where('created_by', auth()->id())
            ->get();
        return view('pages.certificates.create', compact('events'));
    }

    public function getEventRegistrations(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            return response()->json([
                'error' => 'You are not authorized to view this event.',
            ], 403);
        }

        try {
            $registrations = $event->registrations()
                ->whereDoesntHave('certificate')
                ->get()
                ->map(function ($registration) {
                    $formData = $registration->form_data ?? [];
                    return [
                        'id' => $registration->id,
                        'name' => $formData['name'] ?? $formData['participant_name'] ?? 'N/A',
                        'registered_at' => $registration->registered_at->format('Y-m-d H:i'),
                    ];
                });

            return response()->json($registrations);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch registrations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateFromRegistrations(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id',
        ]);

        // Authorization check - verify all registrations belong to user's events
        $registrations = Registration::with('event')
            ->whereIn('id', $request->registration_ids)
            ->get();

        foreach ($registrations as $registration) {
            if ($registration->event->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to generate certificates for some registrations.'
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            $result = $this->certificateService->bulkGenerateFromRegistrations(
                $request->registration_ids,
                Auth::id()
            );

            DB::commit();

            $successCount = count($result['certificates']);
            $errorCount = count($result['errors']);

            $message = "{$successCount} certificate(s) generated successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} failed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'errors' => $result['errors'],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate certificates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateManual(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'data' => 'required|array',
        ]);

        try {
            $event = Event::findOrFail($request->event_id);

            // Authorization check
            if ($event->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to generate certificates for this event.'
                ], 403);
            }

            $certificate = $this->certificateService->generateFromManualData(
                $event,
                $request->data,
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Certificate generated successfully',
                'certificate' => $certificate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function regenerate(Certificate $certificate)
    {
        // Authorization check
        if ($certificate->event->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to regenerate this certificate.'
            ], 403);
        }

        try {
            $this->certificateService->regenerate($certificate);

            return response()->json([
                'success' => true,
                'message' => 'Certificate regenerated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Certificate $certificate)
    {
        // Authorization check
        if ($certificate->event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to view this certificate.');
        }

        $certificate->load(['event.template', 'registration', 'generator']);
        return view('pages.certificates.show', compact('certificate'));
    }

    public function destroy(Certificate $certificate)
    {
        // Authorization check
        if ($certificate->event->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this certificate.'
            ], 403);
        }

        try {
            $certificate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Certificate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No certificates selected'
                ], 400);
            }

            // Authorization check - verify all certificates belong to user's events
            $certificates = Certificate::with('event')
                ->whereIn('id', $ids)
                ->get();

            foreach ($certificates as $certificate) {
                if ($certificate->event->created_by !== auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to delete some certificates.'
                    ], 403);
                }
            }

            Certificate::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' certificate(s) deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete certificates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk download certificates as ZIP
     */
    public function bulkDownload(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No certificates selected'
                ], 400);
            }

            // Get certificates with authorization check
            $certificates = Certificate::with('event')
                ->whereIn('id', $ids)
                ->get();

            // Verify authorization for all certificates
            foreach ($certificates as $certificate) {
                if ($certificate->event->created_by !== auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to download some certificates.'
                    ], 403);
                }
            }

            if ($certificates->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No certificates found'
                ], 404);
            }

            // Create ZIP file
            $zipFileName = 'certificates-' . date('Y-m-d-His') . '.zip';
            $zipFilePath = storage_path('app/temp/' . $zipFileName);

            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create ZIP file'
                ], 500);
            }

            $addedCount = 0;
            foreach ($certificates as $certificate) {
                if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
                    $pdfPath = Storage::disk('public')->path($certificate->pdf_path);
                    $pdfName = $certificate->certificate_number . '.pdf';
                    
                    if ($zip->addFile($pdfPath, $pdfName)) {
                        $addedCount++;
                    }
                }
            }

            $zip->close();

            if ($addedCount === 0) {
                @unlink($zipFilePath);
                return response()->json([
                    'success' => false,
                    'message' => 'No certificate PDFs found'
                ], 404);
            }

            // Download and delete ZIP file
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download certificates: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(Certificate $certificate)
    {
        // Security: Only authenticated users can download certificates
        if (!Auth::check()) {
            abort(403, 'You must be logged in to download certificates. Please contact the certificate issuer if you need a copy.');
        }

        // Authorization check
        if ($certificate->event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to download this certificate.');
        }

        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            abort(404, 'Certificate PDF not found');
        }

        $path = Storage::disk('public')->path($certificate->pdf_path);
        return response()->download($path, $certificate->certificate_number . '.pdf');
    }

    public function preview(Certificate $certificate)
    {
        // Authorization check
        if ($certificate->event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to preview this certificate.');
        }

        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            abort(404, 'Certificate PDF not found');
        }

        return response()->file(Storage::disk('public')->path($certificate->pdf_path));
    }

    /**
     * Download Excel template for bulk import
     */
    public function downloadTemplate(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to download template for this event.'
            ], 403);
        }

        try {
            $template = $event->template;
            
            // Get form fields (show_in_form = true)
            $formFields = $template->formFields()
                ->orderBy('order')
                ->get();

            // Create new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Certificate Data');

            // Set headers
            $col = 1;
            foreach ($formFields as $field) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                
                // Set header value
                $sheet->setCellValue($columnLetter . '1', $field->field_label);
                
                // Style header
                $sheet->getStyle($columnLetter . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F46E5'],
                    ],
                ]);
                
                // Add data validation for select fields
                if ($field->field_type === 'select' && !empty($field->options)) {
                    $options = $field->options;
                    $optionsList = implode(',', $options);
                    
                    // Apply validation to column (rows 2-1000)
                    $validation = $sheet->getCell($columnLetter . '2')->getDataValidation();
                    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Invalid Input');
                    $validation->setError('Please select from dropdown');
                    $validation->setPromptTitle('Select Option');
                    $validation->setPrompt('Choose from: ' . $optionsList);
                    $validation->setFormula1('"' . $optionsList . '"');
                    
                    // Clone validation to other rows
                    for ($row = 3; $row <= 1000; $row++) {
                        $sheet->getCell($columnLetter . $row)->setDataValidation(clone $validation);
                    }
                }
                
                // Add comment with field info
                $comment = $field->field_label;
                if ($field->is_required) {
                    $comment .= ' (Required)';
                }
                $comment .= "\nType: " . ucfirst($field->field_type);
                
                $sheet->getComment($columnLetter . '1')
                    ->getText()
                    ->createTextRun($comment);
                
                // Auto-size column
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                
                $col++;
            }

            // Add sample data row
            $col = 1;
            foreach ($formFields as $field) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                
                // Add sample data based on field type
                $sampleData = match($field->field_type) {
                    'email' => 'example@email.com',
                    'date' => date('Y-m-d'),
                    'number' => '123',
                    'select' => !empty($field->options) ? $field->options[0] : 'Option 1',
                    default => 'Sample ' . $field->field_label,
                };
                
                $sheet->setCellValue($columnLetter . '2', $sampleData);
                $sheet->getStyle($columnLetter . '2')->getFont()->setItalic(true)->getColor()->setRGB('999999');
                
                $col++;
            }

            // Freeze header row
            $sheet->freezePane('A2');

            // Create writer and download
            $writer = new Xlsx($spreadsheet);
            $fileName = 'certificate-template-' . $event->slug . '-' . date('Y-m-d') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import and validate Excel file
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            $event = Event::with('template.formFields')->findOrFail($request->event_id);

            // Authorization check
            if ($event->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to import certificates for this event.'
                ], 403);
            }

            $file = $request->file('excel_file');

            // Load spreadsheet
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            if (empty($data) || count($data) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Excel file is empty or contains only headers'
                ], 400);
            }

            // Get headers from first row
            $headers = array_map('trim', $data[0]);
            
            // Get form fields
            $formFields = $event->template->formFields()->orderBy('order')->get();
            $expectedHeaders = $formFields->pluck('field_label')->toArray();

            // Validate headers
            $missingHeaders = array_diff($expectedHeaders, $headers);
            if (!empty($missingHeaders)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required columns: ' . implode(', ', $missingHeaders)
                ], 400);
            }

            // Map headers to field names
            $headerMap = [];
            foreach ($headers as $index => $header) {
                $field = $formFields->firstWhere('field_label', $header);
                if ($field) {
                    $headerMap[$index] = $field->field_name;
                }
            }

            // Parse data rows
            $parsedData = [];
            $errors = [];
            $rowNumber = 1; // Start from 1 (excluding header)

            for ($i = 1; $i < count($data); $i++) {
                $row = $data[$i];
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $rowData = [];
                $rowErrors = [];

                foreach ($row as $colIndex => $value) {
                    if (!isset($headerMap[$colIndex])) {
                        continue;
                    }

                    $fieldName = $headerMap[$colIndex];
                    $field = $formFields->firstWhere('field_name', $fieldName);

                    if (!$field) {
                        continue;
                    }

                    // Trim value
                    $value = is_string($value) ? trim($value) : $value;

                    // Validate required fields
                    if ($field->is_required && empty($value)) {
                        $rowErrors[] = "{$field->field_label} is required";
                        continue;
                    }

                    // Validate field type
                    switch ($field->field_type) {
                        case 'email':
                            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $rowErrors[] = "{$field->field_label} must be a valid email";
                            }
                            break;
                        case 'number':
                            if (!empty($value) && !is_numeric($value)) {
                                $rowErrors[] = "{$field->field_label} must be a number";
                            }
                            break;
                        case 'date':
                            if (!empty($value)) {
                                try {
                                    $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                                    $value = $dateValue->format('Y-m-d');
                                } catch (\Exception $e) {
                                    // Try parsing as string
                                    $timestamp = strtotime($value);
                                    if ($timestamp === false) {
                                        $rowErrors[] = "{$field->field_label} must be a valid date";
                                    } else {
                                        $value = date('Y-m-d', $timestamp);
                                    }
                                }
                            }
                            break;
                        case 'select':
                            if (!empty($value) && !empty($field->options) && !in_array($value, $field->options)) {
                                $rowErrors[] = "{$field->field_label} must be one of: " . implode(', ', $field->options);
                            }
                            break;
                    }

                    $rowData[$fieldName] = $value;
                }

                if (!empty($rowErrors)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => $rowErrors,
                        'data' => $rowData,
                    ];
                } else {
                    $parsedData[] = [
                        'row' => $rowNumber,
                        'data' => $rowData,
                    ];
                }
            }

            // Return validation results
            return response()->json([
                'success' => true,
                'message' => 'Excel file validated successfully',
                'data' => $parsedData,
                'errors' => $errors,
                'total_rows' => count($parsedData) + count($errors),
                'valid_rows' => count($parsedData),
                'invalid_rows' => count($errors),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process Excel file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate certificates from Excel data
     */
    public function generateFromExcel(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'data' => 'required|array',
            'data.*.data' => 'required|array',
        ]);

        try {
            $event = Event::findOrFail($request->event_id);

            // Authorization check
            if ($event->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to generate certificates for this event.'
                ], 403);
            }

            DB::beginTransaction();
            $rows = $request->data;
            $certificates = [];
            $errors = [];

            foreach ($rows as $index => $row) {
                try {
                    $certificate = $this->certificateService->generateFromManualData(
                        $event,
                        $row['data'],
                        Auth::id()
                    );
                    
                    $certificates[] = [
                        'row' => $row['row'] ?? ($index + 1),
                        'certificate' => $certificate,
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $row['row'] ?? ($index + 1),
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($certificates) . ' certificate(s) generated successfully',
                'certificates' => $certificates,
                'errors' => $errors,
                'total' => count($rows),
                'success_count' => count($certificates),
                'error_count' => count($errors),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate certificates: ' . $e->getMessage()
            ], 500);
        }
    }
}
