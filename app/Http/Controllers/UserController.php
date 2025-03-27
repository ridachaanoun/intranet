<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getAllUsers(Request $request)
    {
        // Validate the request data
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:500',
        ]);

        // Get the per_page value from the request or set a default value
        $perPage = $request->get('per_page', 100);

        // Paginate the users
        $users = User::paginate($perPage);

        // Format the users data
        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'image' => asset('storage/' . $user->image),
                'level' => $user->level,
                'classroom' => $user->classroom,
                'referent_coach' => $user->referent_coach,
            ];
        });

        // Return the paginated and formatted users data
        return response()->json([
            'users' => $formattedUsers,
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ]);
    }
}