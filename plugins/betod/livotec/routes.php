<?php

use Betod\Livotec\Controllers\AppointmentController;
use Betod\Livotec\Controllers\GhnController;
use Betod\Livotec\Models\Doctor;
use Betod\Livotec\Models\IngredientsAndInstructions;
use Betod\Livotec\Models\Orders;
use Betod\Livotec\Models\Product;
use Betod\Livotec\Models\Category;
use Betod\Livotec\Controllers\PayPalController;
// use Betod\Livotec\Controllers\UploadController;
use Betod\Livotec\Models\Schedules;
use Betod\Livotec\Models\Specialties;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Betod\Livotec\Controllers\AuthController;

if (!function_exists('imagePathToRelative')) {
    function imagePathToRelative($diskName)
    {
        return substr($diskName, 0, 3) . '/' . substr($diskName, 3, 3) . '/' . substr($diskName, 6, 3) . '/' . $diskName;
    }
}

if (!function_exists('getCloudinaryUrlFromDiskName')) {
    function getCloudinaryUrlFromDiskName($diskName, $folder = 'livotec')
    {
        $cloudName = config('cloudinary.cloud_url')
            ? parse_url(config('cloudinary.cloud_url'), PHP_URL_HOST)
            : env('CLOUDINARY_CLOUD_NAME');

        if (!$cloudName) {
            \Log::warning("⚠️ Không lấy được cloud_name từ config.");
            return null;
        }

        $ext = pathinfo($diskName, PATHINFO_EXTENSION);
        $name = pathinfo($diskName, PATHINFO_FILENAME);

        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$folder}/{$name}.{$ext}";
    }
}


if (!function_exists('attachCloudinaryUrlToProduct')) {
    function attachCloudinaryUrlToProduct($product)
    {
        if ($product->image) {
            $product->image->cloudinary_url = getCloudinaryUrlFromDiskName($product->image->disk_name);
        }

        if ($product->gallery) {
            foreach ($product->gallery as $img) {
                $img->cloudinary_url = getCloudinaryUrlFromDiskName($img->disk_name);
            }
        }

        return $product;
    }
}

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
    // Route::get("testabc", function () {
    //     return 'ok';
    // });
    Route::get("allProduct", function () {
        $allProduct = Product::with(['gallery', 'image', 'category.parent', 'post', 'ingredientsAndInstructions'])->get();

        $allProduct->transform(fn($p) => attachCloudinaryUrlToProduct($p));

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

        $products->transform(fn($p) => attachCloudinaryUrlToProduct($p));

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

        $products = Product::with(['gallery', 'image', 'category.parent', 'post'])
            ->where('category_id', $category->id)
            ->get();

        $products->transform(fn($p) => attachCloudinaryUrlToProduct($p));

        return $products;
    });

    Route::get('detailProduct/{slug}', function ($slug) {
        $product = Product::with(['gallery', 'image', 'category.parent', 'post'])->where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // return ($product);
        return attachCloudinaryUrlToProduct($product);
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

Route::group(['prefix' => 'apiAppointment'], function () {
    Route::get("specialties", function () {
        $specialties = Specialties::all();

        if ($specialties->isEmpty()) {
            return response()->json([
                'status' => 0,
                'specialties' => 'No data',
            ]);
        }

        return response()->json([
            'status' => 1,
            'specialties' => $specialties,
        ]);
    });

    Route::get("specialties/{specialtyId}/doctors", function ($specialtyId) {
        $doctors = Doctor::where('specialties_id', $specialtyId)->get();
        if ($doctors->isEmpty()) {
            return response()->json([
                'status' => 0,
                'doctors' => 'No data',
            ]);
        }
        return response()->json([
            'status' => 1,
            'doctors' => $doctors,
        ]);
    });

    Route::get("doctors/{doctorId}/schedules", function ($doctorId) {
        $schedules = Schedules::where('doctor_id', $doctorId)
            ->orderBy('day_of_week', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
        if ($schedules->isEmpty()) {
            return response()->json([
                'status' => 0,
                'schedules' => 'No data',
            ]);
        }
        return response()->json([
            'status' => 1,
            'schedules' => $schedules,
        ]);
    });
    Route::post("createAppointment", [AppointmentController::class, 'createAppointment']);
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

Route::group(['prefix' => 'api'], function () {
    Route::get('testabc', [AuthController::class, 'test']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('check-token', [AuthController::class, 'checkToken']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('invalidate', [AuthController::class, 'invalidate']);
    Route::post('signup', [AuthController::class, 'signup']);
});