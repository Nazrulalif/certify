<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index()
    {
        return view('pages.verify.index');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'certificate_number' => 'required|string',
        ]);

        $certificate = Certificate::with(['event', 'generator'])
            ->where('certificate_number', $request->certificate_number)
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found. Please check the certificate number and try again.',
            ]);
        }

        return response()->json([
            'success' => true,
            'certificate' => [
                'certificate_number' => $certificate->certificate_number,
                'event_name' => $certificate->event->name,
                'recipient_name' => $certificate->getFieldValue('name') ?? $certificate->getFieldValue('participant_name') ?? 'N/A',
                'generated_at' => $certificate->generated_at->format('F d, Y'),
                'data' => $certificate->data,
            ],
        ]);
    }

    public function show($certificateNumber)
    {
        $certificate = Certificate::with(['event', 'generator'])
            ->where('certificate_number', $certificateNumber)
            ->first();

        if (!$certificate) {
            abort(404, 'Certificate not found');
        }

        return view('pages.verify.show', compact('certificate'));
    }
}
