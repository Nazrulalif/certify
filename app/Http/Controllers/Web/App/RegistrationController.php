<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    // Show public registration form
    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->with(['template.formFields'])
            ->firstOrFail();

        if (!$event->registration_enabled) {
            abort(403, 'Registration for this event is currently closed.');
        }

        $registrationService = app(RegistrationService::class);
        $formConfig = $registrationService->getFormConfiguration($event);

        return view('pages.registrations.form', [
            'event' => $event,
            'formConfig' => $formConfig,
        ]);
    }

    // Store public registration
    public function store(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)
            ->with(['template.formFields'])
            ->firstOrFail();

        if (!$event->registration_enabled) {
            return back()->with('error', 'Registration for this event is currently closed.');
        }

        try {
            $registrationService = app(RegistrationService::class);
            $registration = $registrationService->createRegistration($event, $request->all());

            return redirect()->route('register.success')
                ->with('success', 'Registration submitted successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to submit registration: ' . $e->getMessage());
        }
    }

    // Show success page
    public function success()
    {
        return view('pages.registrations.success');
    }

    // List registrations for an event (admin only)
    public function index(Request $request, Event $event)
    {
        if ($request->ajax()) {
            $registrations = $event->registrations()->latest('registered_at');

            return datatables()->of($registrations)
                ->addIndexColumn()
                ->addColumn('fields', function ($row) use ($event) {
                    $html = '';
                    $formData = $row->form_data ?? [];
                    foreach ($event->template->formFields as $field) {
                        $value = $formData[$field->field_name] ?? '-';
                        $html .= '<div class="mb-1"><span class="fw-bold text-gray-600">' . $field->field_label . ':</span> ' . $value . '</div>';
                    }
                    return $html;
                })
                ->addColumn('status', function ($row) {
                    $statusClass = $row->status === 'approved' ? 'success' : ($row->status === 'rejected' ? 'danger' : 'warning');
                    return '<div class="dropdown">
                        <button class="btn btn-sm btn-light-' . $statusClass . ' dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            ' . ucfirst($row->status) . '
                        </button>
                        <ul class="dropdown-menu">' . $this->getStatusOptions($row) . '</ul>
                    </div>';
                })
                ->addColumn('registered_at_formatted', function ($row) {
                    return $row->registered_at->format('M d, Y h:i A');
                })
                ->addColumn('action', function ($row) use ($event) {
                    return view('layouts.partials.action-button.registrations.index', [
                        'event' => $event,
                        'registration' => $row,
                    ])->render();
                })
                ->rawColumns(['fields', 'status', 'action'])
                ->make(true);
        }

        $event->load(['template.formFields']);
        return view('pages.registrations.index', compact('event'));
    }

    private function getStatusOptions($registration)
    {
        $html = '';
        foreach (Registration::getStatuses() as $statusValue => $statusLabel) {
            if ($statusValue !== $registration->status) {
                $html .= '<li>
                    <form action="' . route('events.registrations.update-status', [$registration->event_id, $registration->id]) . '" method="POST">
                        ' . csrf_field() . method_field('PATCH') . '
                        <input type="hidden" name="status" value="' . $statusValue . '">
                        <button type="submit" class="dropdown-item">Change to ' . $statusLabel . '</button>
                    </form>
                </li>';
            }
        }
        return $html;
    }

    // Update registration status
    public function updateStatus(Event $event, Registration $registration, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $registration->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Registration status updated successfully!');
    }

    // Delete registration
    public function destroy(Event $event, Registration $registration)
    {
        try {
            $registration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registration deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete selected registrations
     */
    public function bulkDestroy(Request $request, Event $event)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No registrations selected for deletion'
                ], 400);
            }

            // Delete registrations
            $deletedCount = Registration::whereIn('id', $ids)
                ->where('event_id', $event->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} registration(s)"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete registrations: ' . $e->getMessage()
            ], 500);
        }
    }
}
