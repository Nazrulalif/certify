<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Template;
use App\Models\Event;
use App\Models\Certificate;
use App\Models\Registration;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display dashboard based on user role
     */
    public function index()
    {
        $user = auth()->user();

        // Root dashboard
        if ($user->isRoot()) {
            return $this->rootDashboard();
        }

        // User dashboard
        return $this->userDashboard();
    }

    /**
     * Root dashboard with system overview
     */
    private function rootDashboard()
    {
        $data = [
            'totalUsers' => User::count(),
            'activeUsers' => User::active()->count(),
            'rootUsers' => User::roots()->count(),
            'regularUsers' => User::users()->count(),
            'totalTemplates' => Template::count(),
            'totalEvents' => Event::count(),
            'totalCertificates' => Certificate::count(),
            'totalRegistrations' => Registration::count(),
            'verificationsToday' => 0, // TODO: Implement verification tracking
        ];

        return view('pages.dashboard.root', $data);
    }

    /**
     * User dashboard with their activities
     */
    private function userDashboard()
    {
        $userId = auth()->id();
        
        $data = [
            'myTemplates' => Template::where('created_by', $userId)->count(),
            'myEvents' => Event::where('created_by', $userId)->count(),
            'activeEvents' => Event::where('created_by', $userId)
                ->where('registration_enabled', true)
                ->count(),
            'myCertificates' => Certificate::whereHas('event', function($query) use ($userId) {
                $query->where('created_by', $userId);
            })->count(),
            'certificatesThisMonth' => Certificate::whereHas('event', function($query) use ($userId) {
                $query->where('created_by', $userId);
            })->whereMonth('generated_at', now()->month)
              ->whereYear('generated_at', now()->year)
              ->count(),
            'totalRegistrations' => Registration::whereHas('event', function($query) use ($userId) {
                $query->where('created_by', $userId);
            })->count(),
            'recentCertificates' => Certificate::with(['event', 'registration'])
                ->whereHas('event', function($query) use ($userId) {
                    $query->where('created_by', $userId);
                })
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return view('pages.dashboard.user', $data);
    }
}
