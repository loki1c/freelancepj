<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;  // Эта строка важна!

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_chat_id',
        'sender_id',
        'recipient_id',
        'content',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

}
