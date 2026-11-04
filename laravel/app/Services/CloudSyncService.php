<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudSyncService
{
    /**
     * Sincroniza o catálogo de produtos e categorias a partir da nuvem Hostinger
     */
    public static function syncCatalogFromCloud(): array
    {
        $cloudUrl = rtrim(config('services.cloud.url', 'https://akylanches.freedevs.tech'), '/');
        $endpoint = $cloudUrl . '/api/menu';

        $stats = [
            'success' => false,
            'categories' => 0,
            'products' => 0,
            'message' => ''
        ];

        try {
            $verifySsl = config('services.cloud.verify_ssl', env('REMOTE_API_VERIFY_SSL', false));
            $response = Http::withOptions(['verify' => $verifySsl])->timeout(15)->get($endpoint);

            if (!$response->successful()) {
                $stats['message'] = "Servidor remoto retornou HTTP {$response->status()}";
                return $stats;
            }

            $data = $response->json();
            $categories = $data['categories'] ?? [];

            if (empty($categories)) {
                $stats['message'] = 'Nenhuma categoria retornada pela nuvem.';
                return $stats;
            }

            $catCount = 0;
            $prodCount = 0;

            foreach ($categories as $catData) {
                $category = null;
                if (!empty($catData['slug'])) {
                    $category = Category::where('slug', $catData['slug'])->first();
                }
                if (!$category && !empty($catData['id'])) {
                    $category = Category::find($catData['id']);
                }
                if (!$category) {
                    $category = new Category();
                }

                $category->name = $catData['name'];
                $category->slug = $catData['slug'] ?? null;
                $category->sort_order = $catData['sort_order'] ?? 0;
                $category->is_active = $catData['is_active'] ?? true;
                $category->save();
                $catCount++;

                $products = $catData['products'] ?? [];
                foreach ($products as $prodData) {
                    $product = null;
                    if (!empty($prodData['id'])) {
                        $product = Product::find($prodData['id']);
                    }
                    if (!$product && !empty($prodData['name'])) {
                        $product = Product::where('name', $prodData['name'])->where('category_id', $category->id)->first();
                    }
                    if (!$product) {
                        $product = new Product();
                    }

                    $product->category_id = $category->id;
                    $product->name = $prodData['name'];
                    $product->description = $prodData['description'] ?? null;
                    $product->price = $prodData['price'] ?? 0;
                    $product->promotional_price = $prodData['promotional_price'] ?? null;
                    $product->is_promotion = $prodData['is_promotion'] ?? false;
                    $product->is_combo = $prodData['is_combo'] ?? false;
                    $product->is_available = $prodData['is_available'] ?? true;
                    $product->photo_path = $prodData['photo_path'] ?? null;
                    $product->save();
                    $prodCount++;
                }
            }

            $stats['success'] = true;
            $stats['categories'] = $catCount;
            $stats['products'] = $prodCount;
            $stats['message'] = "Sincronizado: {$catCount} categorias, {$prodCount} produtos.";

        } catch (\Throwable $e) {
            Log::warning('CloudSyncService::syncCatalogFromCloud falhou: ' . $e->getMessage());
            $stats['message'] = 'Erro de conexao: ' . $e->getMessage();
        }

        return $stats;
    }
}
