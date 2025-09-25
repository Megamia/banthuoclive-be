<?php
namespace Dat\User\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use Dat\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Dat.User', 'users');
    }

    public function authenticateToken(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];

        $user = User::where('api_token', $token)->first();

        return $user ?: null;
    }


    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['status' => 0, 'message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }

        $token = Str::random(60);
        $user->api_token = $token;
        $user->save();

        $cookie = cookie('token', $token, 1440, '/', null, false, true);

        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name ?? '',
            'email' => $user->email,
            'phone' => $user->phone,
            'province' => $user->province,
            'district' => $user->district,
            'subdistrict' => $user->subdistrict,
            'address' => $user->address,
        ];

        return response()->json([
            'status' => 1,
            'message' => 'Đăng nhập thành công',
            'user' => $userData,
            'token' => $token
        ])->cookie($cookie);
    }

    public function signup(Request $request)
    {
        $email = $request->input('email');
        $firstName = $request->input('first_name');
        $password = $request->input('password');

        if (User::where('email', $email)->exists()) {
            return response()->json(['status' => 0, 'message' => 'Email đã được sử dụng']);
        }

        $user = new User();
        $user->first_name = $firstName;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        // $userData = [
        //     'id' => $user->id,
        //     'first_name' => $user->first_name,
        //     'email' => $user->email,
        // ];

        return response()->json(['status' => 1, 'message' => "Đăng ký thành công"]);
    }

    public function profile(Request $request)
    {
        $user = $this->authenticateToken($request);

        if (!$user) {
            return response()->json(['status' => 1, 'message' => 'Xác thực thông tin người dùng thất bại']);
        }

        return response()->json(['status' => 1, 'user' => $user]);
    }
    public function change_infor(Request $request)
    {
        $user = $this->authenticateToken($request);

        if (!$user) {
            return response()->json(['status' => 1, 'message' => 'Xác thực thông tin người dùng thất bại']);
        }

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => [
                'required',
                'regex:/^(0[3|5|7|8|9])[0-9]{8}$/'
            ],
            'province' => 'nullable|integer',
            'district' => 'nullable|integer',
            'subdistrict' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        \Log::info('Validated data: ', $validatedData);

        $user->update($validatedData);
        $user->refresh();
        return response()->json([
            'status' => 1,
            'message' => 'Cập nhật thông tin thành công',
            'newDataUser' => $user
        ]);
    }
    public function logout(Request $request)
    {
        $user = $this->authenticateToken($request);

        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        $cookie = cookie('token', '', -1, '/', null, false, true);

        return response()->json([
            'status' => 1,
            'message' => 'Đăng xuất thành công'
        ])->cookie($cookie);
    }

    public function test(Request $request)
    {
        $data = User::all();
        return response()->json([
            'message' => "test ok",
            'data' => $data
        ]);
    }

}
