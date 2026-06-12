<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::active();

            // Search by keyword
            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Filter by price range
            if ($request->has('min_price') && $request->has('max_price')) {
                $query->filterByPrice($request->min_price, $request->max_price);
            }

            $products = $query->paginate(12);

            return response()->json([
                'success' => true,
                'message' => 'Daftar produk berhasil diambil',
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => ['error' => $e->getMessage()],
            ], 500);
        }
    }

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
                'errors' => [],
            ], 404);
        }
    }

    // ... kode lainnya tetap ...

    public function store(CreateProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // dd([
            //     'validated' => $data,
            //     'request' => $request->all(),
            // ]);

            // PROSES FOTO: Jika ada upload file
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $data['image_url'] = asset('storage/'.$path);
            }

            $product = Product::create([
                'seller_id' => $request->user()->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'stock' => $data['stock'],
                'image_url' => $data['image_url'] ?? $request->image_url, // Gunakan upload file atau link URL
                'status' => $data['status'] ?? 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dibuat',
                'data' => new ProductResource($product),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->seller_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $data = $request->validated();

            // PROSES FOTO: Tambahkan logika ini agar update foto berfungsi
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $data['image_url'] = asset('storage/'.$path);
            }

            // Gunakan $data (hasil validasi + path gambar) bukan $request->only
            $product->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'data' => new ProductResource($product),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: '.$e->getMessage(),
            ], 404);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            // Check if user is the seller
            if ($product->seller_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus produk ini',
                    'errors' => [],
                ], 403);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
                'errors' => [],
            ], 404);
        }
    }

    public function sellerProducts(Request $request): JsonResponse
    {
        try {
            $query = Product::where('seller_id', $request->user()->id);

            // Search by keyword
            if ($request->has('search')) {
                $query->search($request->search);
            }

            $products = $query->paginate(12);

            return response()->json([
                'success' => true,
                'message' => 'Daftar produk seller berhasil diambil',
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => ['error' => $e->getMessage()],
            ], 500);
        }
    }
}
