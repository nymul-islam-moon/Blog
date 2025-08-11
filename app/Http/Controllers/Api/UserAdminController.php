<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    /**
     * GET /api/users  (admin only)
     * Returns paginated users with their roles.
     */
    public function index(Request $request)
    {
        $users = User::with(['roles:id,name'])
            ->orderBy('id', 'asc')
            ->paginate($request->integer('per_page', 10));

        return response()->json($users);
    }

    /**
     * POST /api/users/{user}/assign-role  (admin only)
     * Body: { "role": "admin|editor|author" }
     */
    public function assignRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => [
                'required',
                'string',
                // must exist in roles table
                Rule::exists('roles', 'name'),
            ],
        ]);

        $user->assignRole($data['role']);

        // return the updated user with roles
        $user->load('roles:id,name');

        return response()->json([
            'message' => "Role '{$data['role']}' assigned to user {$user->id}.",
            'user'    => $user,
        ], 200);
    }
}
