<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurtirEvento extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'evento_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}
