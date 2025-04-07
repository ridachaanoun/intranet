<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\CursusHistory;
use App\Models\Promotion;
use Auth;
use Illuminate\Http\Request;

class Loged_in_user extends Controller
{
    public function getUserDetails()
    {
        $user = Auth::user();

        $personalInfo = $user->personalInfo()->first();
        $accountInfo = $user->accountInfo()->first();
        $profiles = $user->profiles()->first();

        return response()->json([
            'personal_info' => $personalInfo,
            'account_info' => $accountInfo,
            'profiles' => $profiles,
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
        $students = $classroom->students()->get()->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'role' => $student->role,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
                'image_url' => asset('storage/' . $student->image),
            ];
        });

        // Fetch the teacher of the classroom
        $teacher = $classroom->teacher()->first(['id', 'name', 'email', 'image']);
        if ($teacher) {
            $teacher->image_url = asset('storage/' . $teacher->image);
        }
        
        // Fetch the delegate of the classroom
        $delegate = $classroom->delegate()->first(['id', 'name', 'email', 'image']);
        if ($delegate) {
            $delegate->image_url = asset('storage/' . $delegate->image);
        }

        $studentCount = $classroom->students()->count();
        $promostion =   Promotion::find($classroom->promotion_id)->first();
        return response()->json([
            'id' => $classroom->id,
            'slug' => $classroom->slug,
            'name' => $classroom->name,
            'level' => $classroom->level,
            'campus' => $classroom->campus,
            'Learners'=>$studentCount,
            'promostion'=>$promostion,
            'promotion_id' => $classroom->promotion_id,
            'cover_image' => asset('storage/' . $classroom->cover_image),
            'teacher' => $teacher,
            'delegate' => $delegate,
            'created_at' => $classroom->created_at,
            'updated_at' => $classroom->updated_at,
            'students' => $students,
        ]);
    }

    public function getUserCursusHistory()
    {
        $user = Auth::user();


        $cursusHistory = $user->cursusHistories()->with(['student', 'coach', 'promotion'])->get();
        $cursusHistory->each(function ($history) {
            if ($history->coach) {
                $history->coach->image_url = asset('storage/' . $history->coach->image);
            }
        });
        if ($cursusHistory->isEmpty()) {
            return response()->json(['message' => 'No CursusHistory found'], 404);
        }

        return response()->json(['cursus_history' => $cursusHistory]);
    }

}
