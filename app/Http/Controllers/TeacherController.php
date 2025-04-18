<?php
namespace App\Http\Controllers;

use App\Models\User;
class TeacherController extends Controller
{
    public function getAllTeachers()
    {
        // Fetch all users with the role of 'teacher'
        $teachers = User::where('role', 'teacher')->orderBy('name', 'asc')->get();

        return response()->json([
            'teachers' => $teachers,
        ], 200);
    }
}