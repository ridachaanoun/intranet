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
        $this->authorize("teacher",User::class);
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|string',
            'class' => 'required|string',
            'session' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string',
        ]);

        $absence = Absence::create([
            'date' => $request->date,
            'status' => $request->status,
            'class' => $request->class,
            'session' => $request->session,
            'confirmed_by' => Auth::id(),
            'user_id' => $request->user_id,
            'reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Absence added successfully', 'absence' => $absence], 201);
    }
    public function getAbsenceDetailsByUserId (User $user){

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $absences = $user->absences;

        if ($absences->isEmpty()) {
            return response()->json(['message' => 'No absences found for this user'], 404);
        }

        return response()->json(['absences' => $absences], 200);
    
    }
}
