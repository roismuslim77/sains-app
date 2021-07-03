<?php

namespace App\Http\Controllers\V1\Auth;

use App\Helpers\Calculate;
use App\Helpers\Format;
use App\Http\Controllers\Controller;
use App\Member\Distance;
use App\User;
use App\Wilayah\Pool;
use App\Wilayah\Wilayah;
use App\Wilayah\WilayahAddressDetail;
use App\Wilayah\WilayahDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public static function setUserToken($user)
    {
        $user->token = Str::random(32);
        $user->last_login_datetime = Carbon::now();
        $user->save();
    }

    public function login(Request $request)
    {
        $validated = $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ], [], [
            'username' => 'User name',
            'password' => 'Kata Sandi'
        ]);

        $user = User::select('users.*', 'user_classes.class_id', 'classes.name as class_name')
            ->where('users.username', $validated['username'])
            ->join('user_classes', 'user_classes.user_id', '=', 'users.id')
            ->join('classes', 'classes.id', '=', 'user_classes.class_id')
            ->first();

        $active = true;
        $message = 'Success';

        // CHECK MEMBER STATUS
        if (!$user) {
            abort(200, 'Akun belum terdaftar.');
        } elseif ($user->password != md5($validated['password'])) {
            abort(200, 'No telepon atau kata kandi salah.');
        }

        if ($user->status == 0) {
            $active = false;
            $message = 'Akun kamu telah dinonaktifkan.';
            abort(200, $message);
        }

        unset($user->password); // remove password attribute

        // MEMBER AUTHENTICATION TOKEN
        self::setUserToken($user);

        //mapping response data
        $data = [
            'id' => $user->id,
            'role_id' => $user->role_id,
            'class_id' => $user->class_id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'classname' => $user->class_name,
            'phone' => $user->phone,
            'status' => $user->status,
            'last_login_datetime' => $user->last_login_datetime,
            'token' => $user->token
        ];

        return Format::response([
            'active' => $active,
            'data' => $data
        ]);
    }

    public function logout()
    {
        Auth::user()->update([
            'token' => null,
            'token_expired' => Carbon::now(),
        ]);

        return Format::response([
            'message' => 'Berhasil keluar.'
        ]);
    }
}
