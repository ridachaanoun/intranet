<?php
namespace App\Http\Controllers;

use App\Models\Promotion;
class PromotionController extends Controller
{
    public function getAllPromotions()
    {
        $promotions = Promotion::all();

        return response()->json([
            'promotions' => $promotions,
        ], 200);
    }
}