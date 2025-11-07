<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateField;
use App\Services\TemplateFieldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index()
    {
        $templates = Template::with('creator')
            ->where('created_by', auth()->id())
            ->withCount('fields')
            ->latest()
            ->paginate(6);

        return view('pages.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        return view('pages.templates.create');
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'background' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'is_default' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Upload background image
            $backgroundPath = null;
            if ($request->hasFile('background')) {
                $backgroundPath = $request->file('background')->store('templates', 'public');
            }

            // Create template
            $template = Template::create([
                'name' => $request->name,
                'background' => $backgroundPath,
                'is_default' => $request->boolean('is_default'),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // If set as default, unset other defaults
            if ($template->is_default) {
                Template::where('id', '!=', $template->id)->update(['is_default' => false]);
            }

            DB::commit();

            return redirect()
                ->route('templates.edit', $template->id)
                ->with('success', 'Template created successfully! Now add fields to your template.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create template: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to edit this template.');
        }

        $template->load('fields');
        return view('pages.templates.edit', compact('template'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to update this template.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'background' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'is_default' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update background if new file uploaded
            if ($request->hasFile('background')) {
                // Delete old background
                if ($template->background) {
                    Storage::disk('public')->delete($template->background);
                }
                $template->background = $request->file('background')->store('templates', 'public');
            }

            $template->name = $request->name;
            $template->is_default = $request->boolean('is_default');
            $template->save();

            // If set as default, unset other defaults
            if ($template->is_default) {
                Template::where('id', '!=', $template->id)->update(['is_default' => false]);
            }

            DB::commit();

            return back()->with('success', 'Template updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update template: ' . $e->getMessage());
        }
    }

    /**
     * Save template fields from Fabric.js canvas.
     */
    public function saveFields(Request $request, Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify this template.',
            ], 403);
        }

        $request->validate([
            'fields' => 'required|array',
            'fields.*.field_name' => 'required|string',
            'fields.*.field_type' => 'required|string',
            'fields.*.x' => 'required|numeric',
            'fields.*.y' => 'required|numeric',
            'fields.*.width' => 'nullable|numeric',
            'fields.*.height' => 'nullable|numeric',
            'fields.*.font_size' => 'required|integer',
            'fields.*.font_family' => 'required|string',
            'fields.*.color' => 'required|string',
            'fields.*.text_align' => 'required|string',
            'fields.*.bold' => 'boolean',
            'fields.*.italic' => 'boolean',
            'fields.*.rotation' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            // Delete existing fields
            $template->fields()->delete();

            // Create new fields
            foreach ($request->fields as $fieldData) {
                $template->fields()->create($fieldData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template fields saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save fields: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a custom field to template
     */
    public function addField(Request $request, Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify this template.',
            ], 403);
        }

        $request->validate([
            'field_name' => 'required|string|max:255',
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|in:text,email,date,number,textarea',
            'show_in_form' => 'boolean',
            'show_in_cert' => 'boolean',
            'is_required' => 'boolean',
        ]);

        try {
            $fieldService = app(TemplateFieldService::class);
            $field = $fieldService->addCustomField($template, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Field added successfully!',
                'field' => $field,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update field properties
     */
    public function updateField(Request $request, TemplateField $field)
    {
        // Authorization check
        if ($field->template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify this template.',
            ], 403);
        }

        $request->validate([
            'field_label' => 'sometimes|string|max:255',
            'field_type' => 'sometimes|in:text,email,date,number,textarea',
            'show_in_form' => 'sometimes|boolean',
            'show_in_cert' => 'sometimes|boolean',
            'is_required' => 'sometimes|boolean',
        ]);

        try {
            $fieldService = app(TemplateFieldService::class);
            $updatedField = $fieldService->updateField($field, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully!',
                'field' => $updatedField,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update field position on canvas
     */
    public function updateFieldPosition(Request $request, TemplateField $field)
    {
        // Authorization check
        if ($field->template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify this template.',
            ], 403);
        }

        $request->validate([
            'position_data' => 'required|array',
            'position_data.x' => 'sometimes|numeric',
            'position_data.y' => 'sometimes|numeric',
            'position_data.width' => 'sometimes|numeric',
            'position_data.height' => 'sometimes|numeric',
            'position_data.fontSize' => 'sometimes|numeric',
            'position_data.fontFamily' => 'sometimes|string',
            'position_data.color' => 'sometimes|string',
            'position_data.textAlign' => 'sometimes|string',
            'position_data.bold' => 'sometimes|boolean',
            'position_data.italic' => 'sometimes|boolean',
            'position_data.rotation' => 'sometimes|numeric',
        ]);

        try {
            $fieldService = app(TemplateFieldService::class);
            $updatedField = $fieldService->updateFieldPosition($field, $request->position_data);

            return response()->json([
                'success' => true,
                'message' => 'Field position updated successfully!',
                'field' => $updatedField,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a custom field
     */
    public function deleteField(TemplateField $field)
    {
        // Authorization check
        if ($field->template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify this template.',
            ], 403);
        }

        try {
            $fieldService = app(TemplateFieldService::class);
            $fieldService->deleteField($field);

            return response()->json([
                'success' => true,
                'message' => 'Field deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get canvas fields (show_in_cert = true)
     */
    public function getCanvasFields(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this template.',
            ], 403);
        }

        try {
            $fieldService = app(TemplateFieldService::class);
            $fields = $fieldService->getCanvasFields($template);

            return response()->json([
                'success' => true,
                'fields' => $fields,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get form fields (show_in_form = true)
     */
    public function getFormFields(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this template.',
            ], 403);
        }

        try {
            $fieldService = app(TemplateFieldService::class);
            $fields = $fieldService->getFormFields($template);

            return response()->json([
                'success' => true,
                'fields' => $fields,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to delete this template.');
        }

        try {
            // Check if template has events
            $eventsCount = $template->events()->count();
            if ($eventsCount > 0) {
                return back()->with('error', "Cannot delete template. It is being used by {$eventsCount} event(s). Please delete the events first.");
            }

            // Delete background image
            if ($template->background) {
                Storage::disk('public')->delete($template->background);
            }

            $template->delete();

            return redirect()
                ->route('templates.index')
                ->with('success', 'Template deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete template: ' . $e->getMessage());
        }
    }

    /**
     * Set template as default.
     */
    public function setDefault(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to modify this template.');
        }

        DB::beginTransaction();
        try {
            // Unset all defaults
            Template::query()->update(['is_default' => false]);

            // Set this as default
            $template->is_default = true;
            $template->save();

            DB::commit();

            return back()->with('success', 'Template set as default successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to set default: ' . $e->getMessage());
        }
    }

    /**
     * Download template preview with dummy data.
     */
    public function downloadPreview(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to preview this template.');
        }

        try {
            // Load only certificate fields (show_in_cert = true)
            $template->load(['fields' => function ($query) {
                $query->where('show_in_cert', true)->orderBy('order');
            }]);

            // Check if template has certificate fields
            if ($template->fields->isEmpty()) {
                return back()->with('error', 'Template has no certificate fields. Please enable "Show in Cert" for at least one field.');
            }

            // Generate dummy data based on field types (for all fields, not just cert fields)
            $dummyData = [];
            foreach ($template->fields as $field) {
                $dummyData[$field->field_name] = $this->generateDummyValue($field);
            }

            // Create a temporary certificate number
            $previewCertNumber = 'PREVIEW-' . date('YmdHis');

            // Generate temporary QR code for preview
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)
                ->margin(1)
                ->errorCorrection('H')
                ->generate(url('/verify/' . $previewCertNumber));

            // Save temporary QR code
            $qrDirectory = 'certificates/qrcodes/preview';
            $qrFilename = $previewCertNumber . '.svg';
            $qrCodePath = $qrDirectory . '/' . $qrFilename;
            Storage::disk('public')->put($qrCodePath, $qrCode);

            // Get template background
            $backgroundPath = Storage::disk('public')->path($template->background);
            $qrCodeFullPath = Storage::disk('public')->path($qrCodePath);

            // Get image dimensions and calculate scale
            $imageSize = @getimagesize($backgroundPath);
            $originalWidth = $imageSize ? $imageSize[0] : 1122;
            $originalHeight = $imageSize ? $imageSize[1] : 794;
            $pdfWidth = 1122;
            $pdfHeight = 794;
            $scaleX = $pdfWidth / $originalWidth;
            $scaleY = $pdfHeight / $originalHeight;

            // Prepare field data
            $fields = $template->fields->map(function ($field) use ($dummyData, $scaleX, $scaleY) {
                // Get position data from JSON structure
                $positionData = $field->position_data ?? [];
                
                // Skip fields without position data (not yet positioned on canvas)
                if (empty($positionData) || !isset($positionData['x']) || !isset($positionData['y'])) {
                    return null;
                }
                
                return [
                    'field_name' => $field->field_name,
                    'value' => $dummyData[$field->field_name] ?? '',
                    'x' => ($positionData['x'] ?? 0) * $scaleX,
                    'y' => ($positionData['y'] ?? 0) * $scaleY,
                    'width' => ($positionData['width'] ?? 100) * $scaleX,
                    'height' => ($positionData['height'] ?? 20) * $scaleY,
                    'font_size' => ($positionData['fontSize'] ?? 16) * min($scaleX, $scaleY),
                    'font_family' => $positionData['fontFamily'] ?? 'Arial',
                    'color' => $positionData['color'] ?? '#000000',
                    'alignment' => $positionData['textAlign'] ?? 'left',
                    'bold' => $positionData['bold'] ?? false,
                    'italic' => $positionData['italic'] ?? false,
                ];
            })->filter(); // Remove null values (fields without position data)

            // Convert background to base64
            $backgroundBase64 = null;
            $backgroundExists = false;
            if (file_exists($backgroundPath)) {
                $imageData = base64_encode(file_get_contents($backgroundPath));
                $imageType = pathinfo($backgroundPath, PATHINFO_EXTENSION);
                $backgroundBase64 = "data:image/{$imageType};base64,{$imageData}";
                $backgroundExists = true;
            }

            // Create preview certificate object
            $previewCertificate = (object) [
                'certificate_number' => $previewCertNumber,
                'data' => $dummyData,
            ];

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.certificate', [
                'certificate' => $previewCertificate,
                'template' => $template,
                'backgroundPath' => $backgroundBase64,
                'backgroundExists' => $backgroundExists,
                'qrCodePath' => $qrCodeFullPath,
                'fields' => $fields,
                'data' => $dummyData,
            ]);

            $pdf->setPaper('a4', 'landscape');

            // Clean up temporary QR code
            if (Storage::disk('public')->exists($qrCodePath)) {
                Storage::disk('public')->delete($qrCodePath);
            }

            // Download PDF
            return $pdf->download($template->name . '-preview.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate preview: ' . $e->getMessage());
        }
    }

    /**
     * Generate dummy value based on field type and name.
     */
    private function generateDummyValue(TemplateField $field): string
    {
        $fieldName = strtolower($field->field_name);

        // Check field type
        if ($field->field_type === 'date') {
            return date('F d, Y'); // e.g., "January 15, 2025"
        }

        if ($field->field_type === 'number') {
            return '12345';
        }

        // Generate based on field name
        if (str_contains($fieldName, 'name')) {
            return 'Christopher Alexander Johnson';
        }

        if (str_contains($fieldName, 'email')) {
            return 'example@email.com';
        }

        if (str_contains($fieldName, 'date')) {
            return date('F d, Y');
        }

        if (str_contains($fieldName, 'event')) {
            return 'Professional Development Workshop 2025';
        }

        if (str_contains($fieldName, 'course')) {
            return 'Advanced Leadership Training Program';
        }

        if (str_contains($fieldName, 'organization')) {
            return 'International Professional Association';
        }

        if (str_contains($fieldName, 'title')) {
            return 'Certificate of Achievement';
        }

        if (str_contains($fieldName, 'score') || str_contains($fieldName, 'grade')) {
            return '95%';
        }

        if (str_contains($fieldName, 'hours')) {
            return '40 Hours';
        }

        // Default: Lorem ipsum style text
        return 'Lorem Ipsum Dolor Sit Amet';
    }
}
