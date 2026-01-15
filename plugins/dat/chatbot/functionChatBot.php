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

    $cacheKey = 'find_product_' . md5(mb_strtolower($keyword));

    $products = Cache::remember($cacheKey, 600, function () use ($keyword) {
        $query = Product::query()
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('slug', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });

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

function callOpenAPI($message)
{
    $openRouterKey = env('OPENROUTER_API_KEY');
    if (!$openRouterKey) {
        return response()->json(['reply' => 'Chưa cấu hình OpenAI API.'], 500);
    }

    try {
        $client = new Client();

        $prompt = $message;

        $res = $client->post(
            'https://openrouter.ai/api/v1/chat/completions',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',

                    'HTTP-Referer' => 'http://localhost',
                    'X-Title' => 'Laravel Chatbot',
                ],
                'json' => [
                    'model' => 'mistralai/mistral-7b-instruct:free',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Bạn là chatbot tư vấn sản phẩm y tế.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 200,
                ]
            ]
        );


        $data = json_decode($res->getBody(), true);
        $reply = $data['choices'][0]['message']['content'] ?? '';

        $reply = trim($reply);
        $reply = preg_replace('/^<s>|<\/s>$|^###/i', '', $reply);
        $reply = trim($reply);



        $keywordMap = [
            'Cơ xương khớp' => ['xương', 'khớp', 'đau khớp', 'thoái hóa', 'mỏi khớp', 'gout', 'viêm khớp'],
            'Vitamin & Khoáng chất' => ['vitamin', 'khoáng', 'thiếu chất', 'mệt', 'bổ sung', 'tăng đề kháng'],
            'Dinh dưỡng' => ['ăn uống', 'dinh dưỡng', 'tăng cân', 'giảm cân', 'sữa', 'protein'],
            'Dược mỹ phẩm' => ['kem dưỡng', 'mỹ phẩm', 'serum', 'chống nắng', 'trị mụn'],
            'Chăm sóc da mặt' => ['da mặt', 'dưỡng da', 'mụn', 'lão hóa'],
            'Chăm sóc cá nhân' => ['vệ sinh', 'khử mùi', 'tắm gội'],
            'Bao cao su' => ['bao cao su', 'an toàn tình dục'],
            'Thiết bị y tế' => ['đo huyết áp', 'nhiệt kế', 'máy đo đường'],
            'Cải thiện tăng cường chức năng' => [
                'bổ thận',
                'sinh lý',
                'tuần hoàn não',
                'mất ngủ',
                'stress'
            ]
        ];

        $messageLower = mb_strtolower($message);
        $bestMatch = null;
        $bestPos = PHP_INT_MAX;
        $bestLength = 0;

        foreach ($keywordMap as $catName => $keywords) {
            foreach ($keywords as $kw) {
                $pos = mb_stripos($messageLower, $kw);
                if ($pos !== false) {
                    $len = mb_strlen($kw);
                    if ($len > $bestLength || ($len === $bestLength && $pos < $bestPos)) {
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

                if ($products->isNotEmpty()) {
                    $reply .= "\n\n💊 Một số sản phẩm bạn có thể quan tâm ({$bestMatch}):";
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
        }

        return response()->json([
            'reply' => $reply,
            'products' => $productSuggestions
        ]);

    } catch (\Exception $e) {
        Log::error('Lỗi khi gọi OpenAI: ' . $e->getMessage());
        return response()->json(['reply' => 'AI đang gặp sự cố. Vui lòng thử lại sau.'], 500);
    }
}
