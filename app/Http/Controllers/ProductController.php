<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function show($id): JsonResponse
    {
    try {
        $product = Product::active()->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil',
            'data' => new ProductResource($product),
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan',
        ], 404);
    }
    }
}