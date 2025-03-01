<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static findOrFail($id)
 */
class Order extends Model
{
    protected $fillable = ['user_id', 'executor_id', 'title', 'description', 'price', 'status'];

    public static function where(string $string, string $string1)
    {
    }

}

