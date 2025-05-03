<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static findOrFail($id)
 */
class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'description', 'price', 'status', 'category', 'deadline', 'user_id', 'executor_id'
    ];
    public function toArray()
    {
        $array = parent::toArray();
        $array['file'] = $this->file; // явное добавление поля 'file'
        return $array;
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}

