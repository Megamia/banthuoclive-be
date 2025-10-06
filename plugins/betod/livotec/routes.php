<?php

use Betod\Livotec\Controllers\AppointmentController;
use Betod\Livotec\Controllers\Category\ImportCSV;
use Betod\Livotec\Controllers\GhnController;
use Betod\Livotec\Controllers\OrderController;
use Betod\Livotec\Controllers\Revenue\RevenueChart;
use Betod\Livotec\Controllers\Schedules\ImportCsvSchedules;
use Betod\Livotec\Controllers\VnPayController;
use Betod\Livotec\Controllers\ZaloPayController;
use Betod\Livotec\Models\Product;
use Betod\Livotec\Models\Category;
use Betod\Livotec\Controllers\PayPalController;
use Illuminate\Support\Facades\Route;

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
            \Log::warning("Không lấy được cloud_name từ config.");
            return null;
        }

        $ext = pathinfo($diskName, PATHINFO_EXTENSION);
        $name = pathinfo($diskName, PATHINFO_FILENAME);

        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$folder}/{$name}.{$ext}";
    }
}


if (!function_exists('attachCloudinaryUrl')) {
    function attachCloudinaryUrl($product)
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
    Route::get("allProduct", function () {
        $allProduct = Product::with(['gallery', 'image', 'category.parent', 'post', 'ingredientsAndInstructions'])->get();

        $allProduct->transform(fn($p) => attachCloudinaryUrl($p));

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

        $products->transform(fn($p) => attachCloudinaryUrl($p));

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

        $products->transform(fn($p) => attachCloudinaryUrl($p));

        return $products;
    });

    Route::get('detailProduct/{slug}', function ($slug) {
        $product = Product::with(['gallery', 'image', 'category.parent', 'post'])->where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // return ($product);
        return attachCloudinaryUrl($product);
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
    Route::post('createOrder', [OrderController::class, 'createOrder']);
    Route::get('getDataOrder/{order_code}', [OrderController::class, 'getDataOrder']);
    Route::get('getAllDataOrder/{id}', [OrderController::class, 'getAllDataOrder']);
    Route::post("updateStatusOrder/{order_code}", [OrderController::class, 'updateStatusOrder']);
});

Route::group(['prefix' => 'apiAppointment'], function () {
    Route::get("getDataAllDoctor", [AppointmentController::class, 'getDataAllDoctor']);
    Route::get("getDataAllDoctorById/{doctorId}", [AppointmentController::class, 'getDataAllDoctorById']);
    Route::get("getDataAllSpecialties", [AppointmentController::class, 'getDataAllSpecialties']);
    Route::get("specialties/{specialtyId}/doctors", [AppointmentController::class, 'getDoctorsBySpecialty']);
    Route::get("doctors/{doctorId}/schedules", [AppointmentController::class, 'getSchedulesByDoctorId']);
    Route::post("createAppointment", [AppointmentController::class, 'createAppointment']);
    Route::get("getDataAppointmentByUserid/{userId}", [AppointmentController::class, 'getDataAppointmentByUserid']);
});

Route::group(['prefix' => 'apiPaypal'], function () {
    Route::post('createOrder', [PayPalController::class, 'createOrder']);
    Route::post('captureOrder', [PayPalController::class, 'captureOrder']);
});

Route::group(['prefix' => 'apiImport'], function () {
    Route::post('import', [\Betod\Livotec\Controllers\Product\ImportCSV::class, 'importCsv']);
    Route::post('importCsvSchedules', [ImportCsvSchedules::class, 'importCsvSchedules']);
    Route::post('import-category', [ImportCSV::class, 'importCsv']);
});

Route::group(['prefix' => 'apiData'], function () {
    Route::get('data', [RevenueChart::class, 'chart']);
});

Route::group(['prefix' => 'apiGHN'], function () {
    Route::get('/ghn/provinces', [GhnController::class, 'getProvinces']);
    Route::get('/ghn/districts/{province_id}', [GhnController::class, 'getDistricts']);
    Route::get('/ghn/wards/{district_id}', [GhnController::class, 'getWards']);
});

Route::group(['prefix' => 'api/zalopay'], function () {
    Route::post('/create-order', [ZaloPayController::class, 'createOrder']);
    Route::post('/notify', [ZaloPayController::class, 'notify']);
    Route::post('/query-order', [ZaloPayController::class, 'queryOrder']);
});

Route::group(['prefix' => 'api/vnpay'], function () {
    Route::post('/create-order', [VnPayController::class, 'createOrder']);
    Route::get('/return', [VnPayController::class, 'return']);
    // Route::post('/query-order', [VnPayController::class, 'queryOrder']);
});
