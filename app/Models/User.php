<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'termo',
        'imagem',
        'localizacao',
        'aniversario',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function curtidas()
    {
        return $this->hasMany(CurtirEvento::class);
    }

    public function eventosCurtidos()
    {
        return $this->belongsToMany(Evento::class, 'curtir_eventos')->withTimestamps();
    }

    public function checkins()
    {
        return $this->belongsToMany(Evento::class, 'checkin_eventos')->withTimestamps();
    }

    public function amigos()
    {
        return $this->hasMany(Amigo::class, 'user_id');
    }
}
