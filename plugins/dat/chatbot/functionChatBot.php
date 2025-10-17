<?php

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use Betod\Livotec\Models\Category;
use Betod\Livotec\Models\Product;
use Illuminate\Support\Facades\Log;

function handleProductFind($message)
{
    $keyword = trim($message);

    if ($keyword === '') {
        return response()->json([
            'reply' => 'Vui lòng nhập từ khóa sản phẩm cần tìm kiếm.',
            'products' => []
        ]);
    }

    $cacheKey = 'find_product_' . strtolower(preg_replace('/\s+/', '_', $keyword));

    $products = Cache::remember($cacheKey, 600, function () use ($keyword) {
        $query = Product::query()
            ->where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('slug', 'LIKE', "%{$keyword}%")
            ->orWhere('description', 'LIKE', "%{$keyword}%");

        $category = Category::where('name', 'LIKE', "%{$keyword}%")->first();
        if ($category) {
            $query->orWhere('category_id', $category->id);
        }

        return $query->orderBy('price')->get();
    });

    if ($products->isNotEmpty()) {
        return response()->json([
            'reply' => "Tìm thấy {$products->count()} sản phẩm phù hợp với từ khóa “{$keyword}”:",
            'products' => $products->map(function ($product, $index) {
                return [
                    'index' => $index + 1,
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price ?? 0,
                    'stock' => $product->stock,
                    'slug' => $product->slug,
                ];
            })->values(),
        ]);
    }

    return response()->json([
        'reply' => "Xin lỗi, không tìm thấy sản phẩm nào phù hợp với từ khóa “{$keyword}”.",
        'products' => [],
    ]);
}

function callGeminiAPI($message)
{
    $gemini_api_key = env('GEMINI_API_KEY');
    if (!$gemini_api_key) {
        return response()->json(['reply' => 'Chưa cấu hình API AI.'], 500);
    }

    try {
        $client = new Client();
        $res = $client->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$gemini_api_key", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['contents' => [['parts' => [['text' => $message]]]]]
        ]);

        $responseData = json_decode($res->getBody(), true);
        $reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi chưa có câu trả lời cho câu hỏi này.';

        $keywordMap = [
            'Cơ xương khớp' => ['xương', 'khớp', 'đau khớp', 'thoái hóa', 'mỏi khớp', 'gout', 'viêm khớp'],
            'Vitamin & Khoáng chất' => ['vitamin', 'khoáng', 'thiếu chất', 'mệt', 'bổ sung', 'tăng đề kháng'],
            'Dinh dưỡng' => ['ăn uống', 'dinh dưỡng', 'tăng cân', 'giảm cân', 'sữa', 'protein'],
            'Dược mỹ phẩm' => ['kem dưỡng', 'mỹ phẩm', 'serum', 'chống nắng', 'trị mụn'],
            'Chăm sóc da mặt' => ['da mặt', 'dưỡng da', 'mụn', 'lão hóa'],
            'Chăm sóc cá nhân' => ['vệ sinh', 'khử mùi', 'tắm gội', 'chăm sóc cá nhân'],
            'Bao cao su' => ['bao cao su', 'an toàn tình dục', 'quan hệ'],
            'Thiết bị y tế' => ['đo huyết áp', 'nhiệt kế', 'máy đo đường', 'thiết bị y tế'],
            'Cải thiện tăng cường chức năng' => [
                'tăng cường sức khỏe',
                'bổ thận',
                'sinh lý',
                'tăng lực',
                'tuần hoàn não',
                'hoạt huyết',
                'chóng mặt',
                'hoa mắt',
                'mất ngủ',
                'stress'
            ]
        ];

        $messageLower = mb_strtolower($message);
        $bestMatch = null;
        $bestPos = PHP_INT_MAX;
        $bestLength = 0;

        foreach ($keywordMap as $catName => $keywords) {
            foreach ($keywords as $keyword) {
                $pos = mb_stripos($messageLower, $keyword);
                if ($pos !== false) {
                    $len = mb_strlen($keyword);
                    if ($len > $bestLength || ($len == $bestLength && $pos < $bestPos)) {
                        $bestMatch = $catName;
                        $bestPos = $pos;
                        $bestLength = $len;
                    }
                }
            }
        }

        $productSuggestions = [];
        $categoryFound = false;

        if ($bestMatch) {
            $category = Category::where('name', 'LIKE', "%{$bestMatch}%")->first();
            if ($category) {
                $categoryFound = true;
                $products = Product::where('category_id', $category->id)
                    ->orderBy('price')
                    ->take(3)
                    ->get();

                foreach ($products as $p) {
                    $productSuggestions[] = [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'stock' => $p->stock,
                        'slug' => $p->slug
                    ];
                }

                if ($products->isNotEmpty()) {
                    $reply .= "\n\n💊 Một số sản phẩm bạn có thể quan tâm thuộc nhóm {$bestMatch}:";
                }
            }
        }

        if (!$categoryFound) {
            $keyword = explode(' ', trim($messageLower))[0];
            $products = Product::where('name', 'LIKE', "%{$keyword}%")
                ->orderBy('price')
                ->take(3)
                ->get();

            if ($products->isNotEmpty()) {
                $reply .= "\n\n💊 Tôi đã tìm thấy một số sản phẩm có liên quan đến từ khóa bạn nói:";
                foreach ($products as $p) {
                    $productSuggestions[] = [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'stock' => $p->stock,
                        'slug' => $p->slug
                    ];
                }
            }
        }

        return response()->json([
            'reply' => $reply,
            'products' => $productSuggestions
        ]);

    } catch (\Exception $e) {
        \Log::error('Lỗi khi gọi Gemini hoặc gợi ý sản phẩm: ' . $e->getMessage());
        return response()->json(['reply' => 'AI đang gặp sự cố. Vui lòng thử lại sau.'], 500);
    }
}