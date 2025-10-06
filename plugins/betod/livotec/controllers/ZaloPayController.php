<?php
namespace Betod\Livotec\Controllers;

use Backend\Classes\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ZaloPayController extends Controller
{
    /**
     * Tạo đơn hàng ZaloPay
     */
    public function createOrder(Request $request)
    {
        try {
            $app_id   = env('ZALOPAY_APP_ID');
            $key1     = env('ZALOPAY_KEY1');
            $endpoint = env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create');

            $amount        = (int) $request->input('amount', 10000);
            $app_trans_id  = date('ymd') . '_' . uniqid();
            $app_user      = $request->input('app_user', 'user_test');
            $app_time      = round(microtime(true) * 1000);
            $item          = $request->input('item', '[]');
            $description   = $request->input('description', "Thanh toán đơn hàng {$app_trans_id}");

            // embed_data: chứa redirect url FE
            $embed_data = json_encode([
                'redirecturl' => env('ZALOPAY_RETURN_URL') . "?provider=zalopay"
            ]);

            // data_mac để tạo chữ ký
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
                'app_id'       => (int) $app_id,
                'app_trans_id' => $app_trans_id,
                'app_user'     => $app_user,
                'app_time'     => $app_time,
                'amount'       => $amount,
                'description'  => $description,
                'embed_data'   => $embed_data,
                'item'         => $item,
                'mac'          => $mac,
                'callback_url' => env('ZALOPAY_CALLBACK_URL') // thêm notify URL
            ];

            $response = Http::asForm()->post($endpoint, $payload);

            if (!$response->successful()) {
                return Response::json([
                    'status'  => 'error',
                    'message' => 'ZaloPay create order failed',
                    'detail'  => $response->body()
                ], 500);
            }

            $resJson = $response->json();
            return Response::json([
                'status' => 'success',
                'data'   => $resJson
            ]);
        } catch (\Throwable $e) {
            Log::error('ZaloPay Create Exception: ' . $e->getMessage());
            return Response::json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ZaloPay Notify (callback server-to-server)
     */
    public function notify(Request $request)
    {
        try {
            $key2   = env('ZALOPAY_KEY2');
            $data   = $request->input('data');
            $reqMac = $request->input('mac');

            // verify mac
            $calcMac = hash_hmac('sha256', $data, $key2);

            if ($reqMac !== $calcMac) {
                return response()->json([
                    'return_code'    => -1,
                    'return_message' => 'MAC mismatch'
                ]);
            }

            $orderData = json_decode($data, true);

            // TODO: cập nhật trạng thái đơn hàng trong DB của bạn
            Log::info('ZaloPay Notify Success', $orderData);

            return response()->json([
                'return_code'    => 1,
                'return_message' => 'Success'
            ]);
        } catch (\Throwable $e) {
            Log::error('ZaloPay Notify Exception: ' . $e->getMessage());
            return response()->json([
                'return_code'    => 0,
                'return_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Query Order
     */
    public function queryOrder(Request $request)
    {
        try {
            $app_id   = env('ZALOPAY_APP_ID');
            $key1     = env('ZALOPAY_KEY1');
            $endpoint = 'https://sb-openapi.zalopay.vn/v2/query';

            $app_trans_id = $request->input('app_trans_id');
            if (!$app_trans_id) {
                return response()->json(['error' => 'Missing app_trans_id'], 400);
            }

            $data = [
                'app_id'       => (int) $app_id,
                'app_trans_id' => $app_trans_id,
            ];

            $mac = hash_hmac('sha256', $app_id . '|' . $app_trans_id . '|' . $key1, $key1);
            $data['mac'] = $mac;

            $response = Http::asForm()->post($endpoint, $data);

            if (!$response->successful()) {
                return response()->json(['error' => 'Query failed'], 500);
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('ZaloPay Query Exception: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
