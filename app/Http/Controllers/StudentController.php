<?php
namespace App\Http\Controllers;

use App\Models\User;

class StudentController extends Controller
{
    public function getAllStudents()
    {
        // Fetch all users with the role of 'student'
        $students = User::where('role', 'student')->orderBy('name', 'asc')->get();

        return response()->json([
            'students' => $students,
        ], 200);
    }
}