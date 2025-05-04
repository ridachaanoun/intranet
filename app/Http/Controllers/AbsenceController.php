<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
use AuthorizesRequests;

    public function addAbsence(Request $request)
    {
        $this->authorize("teacher", User::class);

        $request->validate([
            'date' => 'required|date',
            'status' => 'required|string',
            'class' => 'required|string',
            'session' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'reason' => 'nullable|string',
        ]);

        // Get the authenticated user's ID
        $confirmedBy = Auth::id();

        if (!$confirmedBy) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $absence = Absence::create([
            'date' => $request->date,
            'status' => $request->status,
            'class' => $request->class,
            'session' => $request->session,
            'confirmed_by' => $confirmedBy,
            'user_id' => $request->user_id,
            'classroom_id' => $request->classroom_id,
            'reason' => $request->reason,
        ]);

        $absence->load('user');

        return response()->json(['message' => 'Absence added successfully', 'absence' => $absence,"id"=>$confirmedBy], 201);
    }
    public function getAbsenceDetailsByUserId (User $user){

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $absences = $user->absences->sortByDesc('created_at');

        if ($absences->isEmpty()) {
            return response()->json(['message' => 'No absences found for this user'], 404);
        }

        return response()->json(['absences' => $absences], 200);
    
    }
    public function getAbsenceDetailsByClassroomId($classroomId)
    {
        $absences = Absence::where('classroom_id', $classroomId)
            ->orderBy('created_at', 'desc')
            ->get();
        $absences->load('user');

        if (!$absences) {
            return response()->json(['message' => 'Classroom not found'], 404);
        }
        if ($absences->isEmpty()) {
            return response()->json(['message' => 'No absences found for this classroom'], 404);
        }

        return response()->json(['absences' => $absences], 200);
    }
}
