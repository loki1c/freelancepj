<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class RegistrationRequest extends FormRequest
{
    /*
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'login' => 'required|unique:users,login|max:255',  // Логин теперь обязателен
            'firstname' => 'required|string|max:255',          // Имя
            'lastname' => 'required|string|max:255',           // Фамилия
            'phone' => 'nullable|numeric',                      // Телефон
            'city' => 'nullable|string|max:255',                // Город
            'email' => 'required|email|string|max:255|unique:users,email',  // Электронная почта
            'password' => 'required|min:8|max:255|confirmed',  // Пароль
        ];
    }
}

