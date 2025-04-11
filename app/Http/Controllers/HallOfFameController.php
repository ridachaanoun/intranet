<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Point;

class HallOfFameController extends Controller
{
    /**
     * Get Top 3 Students by Total Points
     */
    public function getTopStudents()
    {
        try {
            // Aggregate points for each student and get the top 3
            $topStudents = Point::with('student') // Eager load the student relationship
                ->selectRaw('student_id, SUM(points) as total_points')
                ->groupBy('student_id')
                ->orderByDesc('total_points')
                ->limit(3)
                ->get();

            // Transform the data for the response
            $response = $topStudents->map(function ($point) {
                return [
                    'id' => $point->student->id,
                    'name' => $point->student->name,
                    'total_points' => $point->total_points,
                    'image' => asset("storage/".$point->student->image) ,

                ];
            });

            return response()->json([
                'success' => true,
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Hall of Fame',
            ], 500);
        }
    }
}
