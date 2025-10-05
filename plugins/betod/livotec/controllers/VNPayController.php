<?php
namespace Betod\Livotec\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class VnpayController extends Controller
{
    public function createOrder(Request $request)
    {
        $vnp_TmnCode = trim(env('VNPAY_TMN_CODE'));
        $vnp_HashSecret = trim(env('VNPAY_HASH_SECRET'));
        $vnp_Url = trim(env('VNPAY_URL'));
        $vnp_Returnurl = trim(env('VNPAY_RETURN_URL'));

        $vnp_TxnRef = (string) time();
        $vnp_OrderInfo = $request->input('orderInfo', "Thanh toán đơn hàng test");
        $vnp_Amount = intval($request->input('amount', 10000)) * 100;
        $vnp_Locale = "vn";
        $vnp_IpAddr = $request->ip();
        $vnp_OrderType = "billpayment";

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
        ];

        ksort($inputData);
        $hashDataArr = [];
        foreach ($inputData as $key => $value) {
            $hashDataArr[] = urlencode($key) . "=" . urlencode($value);
        }
        $hashData = implode('&', $hashDataArr);

        $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $query = http_build_query($inputData);
        $redirectUrl = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash . "&vnp_SecureHashType=SHA512";

        return response()->json([
            'code' => '00',
            'message' => 'success',
            'data' => [
                'payUrl' => $redirectUrl,
                'orderId' => $vnp_TxnRef,
                'amount' => $vnp_Amount,
            ]
        ]);
    }

    public function return(Request $request)
    {
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        $vnp_HashSecret = trim(env('VNPAY_HASH_SECRET'));

        unset($inputData['vnp_SecureHashType'], $inputData['vnp_SecureHash']);

        ksort($inputData);
        $hashDataArr = [];
        foreach ($inputData as $key => $value) {
            if (is_string($value))
                $value = mb_convert_encoding($value, 'UTF-8');
            $hashDataArr[] = $key . "=" . $value;
        }
        $hashData = implode('&', $hashDataArr);
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);


        if ($secureHash === $vnp_SecureHash) {
            if (($inputData['vnp_ResponseCode'] ?? '') === '00') {
                return response()->json(['status' => 'success', 'message' => 'Thanh toán thành công']);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Thanh toán thất bại']);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Sai chữ ký']);
        }
    }

    public function ipn(Request $request)
    {
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        $vnp_HashSecret = trim(env('VNPAY_HASH_SECRET'));

        unset($inputData['vnp_SecureHashType'], $inputData['vnp_SecureHash']);

        ksort($inputData);
        $hashDataArr = [];
        foreach ($inputData as $key => $value) {
            if (is_string($value))
                $value = mb_convert_encoding($value, 'UTF-8');
            $hashDataArr[] = $key . "=" . $value;
        }
        $hashData = implode('&', $hashDataArr);
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);


        if ($secureHash === $vnp_SecureHash) {
            if (($inputData['vnp_ResponseCode'] ?? '') === '00') {
                return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
            } else {
                return response()->json(['RspCode' => '01', 'Message' => 'Payment Failed']);
            }
        } else {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid Signature']);
        }
    }
}
