<?php
use Illuminate\Http\Request;
use RainLab\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

Route::group(['prefix' => 'apiUser'], function () {
    Route::post('profile', function (Request $request) {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $data = User::with('additional_user')->find($user->id);

            return response()->json([
                'id' => $data->id,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'email' => $data->email,
                'additional_user' => $data->additional_user,
            ], 200);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalid'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['message' => 'Token not provided'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });

    Route::get('apiUser/test', function () {
        return response()->json(['ok' => 1]);
    });


    Route::post('/change-info', function (Request $request) {
        $user = checkToken($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
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
            'subdistrict' => 'nullable|integer',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
        ]);

        $user->additional_user()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $validatedData['phone'],
                'province' => $validatedData['province'],
                'district' => $validatedData['district'],
                'subdistrict' => $validatedData['subdistrict'],
                'address' => $validatedData['address'],
            ]
        );
        $data = User::with('additional_user')->find($user->id);
        $userdata = [
            'id' => $data->id,
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email,
            'additional_user' => $data->additional_user,
        ];
        return response()->json($userdata);
    });
    Route::post('/change-password', function (Request $request) {
        $user = checkToken($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }
        $validated = $request->validate([
            'old_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/', // Ít nhất một chữ hoa
                'regex:/[a-z]/', // Ít nhất một chữ thường
                'regex:/[0-9]/', // Ít nhất một số
                function ($attribute, $value, $fail) use ($request, $user) {
                    if (Hash::check($value, $user->password)) {
                        $fail('Mật khẩu mới không được trùng với mật khẩu cũ.');
                    }
                },
            ],
        ], [
            'new_password.regex' => 'Mật khẩu phải có ít nhất một chữ hoa, một chữ thường và một số.',
            'new_password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ]);

        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json(['status' => 0, 'error' => 'Mật khẩu cũ không chính xác!']);
        }
        $user->password = $validated['new_password'];
        $user->save();
        \Log::info("User ID {$user->id} đã đổi mật khẩu.", ['user_id' => $user->id]);
        return response()->json([
            'status' => 1,
            'message' => 'Đổi mật khẩu thành công!',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'updated_at' => $user->updated_at,
            ],
        ]);
    });
});

