<?php

namespace App\Http\Controllers;

use App\Models\CheckinEvento;
use App\Models\CurtirEvento;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Goutte\Client;
use Carbon\Carbon;

class EventoController extends Controller
{
    public function importSympla()
    {
        $client = new Client();
        $url = 'https://www.sympla.com.br/eventos?s=recife&page=2';

        $crawler = $client->request('GET', $url);

        $crawler->filter('a.sympla-card')->each(function ($node) {
            $title = $node->filter('h3')->text('');
            $link = $node->filter('a')->attr('href') ?? null;
            $dataHora = $node->filter('.sc-eDLJxc')->text('');
            $local = $node->filter('.sc-gLDzan')->text('');
            echo $title.'<br>';
        });
    }

    function importarEventosDoJson($dataDoUltimoDomingo)
    {
        set_time_limit(0); // Sem limite de tempo
        
        $jsonPath = public_path('eventos.json');

        if (!File::exists($jsonPath)) {
            return response()->json(['error' => 'Arquivo não encontrado'], 404);
        }

        $eventos = json_decode(File::get($jsonPath), true);

        $diasDaSemana = [
            'segunda-feira' => 1,
            'terça-feira' => 2,
            'quarta-feira' => 3,
            'quinta-feira' => 4,
            'sexta-feira' => 5,
            'sábado' => 6,
            'domingo' => 0,
        ];

        $domingoBase = Carbon::parse($dataDoUltimoDomingo)->startOfDay();

        foreach ($eventos as $evento) {
            foreach ($evento['horario'] as $linha) {
                [$diaSemanaStr, $horario] = explode(':', $linha, 2);

                $diaSemanaStr = trim($diaSemanaStr);
                $horario = trim($horario);

                if (str_contains(strtolower($horario), 'fechado')) {
                    continue; // Ignorar dias fechados
                }

                $numeroDia = $diasDaSemana[$diaSemanaStr];
                $data = $domingoBase->copy()->addDays($numeroDia);

                $existe = Evento::where('local', $evento['nome'])
                                ->where('data', $data)
                                ->exists();

                if (!$existe) {
                    Evento::create([
                        'nome' => $evento['nome'],
                        'descricao' => $evento['descricao'] ?? null,
                        'endereco' => $evento['endereco'],
                        'cidade' => 'Recife',
                        'local' => $evento['nome'], // Considerando nome como local
                        'estilo' => 'Não informado',
                        'musica_ao_vivo' => false,
                        'horario' => $horario,
                        'ingresso' => 'Não informado',
                        'latitude' => $evento['latitude'],
                        'longitude' => $evento['longitude'],
                        'data' => $data->toDateString(),
                    ]);
                }
            }
        }
    }

    public function checkin(Request $request, Evento $evento)
    {
        // Cria o check-in
        $checkin = CheckinEvento::create([
            'user_id' => $request->user_id, // ou $request->user_id se não tiver auth
            'evento_id' => $evento->id,
            'comentario' => $request->input('comentario'),
        ]);
    
        return response()->json([
            'message' => 'Check-in realizado com sucesso!',
            'data' => $checkin,
        ], 201);
    }

    public function importarEventos()
    {
        $jsonPath = public_path('eventos.json');

        if (!File::exists($jsonPath)) {
            return response()->json(['error' => 'Arquivo não encontrado'], 404);
        }

        $dados = json_decode(File::get($jsonPath), true);

        if (!isset($dados['eventos'])) {
            return response()->json(['error' => 'Formato inválido'], 400);
        }

        $inseridos = 0;
        foreach ($dados['eventos'] as $evento) {
            $data = $dados['data'];

            // Verifica se já existe evento com mesmo local + data
            $existe = Evento::where('local', $evento['local'])
                ->where('data', $data)
                ->exists();

            if (!$existe) {
                Evento::create([
                    'nome' => $evento['nome'],
                    'local' => $evento['local'],
                    'endereco' => $evento['endereco'],
                    'cidade' => $evento['cidade'],
                    'estilo' => $evento['estilo'],
                    'musica_ao_vivo' => $evento['musica_ao_vivo'],
                    'horario' => $evento['horario'],
                    'ingresso' => $evento['ingresso'],
                    'latitude' => $evento['latitude'],
                    'longitude' => $evento['longitude'],
                    'data' => $data
                ]);
                $inseridos++;
            }
        }

        return response()->json(['message' => "$inseridos eventos importados com sucesso."]);
    }
    public function index(Request $request)
    {
        $query = Evento::query();
    
        // Apenas eventos a partir de hoje
        if(!isset($request->endereco) && isset($request->cidade) && isset($request->estilo)){
            $query->where('data', '=', Carbon::today());
        }else{
            $query->where('data', '>=', Carbon::today());
        }
    
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

    private static function validaCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    public function store(Request $request)
    {
        if (!isset($request->cpf) && !self::validaCPF($request->cpf)) {
            return response()->json(['message' => 'CPF inválido.'], 400);
        }

        return Evento::create($request->all());
    }

    public function show(Evento $evento)
    {
        return $evento;
    }

    public function update(Request $request, Evento $evento)
    {
        if (!isset($request->cpf) && !self::validaCPF($request->cpf)) {
            return response()->json(['message' => 'CPF inválido.'], 400);
        }

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
