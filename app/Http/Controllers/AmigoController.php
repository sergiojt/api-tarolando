<?php

namespace App\Http\Controllers;

use App\Models\Amigo;
use Illuminate\Http\Request;

class AmigoController extends Controller
{
    public function index()
    {
        return Amigo::with(['user', 'amigo'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amigo_id' => 'required|exists:users,id|different:user_id',
        ]);

        // Evita duplicação
        if (Amigo::where($data)->exists()) {
            return response()->json(['message' => 'Amizade já existe.'], 409);
        }

        $amizade = Amigo::create($request->all());
        $amizade = Amigo::create(["amigo_id" => $request->user_id, "user_id" => $request->amigo_id]);
        return response()->json($amizade, 201);
    }

    public function destroy(Request $request, $id)
    {
        $amizade = Amigo::where("amigo_id", $id)->where("user_id", $request->user_id)->first();
        $amizade = Amigo::where("amigo_id", $request->user_id)->where("user_id", $id)->first();
        $amizade->delete();

        return response()->json(null, 204);
    }
}
