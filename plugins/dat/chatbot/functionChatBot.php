<?php

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use Betod\Livotec\Models\Category;
use Betod\Livotec\Models\Product;
use Illuminate\Support\Facades\Log;

function extractProductKeyword(string $message): string
{
    $message = mb_strtolower($message);

    $stopWords = [
        'tìm',
        'mua',
        'cho tôi',
        'tư vấn',
        'cần',
        'bán',
        'loại',
        'về',
        'giúp',
    ];

    foreach ($stopWords as $word) {
        $message = str_replace($word, '', $message);
    }

    return trim(preg_replace('/\s+/', ' ', $message));
}

function handleProductFind(string $message)
{
    $keyword = extractProductKeyword($message);

    if ($keyword === '') {
        return response()->json(['products' => []]);
    }

    $products = Product::query()
        ->where('name', 'LIKE', "%{$keyword}%")
        ->limit(5)
        ->get();

    if ($products->isEmpty()) {
        return response()->json(['products' => []]);
    }

    Session::put('last_product_keyword', $keyword);
    Session::put('last_products', $products->pluck('id')->toArray());

    return response()->json([
        'reply' => 'Mình tìm thấy các sản phẩm phù hợp:',
        'products' => $products
    ]);
}




function callOpenAPI($message)
{
    try {
        $client = new Client();

        $res = $client->post(
            'https://openrouter.ai/api/v1/chat/completions',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'openai/gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Bạn là trợ lý tư vấn, KHÔNG đoán sản phẩm nếu chưa có dữ liệu.'
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
        $reply = trim($data['choices'][0]['message']['content'] ?? '');

        return response()->json([
            'reply' => $reply,
            'products' => []
        ]);

    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json([
            'reply' => 'AI đang bận, vui lòng thử lại sau.',
            'products' => []
        ], 500);
    }
}

function isBuyIntent(string $message): bool
{
    $message = mb_strtolower($message);

    $keywords = [
        'mua',
        'đặt',
        'lấy',
        'order',
        'mua ngay',
        'đặt hàng'
    ];

    foreach ($keywords as $word) {
        if (str_contains($message, $word)) {
            return true;
        }
    }

    return false;
}



function handleChatMessage(string $message)
{
    $dbResult = handleProductFind($message);

    $data = $dbResult->getData(true);

    if (!empty($data['products'])) {
        return $dbResult;
    }

    return callOpenAPI($message);
}
