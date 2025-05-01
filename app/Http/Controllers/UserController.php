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
        $perPage = $request->get('per_page', 30);

        // Paginate the users
        $users = User::orderBy('created_at', 'desc')->paginate($perPage);

        // Return the paginated users data
        return response()->json([
            'users' => $users->items(),
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

        $user->load([
            'personalInfo',
            'accountInfo.promotion',
            'profiles',
        ]);

        // Calculate total points
        $totalPoints = $user->points->sum('points') - $user->points_used;
        $user->Total_points = $totalPoints;

        // Get the latest classroom registration
        $lastClassroom = $user->classrooms()
            ->with(['students', 'delegate', 'promotion', 'teacher'])
            ->latest('classroom_student.created_at')
            ->first();

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

        $students = $classroom->students()->get();

        $teacher = $classroom->teacher()->first(['id', 'name', 'email', 'image']);

        $studentCount = $classroom->students()->count();
        $promotion = Promotion::find($classroom->promotion_id);
        $delegate = $classroom->delegate()->get();
        $classroom->Learners = $studentCount;
        $classroom->promotion = $promotion;
        $classroom->teacher = $teacher;
        $classroom->delegate = $delegate;
        $classroom->students = $students;

        return response()->json($classroom, 200);
    }

    public function getUserCursusHistory(User $user)
    {

        $cursusHistory = $user->cursusHistories()->with(['student', 'coach', 'promotion','class'])->get();

        if ($cursusHistory->isEmpty()) {
            return response()->json(['message' => 'No CursusHistory found'], 404);
        }

        return response()->json(['cursus_history' => $cursusHistory]);
    }

    public function searchUsers(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:50',
            'campus' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:500',
        ]);

        $query = $request->get('query');
        $role = $request->get('role');
        $campus = $request->get('campus');
        $level = $request->get('level');
        $perPage = $request->get('per_page', 30);
        $page = $request->get('page', 1);

        // Build 
        $usersQuery = User::query();

        if ($query) {
            $usersQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });
        }

        if ($role) {
            $usersQuery->where('role', $role);
        }

        if ($campus) {
            $usersQuery->where('campus', 'like', "%{$campus}%");
        }

        if ($level) {
            $usersQuery->where('level', $level);
        }

        $users = $usersQuery->orderBy('created_at', 'desc')->paginate($perPage);

        if ($page > $users->lastPage()) {
            return response()->json([
                'users' => [],
                'current_page' => $page,
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'message' => 'No users found on this page.',
            ]);
        }

        // Return
        return response()->json([
            'users' => $users->items(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ]);
    }
}