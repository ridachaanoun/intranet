<?php
namespace App\Http\Controllers;

use App\Models\User;

class StudentController extends Controller
{
    public function getAllStudents()
    {
        // Fetch all users with the role of 'student'
        $students = User::where('role', 'student')->orderBy('name', 'asc')->get();

        $formattedStudents = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'image_url' => asset('storage/' . $student->image),
                'campus' => $student->campus,
                'level' => $student->level,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
            ];
        });

        return response()->json([
            'students' => $formattedStudents,
        ], 200);
    }
}