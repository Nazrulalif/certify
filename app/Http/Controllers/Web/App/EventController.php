<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Template;
use App\Models\EventField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['template', 'creator'])
            ->latest()
            ->paginate(12);

        return view('pages.events.index', compact('events'));
    }

    public function create()
    {
        $templates = Template::orderBy('is_default', 'desc')->orderBy('name')->get();
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
            'fields' => 'required|array|min:1',
            'fields.*.field_name' => 'required|string',
            'fields.*.field_label' => 'required|string',
            'fields.*.field_type' => 'required|string',
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Create event
            $event = Event::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'template_id' => $validated['template_id'],
                'registration_enabled' => $request->boolean('registration_enabled', true),
                'created_by' => Auth::id(),
            ]);

            // Create event fields
            foreach ($validated['fields'] as $index => $field) {
                EventField::create([
                    'event_id' => $event->id,
                    'field_name' => $field['field_name'],
                    'field_label' => $field['field_label'],
                    'field_type' => $field['field_type'],
                    'required' => $field['required'] ?? false,
                    'options' => $field['options'] ?? null,
                    'order' => $index,
                ]);
            }

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
        $event->load(['template', 'creator', 'fields', 'registrations']);

        return view('pages.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $templates = Template::orderBy('is_default', 'desc')->orderBy('name')->get();
        $fieldTypes = EventField::getFieldTypes();
        $event->load('fields');

        return view('pages.events.edit', compact('event', 'templates', 'fieldTypes'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:templates,id',
            'registration_enabled' => 'boolean',
            'fields' => 'required|array|min:1',
            'fields.*.field_name' => 'required|string',
            'fields.*.field_label' => 'required|string',
            'fields.*.field_type' => 'required|string',
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Update event
            $event->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'template_id' => $validated['template_id'],
                'registration_enabled' => $request->boolean('registration_enabled', true),
            ]);

            // Delete old fields and create new ones
            $event->fields()->delete();

            foreach ($validated['fields'] as $index => $field) {
                EventField::create([
                    'event_id' => $event->id,
                    'field_name' => $field['field_name'],
                    'field_label' => $field['field_label'],
                    'field_type' => $field['field_type'],
                    'required' => $field['required'] ?? false,
                    'options' => $field['options'] ?? null,
                    'order' => $index,
                ]);
            }

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
        try {
            $event->delete();
            return redirect()->route('events.index')
                ->with('success', 'Event deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete event: ' . $e->getMessage());
        }
    }

    public function toggleRegistration(Event $event)
    {
        $event->update([
            'registration_enabled' => !$event->registration_enabled
        ]);

        return back()->with('success', 'Registration status updated successfully!');
    }
}
