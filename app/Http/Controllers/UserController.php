<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

class UserController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('view-users');

            $users = User::all();
            return response()->json($users);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized | custom', 'message' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch users', 'message' => $e->getMessage()], 500);
        }
    }

    public function assignRole(int $id, Request $request)
    {
        try {
            $this->authorize('assign-roles');

            $request->validate([
                'role' => 'required|in:admin,editor,author',
            ]);

            $user = User::findOrFail($id);
            $user->role = $request->role;
            $user->save();

            return response()->json(['message' => 'Role updated', 'user' => $user]);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized', 'message' => $e->getMessage()], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to assign role', 'message' => $e->getMessage()], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json($user);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch profile', 'message' => $e->getMessage()], 500);
        }
    }
}
