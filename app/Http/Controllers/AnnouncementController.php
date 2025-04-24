<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request)
    {
        $this->authorize("admin",User::class);
        // Validate the request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Create the announcement
        $announcement = Announcement::create($validatedData);

        // Return a JSON response
        return response()->json([
            'message' => 'Announcement created successfully',
            'announcement' => $announcement,
        ], 201);
    }
    public function index()
    {
        // Fetch all announcements
        $announcements = Announcement::orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Announcements retrieved successfully',
            'announcements' => $announcements,
        ]);
    }
}