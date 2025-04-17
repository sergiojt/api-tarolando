<?php

namespace App\Http\Controllers;

use App\Models\CurtirEvento;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        return Evento::all();
    }

    public function store(Request $request)
    {
        return Evento::create($request->all());
    }

    public function show(Evento $evento)
    {
        return $evento;
    }

    public function update(Request $request, Evento $evento)
    {
        $evento->update($request->all());
        return $evento;
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();
        return response()->noContent();
    }

    public function toggleCurtir(Request $request, $id)
    {
        $curtida = CurtirEvento::where('user_id', $request->user_id)
            ->where('evento_id', $id)
            ->first();

        if ($curtida) {
            $curtida->delete();
            return response()->json(['message' => 'Evento descurtido.']);
        } else {
            CurtirEvento::create([
                'user_id' =>  $request->user_id,
                'evento_id' => $id,
            ]);
            return response()->json(['message' => 'Evento curtido!']);
        }
    }
}
