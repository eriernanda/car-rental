<?php

namespace App\Http\Controllers;

use App\Helpers\JsonFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function loginView()
    {
        if (session()->has('account_id')) {
            return redirect("/rent/all");
        }

        return view('auth.login');
    }

    public function loginSubmit(Request $request)
    {
        try {
            $validateData = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateData->fails()) {
                return JsonFormatter::error(null, $validateData->errors(), 422);
            }

            $user = DB::table("t_account")->where("email", $request->email)->first();
            if(empty($user)) {
                return JsonFormatter::error('', 'Account Not Found', 404);
            }

            if(!Hash::check($request->password, $user->password)) {
                return JsonFormatter::error(null, 'Unauthorized', 401);
            }

            session()->put([
                "account_id"    => Crypt::encryptString($user->id),
                "account_name"  => $user->name,
                "account_email" => $user->email,
                "account_role"  => $user->role,
            ]);

            return JsonFormatter::success(null, 'Success Login');
        } catch (Exception $err) {
            return JsonFormatter::error(null, $err->getMessage(), 500);
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect("/login");
    }
}
