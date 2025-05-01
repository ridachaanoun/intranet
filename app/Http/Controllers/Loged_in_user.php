<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Auth;
use Illuminate\Http\Request;

class Loged_in_user extends Controller
{
    public function getUserDetails()
    {
        // Get the authenticated user
        $user = Auth::user();
    
        // Eager load related data
        $user->load([
            'personalInfo',
            'accountInfo.promotion',
            'profiles',
        ]);
    
        // Calculate total points
        $totalPoints = $user->points->sum('points') - $user->points_used;
    
        $user->Total_points = $totalPoints;
    
        // Return response
        return response()->json([
            'user' => $user,
        ], 200);
    }

    public function getUserClassroom()
    {
        $user = Auth::user();

        // Fetch the last classroom of the logged-in user
        $classroom = $user->classroomsAsStudent()->latest()->first();

        if (!$classroom) {
            return response()->json(['message' => 'No classrooms found'], 404);
        }

        // Fetch the students of the classroom
        $students = $classroom->students()->get();

        // Fetch the teacher of the classroom
        $teacher = $classroom->teacher()->first(['id', 'name', 'email', 'image']);

        // Fetch the delegate of the classroom
        $delegate = $classroom->delegate()->first(['id', 'name', 'email', 'image']);

        $studentCount = $classroom->students()->count();
        $promostion =   Promotion::find($classroom->promotion_id)->first();

        $classroom->promostion = $promostion;
        $classroom->teacher = $teacher;
        $classroom->delegate = $delegate;
        $classroom->students = $students;
        $classroom->Learners = $studentCount;

        return response()->json($classroom);
    }

    public function getUserCursusHistory()
    {
        $user = Auth::user();

        $cursusHistory = $user->cursusHistories()->with(['student', 'coach', 'promotion','class'])->get();

        if ($cursusHistory->isEmpty()) {
            return response()->json(['message' => 'No CursusHistory found'], 404);
        }

        return response()->json(['cursus_history' => $cursusHistory]);
    }

}
