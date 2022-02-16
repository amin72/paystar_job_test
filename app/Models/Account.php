<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_number',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function user() {
        return $this->belongsTo(\App\Models\User::class);
    }
}
