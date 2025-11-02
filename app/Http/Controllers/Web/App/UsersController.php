<?php

namespace App\Http\Controllers\Web\App;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
    /**
     * Display a listing of users with DataTables
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::query();

            return DataTables::of($users)
                ->addIndexColumn() // Adds DT_RowIndex
                ->addColumn('user', function ($row) {
                    return '
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                <div class="symbol-label">
                                    <img src="' . $row->getAvatarAttribute() . '" alt="' . $row->name . '" class="w-100">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="' . route('users.edit', $row->id) . '" class="text-gray-800 text-hover-primary mb-1 fs-6 fw-bold">' . $row->name . '</a>
                                <span class="text-muted fw-semibold text-muted d-block fs-7">' . $row->email . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('myself', function ($row) {
                    return $row->id === Auth::user()->id ? true : false;
                })
                ->addColumn('role', function ($row) {
                    $badgeClass = $row->role === User::ROLE_ROOT ? 'badge-light-danger' : 'badge-light-primary';
                    $roleName = $row->getRoleName();
                    return '<span class="badge ' . $badgeClass . '">' . $roleName . '</span>';
                })
                ->addColumn('status', function ($row) {
                    if ($row->status) {
                        return '<div class="badge badge-light-success">Active</div>';
                    } else {
                        return '<div class="badge badge-light-danger">Inactive</div>';
                    }
                })
                ->addColumn('action', function ($row) {
                    return view('layouts.partials.action-button.users.index', [
                        'row' => $row,
                    ])->render();
                })
                ->rawColumns(['user', 'role', 'status', 'action'])
                ->make(true);
        }

        return view('pages.users.index');
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('pages.users.create');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(Request $request, $id)
    {
        $user = User::findOrFail($id);
        return view('pages.users.edit', compact('user'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:' . User::ROLE_ROOT . ',' . User::ROLE_USER,
            'status' => 'nullable|boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['status'] = $request->has('status') ? true : false;

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:' . User::ROLE_ROOT . ',' . User::ROLE_USER,
            'status' => 'nullable|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['status'] = $request->has('status') ? true : false;

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate the specified user
     */
    public function deactive($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deactivating current user
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 400);
            }

            $user->update(['status' => false]);

            return response()->json([
                'success' => true,
                'message' => 'User deactivated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate the specified user
     */
    public function reactive($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->update(['status' => true]);

            return response()->json([
                'success' => true,
                'message' => 'User reactivated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete selected users
     */
    public function bulk_destroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users selected for deletion'
                ], 400);
            }

            // Prevent deleting current logged-in user
            $currentUserId = Auth::id();
            if (in_array($currentUserId, $ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 400);
            }

            // Delete users
            $deletedCount = User::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} user(s)"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users: ' . $e->getMessage()
            ], 500);
        }
    }
}
