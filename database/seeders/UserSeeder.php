<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\Models\User::where('first_name', 'Super')->where('last_name', 'Admin')->where('role_id', config('constants.roles.admin'))->first();

        if(empty($user)){
            $user = new \App\Models\User();
            $user->first_name = 'Super';
            $user->last_name = 'Admin';
            $user->dob = '1993-06-09';
            $user->gender = 'male';
            $user->email = 'info@gmail.com';
            $user->phone = '9812128449';
            $user->country_code = '91';
            $user->role_id = config('constants.roles.admin');
            $user->password = 'password@123';
            $user->email_verified_at = \Carbon\Carbon::now();
            $user->phone_verified_at = \Carbon\Carbon::now();
        } else {
            $user->email = 'info@gmail.com';
            $user->password = 'password@123';
        }

        $user->save();
    }
}
