<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'origin_id', 'destiny_id'];

    public function origin() {
        return $this->belongsTo(User::class, 'origin_id');
    }

    public function destiny() {
        return $this->belongsTo(User::class, 'destiny_id');
    }
}
