<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'local',
        'endereco',
        'cidade',
        'estilo',
        'musica_ao_vivo',
        'horario',
        'ingresso',
        'latitude',
        'longitude',
        'data',
        'hora',
        'descricao',
        'cpf',
    ];

    public function curtidas()
    {
        return $this->hasMany(CurtirEvento::class);
    }

    public function usuariosQueCurtiram()
    {
        return $this->belongsToMany(User::class, 'curtir_eventos')->withTimestamps();
    }

    public function checkins()
    {
        return $this->hasMany(CheckinEvento::class);
    }
}
