<?php

namespace Betod\Livotec\Controllers;

use ApplicationException;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use Response;
use GuzzleHttp\Client;

class PayPalController
{
    private $client;

    public function __construct()
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_SECRET');

        $environment = new SandboxEnvironment($clientId, $clientSecret);
        $this->client = new PayPalHttpClient($environment);
    }

    /**
     * Lấy tỷ giá từ API (VND -> USD)
     */
    public function getExchangeRate()
    {
        $client = new Client();
        $url = 'https://api.exchangerate-api.com/v4/latest/USD';  // Dùng API tỷ giá

        $response = $client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['rates']['VND'])) {
            return $data['rates']['VND'];
        }

        throw new ApplicationException('Không thể lấy tỷ giá.');
    }

    /**
     * Chuyển đổi VND sang USD
     */
    public function convertVndToUsd($vndAmount)
    {
        $exchangeRate = $this->getExchangeRate();
        $usdAmount = $vndAmount / $exchangeRate;
        return number_format($usdAmount, 2, '.', '');  // Làm tròn về 2 chữ số thập phân
    }

    /**
     * API để tạo đơn hàng PayPal
     */
    public function createOrder()
    {
        $vndAmount = post('amount');  // Số tiền bằng VND

        if (!$vndAmount || $vndAmount <= 0) {
            throw new ApplicationException('Số tiền không hợp lệ.');
        }

        // Chuyển đổi từ VND sang USD
        $usdAmount = $this->convertVndToUsd($vndAmount);

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $usdAmount
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->execute($request);
            return Response::json([
                'status' => 'success',
                'orderID' => $response->result->id
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API để xác nhận thanh toán
     */
    public function captureOrder()
    {
        $orderId = post('orderID');

        if (!$orderId) {
            throw new ApplicationException('Order ID is required.');
        }

        $request = new OrdersCaptureRequest($orderId);
        $request->prefer('return=representation');

        try {
            $response = $this->client->execute($request);
            return Response::json([
                'status' => 'success',
                'data' => $response->result
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
