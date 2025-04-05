<?php
declare(strict_types = 1);
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\RegistrationRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;

class RegisterController extends Controller
{
    /**
     * @param RegistrationRequest $request
     * @return JsonResponse
     */
    public function registration(RegistrationRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('Personal Access Token');

            return response()->json([
                'success' => true,
                'token' => $token->accessToken,
                'user' => $user,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
