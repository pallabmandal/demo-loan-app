<?php

namespace App\Http\Middleware;

use Closure;
use JwtAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        
        if(empty($token)){
            throw new \App\Exceptions\AuthException("Token not found", 401);
        } else {
            
            $init = substr($token, 0, 7);
            
            if($init !== 'Bearer '){
                throw new \App\Exceptions\AuthException("Invalid Token Prefix", 401);
            }

            $token = substr($token, 7);

            $key = env('JWT_KEY');

            if(empty($key)){
                throw new \App\Exceptions\AuthException("Invalid Token Decryption Key", 401);
            }

            try {
               
                $decoded = JWT::decode($token,  new Key($key, 'HS256'));


            } catch (\Exception $e) {
                throw new \App\Exceptions\AuthException("Invalid Token", 401);
            }
            
            $user = \App\Models\User::find($decoded->id);

            if(empty($user)){
                throw new \App\Exceptions\AuthException("Invalid User", 401);
            }

            if($user->status != config('constants.status.active')){
                throw new \App\Exceptions\AuthException("User is Not Active", 401);
            }

            if($user->role_id != config('constants.roles.admin')){
                throw new \App\Exceptions\AuthException("User is Not Admin", 401);
            }

            if($decoded->expired < time()){
                throw new \App\Exceptions\AuthException("Token has expired", 401);
            }

        }
        
        JwtAuth::setAuth($decoded);
        
        return $next($request);

    }
}
