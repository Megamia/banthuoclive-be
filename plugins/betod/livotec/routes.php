<?php

use Betod\Livotec\Controllers\GhnController;
use Betod\Livotec\Models\IngredientsAndInstructions;
use Betod\Livotec\Models\Orders;
use Betod\Livotec\Models\Product;
use Betod\Livotec\Models\Category;
use Betod\Livotec\Controllers\PayPalController;
use Betod\Livotec\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::group(['prefix' => 'apiProduct'], function () {
    // Route::get("allProduct", function () {
    //     return Cache::remember('all_products', 3600, function () {
    //         $allProduct = Product::with(['gallery', 'image', 'category.parent', 'post', 'ingredientsAndInstructions'])->get();
    //         return response()->json([
    //             'allProduct' => $allProduct,
    //             'status' => $allProduct->isNotEmpty() ? 1 : 0
    //         ]);
    //     });
    // });
    function imagePathToRelative($diskName)
    {
        return substr($diskName, 0, 3) . '/' . substr($diskName, 3, 3) . '/' . substr($diskName, 6, 3) . '/' . $diskName;
    }

    Route::get("allProduct", function () {
        $allProduct = Product::with(['gallery', 'image', 'category.parent', 'post', 'ingredientsAndInstructions'])->get();

        $allProduct->transform(function ($product) {
            if ($product->image) {
                $product->image->full_url = URL::to('storage/uploads/public/' . imagePathToRelative($product->image->disk_name));
            }

            if ($product->gallery) {
                foreach ($product->gallery as $img) {
                    $img->full_url = URL::to('storage/uploads/public/' . imagePathToRelative($img->disk_name));
                }
            }

            return $product;
        });

        return response()->json([
            'allProduct' => $allProduct,
            'status' => $allProduct->isNotEmpty() ? 1 : 0
        ]);
    });

    Route::get('navProducts/{slug}', function ($slug) {
        $category = Category::with(['children'])->where('slug', $slug)->first();

        if (!$category) {
            return response()->json(['status' => 0, 'message' => 'No data']);
        }

        $categoryIds = $category->getAllChildrenAndSelf()->pluck('id');
        $products = Product::with(['image', 'category'])
            ->whereIn('category_id', $categoryIds)
            ->get();

        return response()->json([
            'category' => $category,
            'products' => $products,
            'status' => 1
        ]);
    });

    Route::get('product/{slug}', function ($slug) {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return Product::with(['gallery', 'image', 'category.parent', 'post'])
            ->where('category_id', $category->id)
            ->get();
    });

    Route::get('detailProduct/{slug}', function ($slug) {
        $product = Product::with(['gallery', 'image', 'category.parent', 'post'])->where('slug', $slug)->first();
        return $product ?: response()->json(['message' => 'Product not found'], 404);
    });
});

Route::group(['prefix' => 'apiCategory'], function () {
    Route::get('allCategory', function () {
        $allCategory = Category::with(['image', 'filters', 'children'])->get();
        return response()->json([
            'allCategory' => $allCategory,
            'status' => $allCategory->isNotEmpty() ? 1 : 0
        ]);
    });

    Route::get('allCategoryParent', function () {
        $allCategoryParent = Category::whereNull('parent_id')->get();
        return response()->json([
            'allCategoryParent' => $allCategoryParent,
            'status' => $allCategoryParent->isNotEmpty() ? 1 : 0
        ]);
    });
});

Route::group(['prefix' => 'apiOrder'], function () {
    Route::post('createOrder', 'Betod\Livotec\Controllers\OrderController@createOrder');

    Route::get('order/{order_code}', function ($order_code) {
        return Orders::with('orderdetail.product')->where('order_code', $order_code)->first();
    });
    Route::get('allDataOrder/{id}', function ($id) {
        return Orders::with('orderdetail.product')->where('user_id', $id)->get();
    });
    Route::post("/updateStatus/{order_code}", function ($order_code) {
        $order = Orders::where('order_code', $order_code)->first();

        if ($order) {
            $order->update(['status_id' => "2"]);
            return response()->json([
                "message" => "Cập nhật trạng thái thành công",
                "data" => $order
            ], 200);
        } else {
            return response()->json(["message" => "Không tìm thấy đơn hàng"], 404);
        }
    });
});

Route::group(['prefix' => 'apiPaypal'], function () {
    Route::post('createOrder', [PayPalController::class, 'createOrder']);
    Route::post('captureOrder', [PayPalController::class, 'captureOrder']);
});

Route::group(['prefix' => 'apiImport'], function () {
    Route::post('import', [\Betod\Livotec\Controllers\Product\ImportCSV::class, 'importCsv']);
    Route::post('import-category', [\Betod\Livotec\Controllers\Category\ImportCSV::class, 'importCsv']);
});

Route::group(['prefix' => 'apiData'], function () {
    Route::get('data', [\Betod\Livotec\Controllers\Revenue\RevenueChart::class, 'chart']);
});

Route::group(['prefix' => 'apiGHN'], function () {
    Route::get('/ghn/provinces', [GhnController::class, 'getProvinces']);
    Route::get('/ghn/districts/{province_id}', [GhnController::class, 'getDistricts']);
    Route::get('/ghn/wards/{district_id}', [GhnController::class, 'getWards']);
});
// routes/web.php
Route::post('/upload', [UploadController::class, 'upload']);


