<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
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
        $users = User::orderBy('created_at', 'desc')->paginate($perPage);

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

    public function getUserDetails(User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Eager load related data
        $user->load([
            'personalInfo',
            'accountInfo.promotion',
            'profiles',
        ]);
    
        // Calculate total points
        $totalPoints = $user->points->sum('points');
    
        // Add additional attributes
        $user->image_url = asset('storage/' . $user->image);
        $user->Total_points = $totalPoints;
    
        // Get the latest classroom registration
        $lastClassroom = $user->classrooms()->with('students')->latest('classroom_student.created_at')->first();
    
        // Return response
        return response()->json([
            'user' => $user,
            'lastClassroom' => $lastClassroom,
        ], 200);
    }

    public function getClassroomDetailsByUserId(User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $classroom = $user->classroomsAsStudent()->latest()->first();

        if (!$classroom) {
            return response()->json(['message' => 'No classrooms found'], 404);
        }

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

        $teacher = $classroom->teacher()->first(['id', 'name', 'email', 'image']);
        if ($teacher) {
            $teacher->image_url = asset('storage/' . $teacher->image);
        }

        $delegate = $classroom->delegate()->first(['id', 'name', 'email', 'image']);
        if ($delegate) {
            $delegate->image_url = asset('storage/' . $delegate->image);
        }

        $studentCount = $classroom->students()->count();
        $promotion = Promotion::find($classroom->promotion_id);

        return response()->json([
            'id' => $classroom->id,
            'slug' => $classroom->slug,
            'name' => $classroom->name,
            'level' => $classroom->level,
            'campus' => $classroom->campus,
            'Learners' => $studentCount,
            'promotion' => $promotion,
            'promotion_id' => $classroom->promotion_id,
            'cover_image' => asset('storage/' . $classroom->cover_image),
            'teacher' => $teacher,
            'delegate' => $delegate,
            'created_at' => $classroom->created_at,
            'updated_at' => $classroom->updated_at,
            'students' => $students,
        ], 200);
    }
}