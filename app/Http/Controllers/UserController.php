<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $data = $request->all();  // Mengambil seluruh data yang dikirimkan

        // Pastikan data yang diterima adalah array
        if (is_array($data)) {
            foreach ($data as $item) {
                User::create([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'phone' => $item['phone'],
                    'identity_number' => $item['identity_number'],
                ]);
            }
        } else {
            // Jika bukan array, return error
            return response()->json(['error' => 'Data tidak valid'], 400);
        }

        return response()->json(['message' => 'Data berhasil disimpan!'], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string',
            'identity_number' => 'sometimes|string|unique:users,identity_number,' . $id,
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('identity_number')) {
            $user->identity_number = $request->identity_number;
        }

        $user->save();

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
