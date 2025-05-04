<?php
namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PromotionController extends Controller
{
    use AuthorizesRequests;
    
    public function getAllPromotions()
    {
        $promotions = Promotion::all();

        return response()->json([
            'promotions' => $promotions,
        ], 200);
    }
    
    public function storePromotion(Request $request)
    {
        $this->authorize('admin', User::class);
        
        $request->validate([
            'year' => 'required|string|unique:promotions,year',
        ]);
        
        $promotion = Promotion::create([
            'year' => $request->year,
        ]);
        
        return response()->json([
            'message' => 'Promotion created successfully',
            'promotion' => $promotion,
        ], 201);
    }
}