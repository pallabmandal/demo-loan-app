<?php

namespace App\Http\Controllers\v1\Auth;

use App\Traits\LoggerHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\ResponseHandler;
use App\Exceptions\DBOException;
use App\Helpers\CustomValidator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Exceptions\DataNotFoundException;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, ResponseHandler, LoggerHelper;

    public function __construct()
    {
        $this->middleware('guest');
    }

    //Method to create new token for password reset
    public function create(Request $request)
    {
        $inputs = $request->all();

        $validator_rules = [
            'email' => 'required|email|exists:users,email'
        ];

        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result, 2);
        }

        $user = \App\Models\User::where('email', $request->email)->first();

        if(!$user){
            throw new \App\Exceptions\DataNotFoundException('User not found', Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $reset = \App\Models\PasswordReset::updateOrCreate(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => \Str::random(60),
                    'created_at' => \Carbon\Carbon::now(),
                    'expired_at' => \Carbon\Carbon::now()->addDays('1'),
                    'status' => 0
                ]
            );
    
            $data = [
                'name' => $user->first_name,
                'email' => $user->email,
                'url' => env('APP_FE_URL')."/password/reset/".$reset->token
            ];
        
            \Mail::to($user->email)
                ->send(new \App\Mail\SendPasswordResetTokenMail($data));
        }
        catch(\Exception $e) {
            DB::rollback();
            \Log::info('Password reset token not generated. Error:'.$e->getMessage());
            throw new \App\Exceptions\DBOException('Password reset token not generated', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        DB::commit();
        return $this->buildSuccess(true, $response=[], 'We have emailed your password reset link!', Response::HTTP_OK);
    }

    public function reset(Request $request)
    {   
        $inputs = $request->all();

        $validator_rules = [
            'token' => 'required',
            'password' => 'required'
        ];

        $validate_result = CustomValidator::validator($inputs, $validator_rules);

        if($validate_result['code']!== 200){
            return ResponseHandler::buildUnsuccessfulValidationResponse($validate_result, 2);
        }

        $passwordReset = \App\Models\PasswordReset::where('token', $request->token)->first();

        if(!$passwordReset){
            throw new \App\Exceptions\DataNotFoundException('Invalid token', 401);
        }

        if($passwordReset->status == 1) {
            throw new \App\Exceptions\DataNotFoundException('Invalid token', 401);
        }

        if(\Carbon\Carbon::parse($passwordReset->expired_at) <  \Carbon\Carbon::now()) {
            throw new \App\Exceptions\DataNotFoundException('Expired token', 401);
        }

        if(\Carbon\Carbon::parse($passwordReset->created_at)->addMinutes(720)->isPast()){
            throw new DataNotFoundException('Password reset token is expired', Response::HTTP_BAD_REQUEST);
        }

        $user = \App\Models\User::where('email', $passwordReset->email)->first();

        if(empty($user)){
            throw new \App\Exceptions\DataNotFoundException('User not found', Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $user->password = $request->password;
            $user->save();

            $passwordReset->status = 1;
            $passwordReset->save();

            \Mail::to($user->email)
                ->send(new \App\Mail\PasswordResetConfirmationMail($user));
        }
        catch(\Exception $e) {
            DB::rollback();
            \Log::info('Password rest failed!. Error:'.$e->getMessage());
            throw new \App\Exceptions\DBOException('Password rest failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        DB::commit();
        return $this->buildSuccess(true, $res=[], 'Password reset successful', Response::HTTP_OK);
    }
}
