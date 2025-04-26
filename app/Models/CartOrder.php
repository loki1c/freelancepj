<?php
// app/Models/CartOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order; // не забудь импортировать Order

class CartOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'user_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
