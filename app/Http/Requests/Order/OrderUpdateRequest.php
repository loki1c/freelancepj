<?php
declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin(); // или твоя логика проверки админа
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
        '   description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:1',
        ];
    }
}
