<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function createClassroom(Request $request)
    {
        // Validate the request data
        $request->validate([
            'slug' => 'required|string|unique:classrooms,slug',
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'campus' => 'required|string|max:255',
            'promotion_id' => 'required|exists:promotions,id',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000',
            'teacher_id' => 'required|exists:users,id',
            'delegate_id' => 'nullable|exists:users,id',
        ]);

        // Handle the file upload
        if ($request->hasFile('cover_image')) {
            // Get the file from the request
            $file = $request->file('cover_image');

            // Define the file path and name
            $filePath = 'classrooms_images';
            
            // Store the file
            $path = $file->store($filePath, 'public');
        } else {
            return response()->json([
                'message' => 'Image upload failed'
            ], 500);
        }

        // Create the classroom
        $classroom = Classroom::create([
            'slug' => $request->slug,
            'name' => $request->name,
            'level' => $request->level,
            'campus' => $request->campus,
            'promotion_id' => $request->promotion_id,
            'cover_image' => $path,
            'teacher_id' => $request->teacher_id,
            'delegate_id' => $request->delegate_id,
        ]);

        // Fetch the teacher and delegate details
        $teacher = User::find($classroom->teacher_id);
        $delegate = User::find($classroom->delegate_id);

        // Generate asset paths for teacher and delegate images
        $teacherImage = $teacher ? asset('storage/' . $teacher->image) : null;
        $delegateImage = $delegate ? asset('storage/' . $delegate->image) : null;

        return response()->json([
            'message' => 'Classroom created successfully',
            'classroom' => [
                'id' => $classroom->id,
                'slug' => $classroom->slug,
                'name' => $classroom->name,
                'level' => $classroom->level,
                'campus' => $classroom->campus,
                'promotion_id' => $classroom->promotion_id,
                'cover_image' => asset('storage/' . $path),
                'teacher_id' => $classroom->teacher_id,
                'delegate_id' => $classroom->delegate_id,
                'created_at' => $classroom->created_at,
                'updated_at' => $classroom->updated_at,
                'learners' => 0,
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'email_verified_at' => $teacher->email_verified_at,
                    'role' => $teacher->role,
                    'created_at' => $teacher->created_at,
                    'updated_at' => $teacher->updated_at,
                    'image' => $teacherImage,
                ],
                'delegate' => $delegate ? [
                    'id' => $delegate->id,
                    'name' => $delegate->name,
                    'email' => $delegate->email,
                    'email_verified_at' => $delegate->email_verified_at,
                    'role' => $delegate->role,
                    'created_at' => $delegate->created_at,
                    'updated_at' => $delegate->updated_at,
                    'image' => $delegateImage,
                ] : null,
            ]
        ], 201);
    }
}