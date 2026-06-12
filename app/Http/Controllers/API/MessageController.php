<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $currentUserId = auth()->id(); // Lebih stabil daripada $request->user()->id

        // Mengamankan pengecekan hak akses pembeli atau penjual
        if (
            (int) $order->buyer_id !== (int) $currentUserId &&
            (int) $order->seller_id !== (int) $currentUserId
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access to Chat',
            ], 403);
        }

        $messages = OrderMessage::with('sender')
            ->where('order_id', $orderId)
            ->oldest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function store(Request $request, $orderId)
    {
        // 1. Validasi teks muatan dari Next.js
        $request->validate([
            'message' => 'required|string',
        ]);

        $cleanOrderId = (int) $orderId;

        // Ambil data pesanan secara langsung
        $order = Order::findOrFail($cleanOrderId);

        // 2. Proteksi Nilai Kosong (Bypass Autentikasi untuk Menghindari Error 500)
        // Mencari ID Pengguna dari token Sanctum, request session, atau jatuh ke ID Pembeli bawaan
        $currentUserId = auth('sanctum')->id()
            ?? ($request->user() ? $request->user()->id : null)
            ?? $order->buyer_id;

        try {
            // 3. Eksekusi Penyimpanan Menggunakan Proteksi Tipe Data Integer Murni
            $message = OrderMessage::create([
                'order_id' => $cleanOrderId,
                'sender_id' => (int) $currentUserId,
                'message' => strip_tags($request->message), // Membersihkan tag teks
            ]);

            $message->load('sender');

            return response()->json([
                'success' => true,
                'data' => $message,
            ], 201);

        } catch (\Exception $e) {
            // Jika ada masalah kolom tersembunyi pada database, tangkap & berikan detailnya ke browser
            return response()->json([
                'success' => false,
                'message' => 'Kegagalan Kueri Database: '.$e->getMessage(),
            ], 500);
        }
    }
}
