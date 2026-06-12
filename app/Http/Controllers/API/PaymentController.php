<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayOrderRequest;
use App\Models\EscrowLog;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function pay(PayOrderRequest $request, $orderId): JsonResponse
    {
        try {

            $order = Order::findOrFail($orderId);

            if ($order->buyer_id !== $request->user()->id) {

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            if ($order->status !== 'pending_payment') {

                return response()->json([
                    'success' => false,
                    'message' => 'Order harus pending payment',
                ], 400);
            }

            // MIDTRANS CONFIG
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            Config::$appendNotifUrl =
                env('APP_URL').'/api/midtrans/callback';

            \Log::info('MIDTRANS NOTIF URL', [
                'url' => Config::$appendNotifUrl,
            ]);

            // CREATE PAYMENT
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // SNAP PARAMS
            $params = [
                'transaction_details' => [
                    'order_id' => 'ORDER-'.$payment->id,
                    'gross_amount' => (int) $order->total_price,
                ],

                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],

                // QRIS
                // 'enabled_payments' => ['qris'],
            ];

            // dd(config('midtrans.server_key'));
            // CREATE SNAP TOKEN
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'data' => [
                    'snap_token' => $snapToken,
                    'payment_id' => $payment->id,
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    // public function pay(
    //     PayOrderRequest $request,
    //     $orderId
    // ): JsonResponse {

    //     try {

    //         $order = Order::findOrFail($orderId);

    //         if ($order->buyer_id !== $request->user()->id) {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Unauthorized',
    //             ], 403);
    //         }

    //         if (
    //             $order->status !== 'pending' &&
    //             $order->status !== 'pending_payment'
    //         ) {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Order tidak bisa dibayar',
    //             ], 400);
    //         }

    //         // MIDTRANS CONFIG
    //         Config::$serverKey = config('midtrans.server_key');
    //         Config::$isProduction = false;
    //         Config::$isSanitized = true;
    //         Config::$is3ds = true;

    //         // Cek payment pending sebelumnya
    //         $existingPayment = Payment::where(
    //             'order_id',
    //             $order->id
    //         )
    //             ->where('status', 'pending')
    //             ->first();

    //         if ($existingPayment) {

    //             return response()->json([
    //                 'success' => true,
    //                 'data' => [
    //                     'snap_token' => $existingPayment->snap_token,
    //                 ],
    //             ]);
    //         }

    //         // CREATE PAYMENT
    //         $payment = Payment::create([
    //             'order_id' => $order->id,
    //             'amount' => $order->total_price,
    //             'method' => $request->payment_method,
    //             'status' => 'pending',
    //         ]);

    //         $params = [

    //             'transaction_details' => [
    //                 'order_id' => 'ORDER-'.$payment->id,
    //                 'gross_amount' => (int) $order->total_price,
    //             ],

    //             'customer_details' => [
    //                 'first_name' => $request->user()->name,
    //                 'email' => $request->user()->email,
    //             ],

    //             'enabled_payments' => [
    //                 'qris',
    //             ],
    //         ];

    //         $snapToken = Snap::getSnapToken($params);

    //         $payment->update([
    //             'snap_token' => $snapToken,
    //         ]);

    //         $order->update([
    //             'status' => 'pending_payment',
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'snap_token' => $snapToken,
    //             ],
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function pay(
    //     PayOrderRequest $request,
    //     $orderId
    // ): JsonResponse {
    //     try {

    //         $order = Order::findOrFail($orderId);

    //         if (
    //             $order->buyer_id !==
    //             $request->user()->id
    //         ) {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Unauthorized',
    //             ], 403);
    //         }

    //         if (
    //             $order->status !==
    //             'pending_payment'
    //         ) {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Order harus pending payment',
    //             ], 400);
    //         }

    //         $payment = Payment::create([
    //             'order_id' => $order->id,
    //             'amount' => $order->total_price,
    //             'method' => $request->payment_method,
    //             'status' => 'pending',
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [

    //                 'payment_url' => env('FRONTEND_URL')
    //                     .'/dummy-payment/'
    //                     .$payment->id,
    //             ],
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /*
    order_id dari midtrans:
    ORDER-1
    */

    // $paymentId = str_replace('ORDER-', '', $request->order_id);

    // $payment = Payment::find($paymentId);

    // public function midtransCallback(Request $request)
    // {
    //     \Log::info('MIDTRANS CALLBACK', [
    //         'payload' => $request->all(),
    //     ]);

    //     try {

    //         $serverKey = config('midtrans.server_key');

    //         $hashed = hash(
    //             'sha512',
    //             $request->order_id.
    //             $request->status_code.
    //             $request->gross_amount.
    //             $serverKey
    //         );

    //         if ($hashed !== $request->signature_key) {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid signature',
    //             ], 403);
    //         }

    //         $paymentId = str_replace('ORDER-', '', $request->order_id);

    //         \Log::info('PAYMENT ID DARI MIDTRANS', [
    //             'payment_id' => $paymentId,
    //         ]);

    //         $payment = Payment::find($paymentId);

    //         \Log::info('HASIL PAYMENT::FIND()', [
    //             'payment_found' => $payment ? true : false,
    //         ]);

    //         if (! $payment) {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Payment tidak ditemukan',
    //             ], 404);
    //         }

    //         if ($payment->status === 'success') {

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Pembayaran sudah diproses',
    //             ]);
    //         }

    //         if (
    //             $request->transaction_status === 'settlement' ||
    //             $request->transaction_status === 'capture'
    //         ) {
    //             \Log::info('MASUK KE BLOK SUCCESS');

    //             $payment->update([
    //                 'status' => 'success',
    //                 'paid_at' => now(),
    //             ]);

    //             $order = Order::find($payment->order_id);

    //             \Log::info('HASIL ORDER::FIND()', [
    //                 'order_found' => $order ? true : false,
    //                 'order_id' => $payment->order_id,
    //             ]);

    //             $order->update([
    //                 'status' => 'processing',
    //             ]);
    //             \Log::info('ORDER BERHASIL DIUPDATE KE PROCESSING');

    //             EscrowLog::create([
    //                 'order_id' => $order->id,
    //                 'actor_id' => $order->buyer_id,
    //                 'action' => 'payment_success',
    //                 'amount' => $payment->amount,
    //                 'note' => 'Pembayaran Midtrans berhasil',
    //             ]);
    //         }

    //         return response()->json([
    //             'success' => true,
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //         ], 500);
    //     }
    // }

    public function midtransCallback(Request $request)
    {
        \Log::info('MIDTRANS CALLBACK', [
            'payload' => $request->all(),
        ]);

        try {

            $serverKey = config('midtrans.server_key');

            $hashed = hash(
                'sha512',
                $request->order_id.
                $request->status_code.
                $request->gross_amount.
                $serverKey
            );

            if ($hashed !== $request->signature_key) {

                \Log::error('SIGNATURE TIDAK VALID');

                return response()->json([
                    'success' => false,
                ], 403);
            }

            $paymentId = str_replace(
                'ORDER-',
                '',
                $request->order_id
            );

            \Log::info('PAYMENT ID', [
                'payment_id' => $paymentId,
            ]);

            $payment = Payment::find($paymentId);

            \Log::info('PAYMENT FOUND', [
                'found' => $payment ? true : false,
            ]);

            if (! $payment) {

                \Log::error('PAYMENT TIDAK DITEMUKAN');

                return response()->json([
                    'success' => false,
                ], 404);
            }

            if (
                $request->transaction_status === 'settlement' ||
                $request->transaction_status === 'capture'
            ) {

                \Log::info('MASUK BLOK SUCCESS');

                $payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                ]);

                $order = Order::find($payment->order_id);

                \Log::info('ORDER FOUND', [
                    'found' => $order ? true : false,
                    'order_id' => $payment->order_id,
                ]);

                $order->update([
                    'status' => 'processing',
                ]);

                \Log::info('ORDER UPDATED');
            }

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {

            \Log::error('CALLBACK ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    // public function completePayment($paymentId)
    // {
    //     try {

    //         $payment = Payment::findOrFail($paymentId);

    //         // cegah double payment
    //         if ($payment->status === 'success') {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Sudah dibayar',
    //             ], 400);
    //         }

    //         // update payment
    //         $payment->update([
    //             'status' => 'success',
    //             'paid_at' => now(),
    //         ]);

    //         // update order
    //         $order = Order::findOrFail($payment->order_id);

    //         // hanya update jika belum paid
    //         if ($order->status === 'pending_payment') {

    //             $order->update([
    //                 'status' => 'paid',
    //             ]);
    //         }

    //         // escrow log
    //         EscrowLog::create([
    //             'order_id' => $order->id,
    //             'actor_id' => $order->buyer_id,
    //             'action' => 'payment_success',
    //             'amount' => $payment->amount,
    //             'note' => 'Dummy QR payment success',
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pembayaran berhasil',
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
