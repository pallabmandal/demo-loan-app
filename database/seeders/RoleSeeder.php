<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                "id" => "1",
                "name" => "Admin",
                "code" => "admin",
            ],
            [
                "id" => "2",
                "name" => "Borrower",
                "code" => "borrower",
            ]
        ];

        foreach ($roles as $value) {
            
            $existRole = \App\Models\Role::where('code', $value['code'])->first();

            if(empty($existRole)){
                $existRole = new \App\Models\Role();
                $existRole->name = $value['name'];
                $existRole->code = $value['code'];
            } else {
                $existRole->name = $value['name'];
            }

            $existRole->save();
        
        }
    }
}
