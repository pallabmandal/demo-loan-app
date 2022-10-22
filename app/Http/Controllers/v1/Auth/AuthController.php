<?php

namespace App\Http\Controllers\v1\Auth;

use \Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\ResponseHandler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Helpers\CustomValidator;
use App\Exceptions\DBOException;
use App\Exceptions\DataException;

class AuthController extends Controller
{
    use AuthenticatesUsers;
    use ResponseHandler;

    public function register(Request $request) {
        $inputs = $request->all();

        $validator_rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'dob' => 'required',
            'gender' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'country_code' => 'required',
            'password' => 'required'
        ];
        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result);
        }

        $user = \App\Models\User::where('email', $inputs['email'])->first();
        if(!empty($user)){
            throw new DataException('Email already exists in our system', 400);
        }

        $user = \App\Models\User::where('phone', $inputs['phone'])->where('country_code', $inputs['country_code'])->first();
        if(!empty($user)){
            throw new DataException('Phone already exists in our system', 400);
        }

        if(empty($user)){
            $user = new \App\Models\User();
            $user->first_name = $inputs['first_name'];
            $user->last_name = $inputs['last_name'];
            $user->dob = $inputs['dob'];
            $user->gender = $inputs['gender'];
            $user->email = $inputs['email'];
            $user->phone = $inputs['phone'];
            $user->country_code = $inputs['country_code'];
            $user->role_id = config('constants.roles.borrower');
            $user->password = $inputs['password'];
            $user->status = config('constants.status.active');
            $user->email_verified_at = \Carbon\Carbon::now();
            $user->phone_verified_at = \Carbon\Carbon::now();
        }

        $user->save();

        return $this->buildSuccess(true, $user, 'User Created successfully. Please log in to continue', Response::HTTP_OK);
    
    }

    public function login(Request $request)
    {    
        $inputs = $request->all();

        $validator_rules = [
            'email' => 'required',
            'password' => 'required'
        ];
        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result);
        }

        if ($this->attemptLogin($request)) {
            $user = \App\Models\User::where('email', $inputs['email'])->first();

            if($user->status != '1'){
                throw new \App\Exceptions\AuthException("User is Not Active", 401);
            }

            $key = env('JWT_KEY');

            if(empty($key)){
                throw new \App\Exceptions\AuthException("Invalid Token Decryption Key", 401);
            }

            $payload = array(
                "id" => $user->id,
                "role_id" => $user->role_id,
                "name" => $user->name,
                "email" => $user->email,
                "status" => $user->status,
                "created" => time(),
                "expired" => time()+24*3600*10
            );

            $jwt = JWT::encode($payload, $key, 'HS256');
            $payload['token'] = $jwt;

            $refreshPayload = array(
                "id" => $user->id,
                "role_id" => $user->role_id,
                "name" => $user->name,
                "email" => $user->email,
                "status" => $user->status,
                "created" => $payload['created'],
                "token" => $payload['token'],
                "expired" => time()+24*3600*365
            );

            $user->last_login = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
            $user->save();

            $jwtRefresh = JWT::encode($refreshPayload, $key, 'HS256');

            $payload['refresh_token'] = $jwtRefresh;

            return $this->buildSuccess(true, $payload, 'User logged in successfully', Response::HTTP_OK);
        } else {
            throw new \App\Exceptions\AuthException("Invalid Credentials, please try again", 401);
        }
    }
}
