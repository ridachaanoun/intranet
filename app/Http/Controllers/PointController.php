<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Point;
use Illuminate\Support\Facades\Auth;

class PointController extends Controller
{
    use AuthorizesRequests;
    public function assignPoints(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $teacher = Auth::user();

        $this->authorize("teacher",$teacher);
        $point = Point::create([
            'student_id' => $request->student_id,
            'teacher_id' => $teacher->id,
            'points' => $request->points,
            'reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Points assigned successfully', 'point' => $point], 201);
    }
}
