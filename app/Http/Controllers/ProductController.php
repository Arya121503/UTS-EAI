<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
    }

    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (is_array($data)) {
            $products = [];
            foreach ($data as $item) {
                $product = Product::create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'stock' => $item['stock'],
                    'price' => $item['price'],
                ]);
                $products[] = $product;
            }
            return response()->json($products, 201);
        } else {
            return response()->json(['error' => 'Data tidak valid'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $request->validate([
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);

        // Update hanya field yang ada di request
        if ($request->has('price')) {
            $product->price = $request->price;
        }
        if ($request->has('stock')) {
            $product->stock = $request->stock;
        }

        $product->save();

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }
        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}
