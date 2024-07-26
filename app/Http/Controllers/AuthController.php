<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Mail\ForgetPasswordMail;
use App\Models\User;
use Mail;

class AuthController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:7',
            'confirm_password' => 'required|same:password',
            'contury_number' => 'required|numeric',
            'phone_number' => 'required|numeric|unique:users'
        ]);
    }

    protected function emailValidation($email)
    {
        // https://apilayer.com/marketplace/email_verification-api

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.apilayer.com/email_verification/check?email=".$email,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: text/plain",
            "apikey: mP1Qri9LcC1tFmZKmktOmHTmECucWBkj"
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $json_response = json_decode($response);

        return $json_response->smtp_check;
    }

    public function addAdmin(Request $request)
    {
        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }
        // if (!$this->emailValidation($request['email'])){
        //     return response()->json(['data'=>'Email is not valid!'], 400);
        // }

        $user = User::create([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'user_name' => $request['user_name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'contury_number' => $request['contury_number'],
            'phone_number' => $request['phone_number'],
            'permission' => 1
        ]);
        $user->save();

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function adminLogin(Request $request)
    {
        if(auth('web')->attempt($request->only('email', 'password'))){

            $user = User::where('email', $request['email'])->firstOrFail();
            $permission = $user->permission;

        }else if (auth('web')->attempt($request->only('user_name', 'password'))) {

            $user = User::where('user_name', $request['user_name'])->firstOrFail();
            $permission = $user->permission;

        }else{
            return response()->json([
                'errors' => 'Invalid login details'
            ], 401);
        }

        if($permission == 0){
            return response()->json(['data' => "Access Denied"], 403); 
        }

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function register(Request $request)
    {
        $validatedData = $this->validator($request->all());
        if ($validatedData->fails()) {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }
        // if (!$this->emailValidation($request['email'])){
        //     return response()->json(['data'=>'Email is not valid!'], 400);
        // }

        $user = User::create([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'user_name' => $request['user_name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'contury_number' => $request['contury_number'],
            'phone_number' => $request['phone_number']
        ]);
        $user->save();

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        if(auth('web')->attempt($request->only('email', 'password'))){

            $user = User::where('email', $request['email'])->firstOrFail();
            $permission = $user->permission;

        }else if (auth('web')->attempt($request->only('user_name', 'password'))) {

            $user = User::where('user_name', $request['user_name'])->firstOrFail();
            $permission = $user->permission;

        }else{
            return response()->json([
                'errors' => 'Invalid login details'
            ], 401);
        }

        if($permission != 0){
            return response()->json(['data' => "Access Denied"], 403); 
        }

        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()], 400);
        }

        $email = $request['email'];

        if(User::where('email', $email)->doesntExist()){
            return response()->json([
                'data' => 'User Does not Exists !'
            ], 400);
        }

        $code = Str::random(10);

        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $code
        ]);

        // $mailData = [
        //     'code' => $code,
        // ];

        // Mail::to($email)->send(new ForgetPasswordMail($mailData));

        return response()->json([
            'data' => 'Check Your Email.'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => 'required|string|min:10|max:10',
            'password' => 'required|string|min:7',
            'confirm_password' => 'required|string|same:password',
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()], 400);
        }
        
        $passwordResets = DB::table('password_reset_tokens')->where('token', $request['code'])->first();

        if(!$passwordResets){
            return response()->json([
                'data' => 'Invalid Code !'
            ], 400);
        }

        $user = User::where('email', $passwordResets->email)->first();
        $user->password = Hash::make($request['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('token', $request['code'])->delete();

        return response()->json([
            'data' => 'Reset Success.'
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'old_password' => 'required|string|min:7',
            'new_password' => 'required|string|min:7',
            'confirm_new_password' => 'required|same:new_password',
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()], 400);
        }

        if(!Hash::check($request->old_password, $user->password)){
            return response()->json(['data'=>'The password is incorrect'], 400);
        }

        $user->password = Hash::make($request['new_password']);
        $user->save();

        return response()->json(['data' => 'change password done'], 200);
    }

    public function logout () {
        $token = Auth::user()->token();
        $token->revoke();
        return response()->json(['message' => 'You have been successfully logged out!'], 200);
    }
}
