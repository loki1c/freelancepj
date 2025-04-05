<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderChat;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Order\OrderChatRequest;
use Exception;

class ChatController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $check = checkPermission(permission: 'order_chat_show');
        if ($check['success'] === false) {
            return response()->json([$check], status: 400);
        }

        $result = OrderChat::query()->find($id);
        if ($result) {
            return response()->json(['success' => true, 'data' => $result->toArray()]);
        } else {
            return response()->json(['success' => false, 'message' => __('icp.not_found_chat')], status: 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderChatRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $chat = OrderChat::create($validated);

            return response()->json(['success' => true, 'data' => $chat], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('icp.chat_create_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

