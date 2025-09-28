<?php

namespace Betod\Livotec\Controllers;

use Betod\Livotec\UpdateOrderStatusJob;
use Betod\Livotec\Models\OrderDetail;
use Betod\Livotec\Models\Product;
use Betod\Livotec\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Betod\Livotec\Controllers\GhnController;

class OrderController extends Controller
{
    protected $ghn;

    public function __construct()
    {
        $this->ghn = new GhnController();
        $this->token = env('GHN_API_KEY');
    }

    public function createOrder(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'
            ],
            'email' => 'required|email|max:255',
            'province' => 'required|integer',
            'district' => 'required|integer',
            'subdistrict' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'diffname' => 'nullable|string|max:255',
            'diffphone' => [
                'nullable',
                'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'
            ],
            'diffprovince' => 'nullable|integer',
            'diffdistrict' => 'nullable|integer',
            'diffsubdistrict' => 'nullable|string|max:255',
            'diffaddress' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'required|boolean',
            'paymenttype' => 'required|integer',
            'differentaddresschecked' => 'required|boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'paypal_order_id' => 'nullable|string|max:500',
        ]);

        // \Log::info("Validated data: ", $validatedData);

        $totalPrice = array_reduce($validatedData['items'], function ($sum, $item) {
            return $sum + $item['price'] * $item['quantity'];
        }, 0);

        $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $propertyData = Arr::except($validatedData, ['items', 'differentaddresschecked', 'terms', 'user_id']);

        if ($request->has('paypal_order_id')) {
            $propertyData['paymenttype'] = 1;
            $propertyData['paypal_order_id'] = $request->input('paypal_order_id');
        }

        try {
            DB::beginTransaction();

            $ghnItems = collect($validatedData['items'])->map(function ($item) {
                $product = Product::find($item['product_id']);
                return [
                    'name' => $product->name ?? 'Unknown Product',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] * $item['quantity'],
                ];
            })->toArray();

            // \Log::info("Creating GHN order for order_code: {$orderCode}");

            $ghnResponse = $this->createGhnOrderForItems($validatedData, $ghnItems, $orderCode);

            // \Log::info("GHN Response: ", $ghnResponse);

            if (!isset($ghnResponse['code']) || $ghnResponse['code'] !== 200) {
                DB::rollBack();
                return response()->json([
                    'code' => 400,
                    'message' => $ghnResponse['message'] ?? 'Tạo đơn hàng thất bại GHN'
                ], 400);
            }

            $order = Orders::create([
                'user_id' => $validatedData['user_id'] ?? null,
                'order_code' => $orderCode,
                'price' => $totalPrice,
                'property' => $propertyData,
                'ghn_order_code' => $ghnResponse['data']['order_code'] ?? 'DEFAULT_CODE'
            ]);

            foreach ($validatedData['items'] as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] * $item['quantity'],
                ]);

                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stock = max(0, $product->stock - $item['quantity']);
                    $product->sold_out += $item['quantity'];
                    $product->save();
                }
            }

            DB::commit();

            UpdateOrderStatusJob::dispatch($order->id)->delay(now()->addMinutes(5));

            return response()->json([
                'message' => 'Order created successfully!',
                'order_code' => $order->order_code,
                'ghn_order_code' => $order->ghn_order_code,
                'data' => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::error('Error creating order: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'Lỗi hệ thống, tạo đơn không thành công.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function createGhnOrderForItems($validatedData, $items, $orderCode)
    {
        $provinceId = (int) trim($validatedData['province']);
        $districtId = (int) trim($validatedData['district']);
        $subdistrictId = trim((string) $validatedData['subdistrict']);

        if (!$this->isValidShippingArea($provinceId, $districtId, $subdistrictId)) {
            return ['code' => 400, 'message' => 'Invalid shipping area'];
        }

        $provinceID = $this->ghn->findProvinceById($provinceId);
        $districtID = $this->ghn->findDistrictById($provinceID, $districtId);
        $wardCode = $this->ghn->findWardCodeById($districtID, $subdistrictId);

        $senderProvinceId = 201;
        $senderDistrictId = 3440;
        $senderWardId = 13004;

        $senderProvinceID = $this->ghn->findProvinceById($senderProvinceId);
        $senderDistrictID = $this->ghn->findDistrictById($senderProvinceID, $senderDistrictId);
        $senderWardCode = $this->ghn->findWardCodeById($senderDistrictID, $senderWardId);

        if (!$senderProvinceID || !$senderDistrictID || !$senderWardCode) {
            return ['code' => 400, 'message' => 'Sender address invalid'];
        }

        $apiKey = env('GHN_API_KEY');

        $response = Http::withHeaders([
            'Token' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create', [
                    'payment_type_id' => $validatedData['paymenttype'] ?? null,
                    'note' => $validatedData['notes'] ?? '',
                    'to_name' => $validatedData['name'],
                    'to_phone' => $validatedData['phone'],
                    'to_address' => $validatedData['address'],
                    'to_ward_code' => $wardCode,
                    'to_district_id' => $districtID,
                    'to_province_id' => $provinceID,
                    'required_note' => 'KHONGCHOXEMHANG',
                    'from_name' => 'Megami Shop',
                    'from_phone' => '0869208950',
                    'from_address' => 'Hà Nội',
                    'from_ward_code' => $senderWardCode,
                    'from_district_id' => $senderDistrictID,
                    'from_province_id' => $senderProvinceID,
                    'client_order_code' => $orderCode,
                    'cod_amount' => array_reduce($items, fn($sum, $i) => $sum + $i['price'], 0),
                    'weight' => 500,
                    'length' => 30,
                    'width' => 20,
                    'height' => 10,
                    'service_type_id' => 2,
                    'items' => $items,
                ]);

        $result = $response->json();
        if (!isset($result['code']) || $result['code'] != 200) {
            return [
                'code' => $result['code'] ?? 400,
                'message' => $result['message'] ?? 'Tạo đơn hàng thất bại GHN',
                'data' => $result['data'] ?? null,
            ];
        }
        // \Log::warning('GHN create order response: ', $result);

        return $result;
    }

    private function isValidShippingArea($provinceId, $districtId, $subdistrictId)
    {
        $province = $this->ghn->findProvinceById($provinceId);
        if (!$province)
            return false;

        $district = $this->ghn->findDistrictById($provinceId, $districtId);
        if (!$district)
            return false;

        $wardCode = $this->ghn->findWardCodeById($districtId, $subdistrictId);
        if (!$wardCode)
            return false;

        return true;
    }

    public function getDataOrder(Request $request, $order_code)
    {
        $dataOrder = Orders::with('orderdetail.product')
            ->where('order_code', $order_code)
            ->first();

        if (!$dataOrder) {
            return response()->json([
                'status' => 0,
                'message' => 'Lấy thông tin đơn hàng thất bại'
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Lấy danh sách đơn hàng thành công',
            'dataOrder' => $dataOrder
        ]);
    }
    public function getAllDataOrder(Request $request, $user_id)
    {
        $allDataOrder = Orders::with('orderdetail.product')
            ->where('user_id', $user_id)
            ->get();

        if ($allDataOrder->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Lấy thông tin tất cả đơn hàng thất bại'
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Lấy danh sách tất cả đơn hàng thành công',
            'allDataOrder' => $allDataOrder
        ]);
    }
    public function updateStatusOrder(Request $request, $order_code)
    {
        $order = Orders::where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json([
                'status' => 0,
                'message' => 'Không tìm thấy đơn hàng'
            ]);
        }

        $order->update(['status_id' => 2]);

        return response()->json([
            'status' => 1,
            'message' => 'Cập nhật trạng thái thành công',
            'data' => $order
        ]);
    }
}
