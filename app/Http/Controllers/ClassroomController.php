<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
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
            ]
        ], 201);
    }
}