<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Template;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $results = collect();

        $template = Template::where('created_by', auth()->id())->search($query)->take(5)->get();
        $events = Event::where('created_by', auth()->id())->search($query)->take(5)->get();
        $certificates = Certificate::where('created_by', auth()->id())->search($query)->take(5)->get();

        // Map users
        if (Auth::user()->is_admin) {
            $users = User::search($query)->take(5)->get();

            // Map users
            $results = $results->concat(
                $users->map(function ($i) use ($query) {
                    $match_details = '';
                    $roleName = $i->getRoleName();

                    // Check if role name matches the search query
                    if (stripos($roleName, $query) !== false) {
                        $match_details = 'Role: ' . $this->highlight($roleName, $query);
                    }

                    // Check if email matches
                    if (stripos($i->email, $query) !== false && empty($match_details)) {
                        $match_details = 'Email: ' . $this->highlight($i->email, $query);
                    }

                    return [
                        'group' => 'User',
                        'title' => $i->name,
                        'highlighted' => $this->highlight($i->name, $query),
                        'match_info' => $match_details,
                        'url' => route('users.edit', $i->id),
                    ];
                }),
            );
        }

        // Map templates
        $results = $results->concat(
            $template->map(function ($i) use ($query) {
                $match_details = '';
                $templateName = $i->name;

                // Check if template name matches the search query
                if (stripos($templateName, $query) !== false) {
                    $match_details = 'Template: ' . $this->highlight($templateName, $query);
                }

                return [
                    'group' => 'Template',
                    'title' => $i->name,
                    'highlighted' => $this->highlight($i->name, $query),
                    'match_info' => $match_details,
                    'url' => route('templates.edit', $i->id),
                ];
            }),
        );

        // Map events
        $results = $results->concat(
            $events->map(function ($i) use ($query) {
                $match_details = '';
                $eventName = $i->name;

                // Check if event name matches the search query
                if (stripos($eventName, $query) !== false) {
                    $match_details = 'Event: ' . $this->highlight($eventName, $query);
                }

                return [
                    'group' => 'Event',
                    'title' => $i->name,
                    'highlighted' => $this->highlight($i->name, $query),
                    'match_info' => $match_details,
                    'url' => route('events.show', $i->id),
                ];
            }),
        );

         // Map certificates
        $results = $results->concat(
            $certificates->map(function ($i) use ($query) {
                $match_details = '';
                $certificateNumber = $i->certificate_number;

                // Check if certificate number matches the search query
                if (stripos($certificateNumber, $query) !== false) {
                    $match_details = 'Certificate: ' . $this->highlight($certificateNumber, $query);
                }

                return [
                    'group' => 'Certificate',
                    'title' => $i->certificate_number,
                    'highlighted' => $this->highlight($i->certificate_number, $query),
                    'match_info' => $match_details,
                    'url' => route('certificates.show', $i->id),
                ];
            }),
        );

        return response()->json($results);
    }

    public function highlight($text, $query)
    {
        $escapedText = e($text); // Escape HTML
        $escapedQuery = preg_quote($query, '/');

        // Check if query is empty to avoid errors with empty pattern
        if (empty($escapedQuery)) {
            return $escapedText;
        }

        return preg_replace("/($escapedQuery)/i", '<strong style="color: orange;">$1</strong>', $escapedText);
    }
}
