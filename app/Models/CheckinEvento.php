<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinEvento extends Model
{
    use HasFactory;

    protected $table = 'checkin_eventos';
    protected $fillable = ['user_id', 'evento_id', 'comentario'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
