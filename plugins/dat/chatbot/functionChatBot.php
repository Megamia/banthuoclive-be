<?php

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use Betod\Livotec\Models\Category;
use Betod\Livotec\Models\Product;
use Illuminate\Support\Facades\Log;

function handleProductFind($message)
{
    $keyword = trim(str_replace('tìm kiếm', '', $message));
    if (empty($keyword)) {
        return response()->json(['reply' => 'Vui lòng nhập từ khóa sản phẩm cần tìm kiếm.']);
    }

    $products = Cache::remember("find_product_$keyword", 600, function () use ($keyword) {
        $category = Category::where('name', 'LIKE', "%$keyword%")->first();
        return $category ? Product::where('category_id', $category->id)->get() : Product::where('name', 'LIKE', "%$keyword%")->get();
    });

    if ($products->isNotEmpty()) {
        $products=$products->sortBy('price');
        $reply = "🔍 Danh sách sản phẩm phù hợp:\n";
        foreach ($products as $index => $product) {
            $reply .= sprintf(
                "%d. %s\n💰 Giá: %s VNĐ\n📦 Còn: %d cái.\n===========================\n",
                $index + 1,
                $product->name,
                number_format($product->price ?? 0),
                $product->stock
            );
        }
        return response()->json(['reply' => $reply]);
    }

    return response()->json(['reply' => 'Xin lỗi, không tìm thấy sản phẩm phù hợp.']);
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
        $reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi không có câu trả lời.';

        return response()->json(['reply' => $reply]);
    } catch (\Exception $e) {
        Log::error('Lỗi khi gọi API Gemini: ' . $e->getMessage());
        return response()->json(['reply' => 'AI đang gặp sự cố. Vui lòng thử lại sau.'], 500);
    }
}
