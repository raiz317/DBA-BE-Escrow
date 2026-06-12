<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// PERBAIKAN: Impor kedua model ini agar relasi terbaca dengan benar

class OrderMessage extends Model
{
    // Menentukan nama tabel kustom di database
    protected $table = 'order_messages';

    // Mengizinkan kolom diisi secara massal melalui method ::create()
    protected $fillable = [
        'order_id',
        'sender_id',
        'message',
    ];

    /**
     * Relasi ke model User (Pengirim Pesan)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relasi ke model Order (Pesanan Terkait)
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id'); // Menambahkan foreign key 'order_id' secara eksplisit
    }
}
