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
                    return $row->getFieldValue('name') ?? $row->getFieldValue('participant_name') ?? '-';
                })
                ->addColumn('generated_by', function ($row) {
                    return $row->generator ? $row->generator->name : '-';
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
        $events = Event::with('template')->get();
        return view('pages.certificates.create', compact('events'));
    }

    public function getEventRegistrations(Event $event)
    {
        try {
            $registrations = $event->registrations()
                ->whereDoesntHave('certificate')
                ->get()
                ->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'name' => $registration->getFieldValue('name') ?? $registration->getFieldValue('participant_name') ?? 'N/A',
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
        $certificate->load(['event.template', 'registration', 'generator']);
        return view('pages.certificates.show', compact('certificate'));
    }

    public function destroy(Certificate $certificate)
    {
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

    public function download(Certificate $certificate)
    {
        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            abort(404, 'Certificate PDF not found');
        }

        $path = Storage::disk('public')->path($certificate->pdf_path);
        return response()->download($path, $certificate->certificate_number . '.pdf');
    }

    public function preview(Certificate $certificate)
    {
        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            abort(404, 'Certificate PDF not found');
        }

        return response()->file(Storage::disk('public')->path($certificate->pdf_path));
    }
}
