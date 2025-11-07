<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Template;
use App\Models\EventField;
use App\Services\EventConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['template', 'creator'])
            ->where('created_by', auth()->id())
            ->latest()
            ->paginate(12);

        return view('pages.events.index', compact('events'));
    }

    public function create()
    {
        $templates = Template::where('created_by', auth()->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
        $fieldTypes = EventField::getFieldTypes();

        return view('pages.events.create', compact('templates', 'fieldTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:templates,id',
            'registration_enabled' => 'boolean',
            'static_values' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Create event
            $event = Event::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'template_id' => $validated['template_id'],
                'registration_enabled' => $request->boolean('registration_enabled', true),
                'static_values' => $validated['static_values'] ?? [],
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('events.show', $event)
                ->with('success', 'Event created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create event: ' . $e->getMessage());
        }
    }

    public function show(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to view this event.');
        }

        $event->load(['template', 'creator', 'fields', 'registrations']);

        return view('pages.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to edit this event.');
        }

        $templates = Template::where('created_by', auth()->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
        $fieldTypes = EventField::getFieldTypes();
        $event->load('fields');

        return view('pages.events.edit', compact('event', 'templates', 'fieldTypes'));
    }

    public function update(Request $request, Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to update this event.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:templates,id',
            'registration_enabled' => 'boolean',
            'static_values' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Update event
            $event->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'template_id' => $validated['template_id'],
                'registration_enabled' => $request->boolean('registration_enabled', true),
                'static_values' => $validated['static_values'] ?? [],
            ]);

            DB::commit();

            return redirect()->route('events.show', $event)
                ->with('success', 'Event updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update event: ' . $e->getMessage());
        }
    }

    public function destroy(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to delete this event.');
        }

        try {
            // Check if event has registrations
            $registrationsCount = $event->registrations()->count();
            if ($registrationsCount > 0) {
                return back()->with('error', "Cannot delete event. It has {$registrationsCount} registration(s). Please delete the registrations first.");
            }

            // Check if event has certificates
            $certificatesCount = $event->certificates()->count();
            if ($certificatesCount > 0) {
                return back()->with('error', "Cannot delete event. It has {$certificatesCount} certificate(s). Please delete the certificates first.");
            }

            $event->delete();
            return redirect()->route('events.index')
                ->with('success', 'Event deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete event: ' . $e->getMessage());
        }
    }

    /**
     * Get static value fields for a template (AJAX)
     */
    public function getStaticValueFields(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this template.',
            ], 403);
        }

        try {
            $eventService = app(EventConfigurationService::class);
            $fields = $eventService->getStaticValueFields($template);

            return response()->json([
                'success' => true,
                'fields' => $fields->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'field_name' => $field->field_name,
                        'field_label' => $field->field_label,
                        'field_type' => $field->field_type,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get registration form preview for an event (AJAX)
     */
    public function getRegistrationFormPreview(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this event.',
            ], 403);
        }

        try {
            $eventService = app(EventConfigurationService::class);
            $formPreview = $eventService->getRegistrationFormPreview($event);

            return response()->json([
                'success' => true,
                'fields' => $formPreview,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get registration form preview from template (AJAX) - for event creation
     */
    public function getTemplateFormPreview(Template $template)
    {
        // Authorization check
        if ($template->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this template.',
            ], 403);
        }

        try {
            // Get form fields from template
            $formFields = $template->fields()
                ->where('show_in_form', true)
                ->orderBy('order')
                ->get()
                ->map(function ($field) {
                    return [
                        'field_name' => $field->field_name,
                        'field_label' => $field->field_label,
                        'field_type' => $field->field_type,
                        'is_required' => $field->is_required,
                        'is_predefined' => $field->is_predefined,
                    ];
                });

            return response()->json([
                'success' => true,
                'fields' => $formFields,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get event configuration summary (AJAX)
     */
    public function getConfigurationSummary(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this event.',
            ], 403);
        }

        try {
            $eventService = app(EventConfigurationService::class);
            $summary = $eventService->getEventConfigurationSummary($event);

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save static values for event
     */
    public function saveStaticValues(Request $request, Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify this event.',
            ], 403);
        }

        $request->validate([
            'static_values' => 'required|array',
        ]);

        try {
            $eventService = app(EventConfigurationService::class);
            $eventService->saveStaticValues($event, $request->static_values);

            return response()->json([
                'success' => true,
                'message' => 'Static values saved successfully!',
                'event' => $event->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function toggleRegistration(Event $event)
    {
        // Authorization check
        if ($event->created_by !== auth()->id()) {
            abort(403, 'You are not authorized to modify this event.');
        }

        $event->update([
            'registration_enabled' => !$event->registration_enabled
        ]);

        return back()->with('success', 'Registration status updated successfully!');
    }
}
