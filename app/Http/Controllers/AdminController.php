<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends Controller
{
use AuthorizesRequests;
    public function updateUserRole(Request $request, User $user)
    {
        $this->authorize("create",$user);

        $validatedData = $request->validate([
            'role' => 'required|string|in:admin,user,student',
        ]);

        // Update the user's role
        $user->role = $validatedData['role'];
        $user->save();

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ]);
    }
}