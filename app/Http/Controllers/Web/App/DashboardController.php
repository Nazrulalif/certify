<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            // TODO: Add more stats when other modules are ready
            // 'totalTemplates' => Template::count(),
            // 'totalEvents' => Event::count(),
            // 'totalCertificates' => Certificate::count(),
        ];

        return view('pages.dashboard.root', $data);
    }

    /**
     * User dashboard with their activities
     */
    private function userDashboard()
    {
        $data = [
            // TODO: Add user-specific stats when modules are ready
            // 'myTemplates' => Template::where('created_by', auth()->id())->count(),
            // 'myEvents' => Event::where('created_by', auth()->id())->count(),
            // 'myCertificates' => Certificate::where('created_by', auth()->id())->count(),
        ];

        return view('pages.dashboard.user', $data);
    }
}
