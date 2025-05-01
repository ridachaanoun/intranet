<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize("admin", User::class);
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:500',
        ]);
        $perPage = $request->get('per_page', 30);

        $products = Product::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([$products]);
    }

}
