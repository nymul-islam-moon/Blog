<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function assignRole($id, Request $request)
    {
        $request->validate(['role' => 'required|in:admin,editor,author']);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Role updated', 'user' => $user]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}
