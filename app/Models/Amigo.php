<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Amigo extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amigo_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function amigo()
    {
        return $this->belongsTo(User::class, 'amigo_id');
    }

}
