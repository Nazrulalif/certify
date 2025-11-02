<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateField;
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
        $template->load('fields');
        return view('pages.templates.edit', compact('template'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, Template $template)
    {
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
     * Remove the specified template from storage.
     */
    public function destroy(Template $template)
    {
        try {
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
}
