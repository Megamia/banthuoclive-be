<?php

use Betod\Livotec\Models\Product;
use Illuminate\Http\Request;
use Dat\Chatbot\Models\ChatBot;
use Illuminate\Support\Facades\Log;
require_once __DIR__ . '/functionChatBot.php';


Route::group(['prefix' => 'apiChatBot'], function () {
    Route::post('/learn', function (Request $request) {
        $message = trim($request->input('message', ''));

        if (empty($message)) {
            return response()->json(['reply' => 'Vui lòng nhập nội dung hợp lệ.'], 400);
        }

        $data = explode('|', $message);

        if (count($data) !== 2) {
            return response()->json(['reply' => 'Sai định dạng! Hãy gửi: "học: câu hỏi | câu trả lời".'], 400);
        }

        $question = strtolower(trim($data[0]));
        $answer = trim($data[1]);

        try {
            $existingChat = ChatBot::where('question', $question)
                ->where('answer', $answer)
                ->first();

            if ($existingChat) {
                return response()->json([
                    'reply' => 'Câu hỏi và câu trả lời này đã tồn tại.',
                    'status' => 0,
                    'message' => 'Đã tồn tại'
                ]);
            }

            ChatBot::create([
                'question' => $question,
                'answer' => $answer
            ]);

            return response()->json([
                'reply' => 'Tôi đã học câu mới: ' . implode(' | ', [$question, $answer]),
                'status' => 1,
                'message' => 'Tạo dữ liệu mới'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lưu dữ liệu chatbot: ' . $e->getMessage());
            return response()->json(['reply' => 'Đã có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    });

    Route::post('/chat', function (Request $request) {
        $message = trim($request->input('message', ''));

        if ($message === '') {
            return response()->json(['reply' => 'Vui lòng nhập tin nhắn hợp lệ.'], 400);
        }

        if (isBuyIntent($message)) {

            $lastKeyword = session('last_product_keyword');
            $lastProductIds = session('last_products');

            if ($lastKeyword && $lastProductIds) {
                $products = Product::whereIn('id', $lastProductIds)->get();

                if ($products->isNotEmpty()) {
                    return response()->json([
                        'reply' => 'Bạn muốn mua sản phẩm nào?',
                        'products' => $products
                    ]);
                }
            }

            return response()->json([
                'reply' => 'Bạn muốn mua sản phẩm nào? Vui lòng nhập tên sản phẩm.'
            ]);
        }

     
        $chatResponse = ChatBot::where('question', mb_strtolower($message))->first();
        if ($chatResponse) {
            return response()->json(['reply' => $chatResponse->answer]);
        }

        $productResponse = handleProductFind($message);
        $data = $productResponse->getData(true);

        if (!empty($data['products'])) {
            return $productResponse;
        }

        return callOpenAPI($message);
    });

    Route::get('/allChat', function () {
        $data = ChatBot::all();

        if ($data->isEmpty()) {
            return response()->json(['message' => "Không có dữ liệu"], 404);
        }

        return response()->json(['data' => $data]);
    });

});
