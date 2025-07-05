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
use Betod\Livotec\Controllers\GhnController;

class OrderController extends Controller
{
    protected $ghn;

    public function __construct()
    {
        $this->ghn = new GhnController();
    }

    public function createOrder(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'province' => 'required|integer',
            'district' => 'required|integer',
            'subdistrict' => 'required|integer',
            'address' => 'required|string|max:500',
            'diffname' => 'nullable|string|max:255',
            'diffphone' => 'nullable|string|max:20',
            'diffprovince' => 'nullable|integer',
            'diffdistrict' => 'nullable|integer',
            'diffsubdistrict' => 'nullable|integer',
            'diffaddress' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'required|boolean',
            'paymenttype' => 'required|integer',
            'differentaddresschecked' => 'required|boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $totalPrice = array_reduce($validatedData['items'], function ($sum, $item) {
            return $sum + $item['price'] * $item['quantity'];
        }, 0);

        $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $propertyData = Arr::except($validatedData, ['items', 'differentaddresschecked', 'terms', 'user_id']);

        $order = Orders::create([
            'user_id' => $validatedData['user_id'] ?? null,
            'order_code' => $orderCode,
            'price' => $totalPrice,
            'property' => $propertyData,
        ]);

        foreach ($validatedData['items'] as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => ($item['price'] * $item['quantity']),
            ]);

            $product = Product::find($item['product_id']);
            if ($product) {
                $product->stock = max(0, $product->stock - $item['quantity']);
                $product->sold_out += $item['quantity'];
                $product->save();
            }
        }

        $ghnResponse = $this->createGhnOrder($order);

        if (is_array($ghnResponse) && isset($ghnResponse['code']) && $ghnResponse['code'] === 200) {
            $order->ghn_order_code = $ghnResponse['data']['order_code'] ?? 'DEFAULT_CODE';
            $order->save();
        } elseif ($ghnResponse instanceof JsonResponse) {
            $responseData = $ghnResponse->getData(true);
            if (isset($responseData['message'])) {
                return response()->json(['code' => 400, 'message' => $responseData['message']], 400);
            }
        } else {
            return response()->json(['code' => 400, 'message' => 'Tạo đơn hàng thất bại. Vui lòng thử lại sau.'], 400);
        }

        UpdateOrderStatusJob::dispatch($order->id)->delay(now()->addMinutes(5));

        return response()->json([
            'message' => 'Order created successfully!',
            'order_code' => $order->order_code,
            'ghn_order_code' => $ghnResponse['data']['order_code'] ?? 'DEFAULT_CODE',
            'data' => $order,
        ], 201);
    }


    private function isValidShippingArea($provinceId, $districtId, $subdistrictId)
    {
        $province = $this->ghn->findProvinceById($provinceId);
        if (!$province) {
            return false;
        }

        $district = $this->ghn->findDistrictById($provinceId, $districtId);
        if (!$district) {
            return false;
        }

        $wardCode = $this->ghn->findWardCodeById($districtId, $subdistrictId);
        if (!$wardCode) {
            return false;
        }

        return true;
    }

    private function createGhnOrder($order)
    {
        $property = $order->property;

        $provinceId = (int) trim($property['province']);
        $districtId = (int) trim($property['district']);
        $subdistrictId = (int) trim($property['subdistrict']);

        if (!$this->isValidShippingArea($provinceId, $districtId, $subdistrictId)) {
            return response()->json(['code' => 400, 'message' => 'Invalid shipping area'], 400);
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
            return response()->json(['code' => 400, 'message' => 'Sender address invalid'], 400);
        }

        $orderDetails = OrderDetail::where('order_id', $order->id)->get();
        $items = $orderDetails->map(function ($item) {
            $product = Product::find($item->product_id);
            return [
                'name' => $product->name ?? 'Unknown Product',
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();

        $apiKey = env('GHN_API_KEY');

        $response = Http::withHeaders([
            'Token' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create', [
                    'payment_type_id' => $property['paymenttype'] ?? null,
                    'note' => $property['notes'] ?? '',
                    'to_name' => $property['name'],
                    'to_phone' => $property['phone'],
                    'to_address' => $property['address'],
                    'to_ward_code' => $wardCode,
                    'to_district_id' => $districtID,
                    'to_province_id' => $provinceID,
                    'required_note' => 'CHOXEMHANGKHONGTHU',
                    'from_name' => 'Megami Shop',
                    'from_phone' => '0869208950',
                    'from_address' => 'Hà Nội',
                    'from_ward_code' => $senderWardCode,
                    'from_district_id' => $senderDistrictID,
                    'from_province_id' => $senderProvinceID,
                    'client_order_code' => $order->order_code,
                    'cod_amount' => $order->price,
                    'weight' => 500,
                    'length' => 30,
                    'width' => 20,
                    'height' => 10,
                    'service_type_id' => 2,
                    'items' => $items,
                ]);

        if ($response->failed()) {
            return response()->json([
                'code' => 400,
                'message' => 'Khu vực này hiện tại đang quá tải không thể tạo đơn, mong quý khách thông cảm và tạo lại sau!'
            ], 400);
        }
        \Log::info("response: ", $response->json());


        return $response->json();
    }
}
