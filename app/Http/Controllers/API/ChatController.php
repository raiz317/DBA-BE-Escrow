<?php

namespace App\Http\Controllers\API;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $chats = Chat::with([
            'buyer',
            'seller',
            'order',
            'messages' => fn ($q) => $q->latest()->limit(1),
        ])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $chats,
        ]);
    }

    public function messages($chatId, Request $request)
    {
        $chat = Chat::findOrFail($chatId);

        if (
            $chat->buyer_id !== $request->user()->id &&
            $chat->seller_id !== $request->user()->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $messages = Message::with('sender')
            ->where('chat_id', $chatId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function send(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $chat = Chat::findOrFail($chatId);

        if (
            $chat->buyer_id !== $request->user()->id &&
            $chat->seller_id !== $request->user()->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }
}
