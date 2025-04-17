<?php

namespace App\Http\Controllers;

use App\Models\CurtirEvento;
use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $query = Evento::query();
    
        // Apenas eventos a partir de hoje
        $query->where('data', '>=', Carbon::today());
    
        // Filtro por endereço
        if ($request->filled('endereco')) {
            $query->where('endereco', 'like', '%' . $request->endereco . '%');
        }

        if ($request->filled('cidade')) {
            $query->where('cidade', 'like', '%' . $request->cidade . '%');
        }
    
        // Filtro por estilo
        if ($request->filled('estilo')) {
            $query->where('estilo', 'like', '%' . $request->estilo . '%');
        }
    
        // Ordenar por data ASC
        $query->orderBy('data', 'asc');
    
        return $query->get();
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
