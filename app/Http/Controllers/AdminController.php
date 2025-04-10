<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminController extends Controller
{
use AuthorizesRequests;
    public function updateUserRole(Request $request, User $user)
    {
        $this->authorize("admin",$user);

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

    public function uploadImage(Request $request,user $user)
    {

        $this->authorize("admin",$user);
        
        // Validate the request data
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000',
        ]);
    

        // Handle the file upload
        if ($request->hasFile('image')) {
            // Get the file from the request
            $file = $request->file('image');
    
            // Define the file path and name
            $filePath = 'profile_images';
            
            // Store the file
            $path = $file->store($filePath, 'public');
    
            // Update the user's image path
            $user->image = $path;
            $user->save();
    
            return response()->json([
                'message' => 'Image uploaded successfully',
                'image_url' => asset('storage/' . $path),
            ], 200);
        }
    
        return response()->json(['error' => 'Image upload failed'], 500);
    }

    public function deleteUser($id)
    {

        $this->authorize("admin",User::class);
        // Find the user by ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }


        // Delete the user
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}