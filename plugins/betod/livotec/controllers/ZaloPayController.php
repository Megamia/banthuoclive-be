<?php
namespace Betod\Livotec\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Response;

class ZaloPayController extends Controller
{
    public function createOrder(Request $request)
    {
        try {
            $app_id = env('ZALOPAY_APP_ID');
            $key1 = env('ZALOPAY_KEY1');
            $key2 = env('ZALOPAY_KEY2');
            $endpoint = env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create');

            $amount = (int) $request->input('amount', 10000);
            $app_trans_id = date('ymd') . '_' . uniqid();
            $app_user = $request->input('app_user', 'user_test');
            $app_time = round(microtime(true) * 1000);
            $item = $request->input('item', '[]');
            $embed_data = json_encode($request->input('embed_data', [
                'redirecturl' => env('ZALOPAY_RETURN_URL') . "?provider=zalopay"
            ]));
            // $embed_data = "{}";
            $description = $request->input('description', "Thanh toán đơn hàng {$app_trans_id}");

            $data_mac = implode("|", [
                $app_id,
                $app_trans_id,
                $app_user,
                $amount,
                $app_time,
                $embed_data,
                $item
            ]);

            $mac = hash_hmac("sha256", $data_mac, $key1);

            $payload = [
                'app_id' => (int) $app_id,
                'app_trans_id' => $app_trans_id,
                'app_user' => $app_user,
                'app_time' => $app_time,
                'amount' => $amount,
                'description' => $description,
                'embed_data' => $embed_data,
                'item' => $item,
                'mac' => $mac
            ];

            // Log::info('ZaloPay Create Request', $payload);

            $response = Http::acceptJson()->post($endpoint, $payload);

            if (!$response->successful()) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'ZaloPay create order failed',
                    'detail' => $response->body()
                ], 500);
            }

            $resJson = $response->json();
            // Log::info('ZaloPay Create Response', $resJson);

            return Response::json([
                'status' => 'success',
                'data' => $resJson
            ]);
        } catch (\Throwable $e) {
            Log::error('ZaloPay Create Exception: ' . $e->getMessage());
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function notify(Request $request)
    {
        $data = $request->all();

        $mac = $data['mac'] ?? null;
        $key2 = env('ZALOPAY_KEY2');

        $raw_verify = implode("|", [
            $data['app_id'] ?? '',
            $data['app_trans_id'] ?? '',
            $data['app_time'] ?? '',
            $data['app_user'] ?? ($data['appuser'] ?? ''),
            $data['amount'] ?? '',
            $data['status'] ?? '',
            $data['zp_trans_id'] ?? ''
        ]);

        $calc_mac = hash_hmac('sha256', $raw_verify, $key2);

        if ($mac !== $calc_mac) {
            return Response::json(['return_code' => -1, 'return_message' => 'MAC mismatch']);
        }

        if ((int) ($data['status'] ?? 0) === 1) {
            return Response::json(['return_code' => 1, 'return_message' => 'Success']);
        } else {
            return Response::json(['return_code' => 0, 'return_message' => 'Failed']);
        }
    }
    public function queryOrder(Request $request)
    {
        try {
            $app_id = env('ZALOPAY_APP_ID');
            $key1 = env('ZALOPAY_KEY1');
            $endpoint = 'https://sb-openapi.zalopay.vn/v2/query';

            $app_trans_id = $request->input('app_trans_id');
            if (!$app_trans_id) {
                return response()->json(['error' => 'Missing app_trans_id'], 400);
            }

            $data = [
                'app_id' => (int) $app_id,
                'app_trans_id' => $app_trans_id,
            ];

            $mac = hash_hmac('sha256', $app_id . '|' . $app_trans_id . '|' . $key1, $key1);
            $data['mac'] = $mac;

            $response = \Http::asForm()->post($endpoint, $data);

            if (!$response->successful()) {
                return response()->json(['error' => 'Query failed'], 500);
            }

            return $response->json();
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
