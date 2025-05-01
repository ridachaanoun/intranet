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
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:500',
        ]);
        $perPage = $request->get('per_page', 30);

        $products = Product::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([$products]);
    }

    public function store(Request $request)
    {
        $this->authorize("admin", User::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5000',
            'description' => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $this->authorize("admin", User::class);

        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'price' => 'sometimes|required|integer',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:5000',
            'description' => 'sometimes|required|string',
        ]);
        if ($request->hasfile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }
        $product->update($validated);
        return response()->json($product);
    }
}
