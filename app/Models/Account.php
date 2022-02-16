<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'card_number',
        'password',
    ];

    protected $hidden = [
        'password',
    ];


    public function can_transfer_money($amount) {
        return $this->total >= $amount;
    }
}
