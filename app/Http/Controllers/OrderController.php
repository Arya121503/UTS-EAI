<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::all());
    }

    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Validasi user dari UserService
        $userResponse = Http::get('http://localhost:8000/api/users/' . $request->user_id);
        if ($userResponse->failed()) {
            return response()->json(['message' => 'User tidak ditemukan di UserService'], 404);
        }

        $orders = [];
        $totalOrderPrice = 0;

        foreach ($request->products as $item) {
            // Ambil data produk dari ProductService
            $productResponse = Http::get('http://localhost:8001/api/products/' . $item['product_id']);
            if ($productResponse->failed()) {
                return response()->json(['message' => 'Produk dengan ID ' . $item['product_id'] . ' tidak ditemukan'], 404);
            }
            $product = $productResponse->json();

            $totalPrice = $product['price'] * $item['quantity'];
            $totalOrderPrice += $totalPrice;

            // Simpan order per produk
            $order = Order::create([
                'user_id' => $request->user_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'total_price' => $totalPrice,
                'status' => 'completed',
            ]);

            $orders[] = $order;
        }

        return response()->json([
            'message' => 'Order berhasil dibuat',
            'total_price' => $totalOrderPrice,
            'orders' => $orders,
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string',
        ]);

        // Jika quantity diupdate, hitung ulang total_price berdasarkan product
        if ($request->has('quantity')) {
            // Ambil data produk dari ProductService
            $productResponse = Http::get('http://localhost:8001/api/products/' . $order->product_id);
            if ($productResponse->failed()) {
                return response()->json(['message' => 'Produk tidak ditemukan di ProductService'], 404);
            }
            $product = $productResponse->json();

            $order->quantity = $request->quantity;
            $order->total_price = $product['price'] * $request->quantity;
        }

        if ($request->has('status')) {
            $order->status = $request->status;
        }

        $order->save();

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Order berhasil dihapus']);
    }

    // Menampilkan semua transaksi (order)
    public function transactions()
    {
        return response()->json(Order::all());
    }
}
