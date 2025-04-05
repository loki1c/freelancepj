<?php

declare(strict_types = 1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
        ];
    }
}
