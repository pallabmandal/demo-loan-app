<?php

namespace App\Traits;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Redis;

trait RolePermissionTrait {

    public static function getUserRolePermissions($key, $role) {

        $fetchedData    = [];
        $permissionData = [];

        try {

            if (env('REDIS_SWITCH')) {
                $fetchedData = Redis::smembers($key);

                if (empty($fetchedData)) {
                    $permissionData = self::roleFailSafe($role);
                } else {
                    foreach ($fetchedData as $value) {
                        $value = json_decode($value, true);
                        if ($value['role_id'] == $role) {
                            $permissionData[] = $value['permission'];
                        }

                    }

                }
            } else {
                $permissionData = self::roleFailSafe($role);
            }

        } catch (\Exception $e) {
            $permissionData = self::roleFailSafe($role);
        }

        return $permissionData;

    }

    public static function roleFailSafe($role) {
        $roles = RolePermission::where('role_id', $role)->get()->pluck('permission');

        return json_decode(json_encode($roles), true);
    }

    public static function flushDb($key) {
        Redis::DEL($key);
    }

    public static function createRole($key, $value) {
        Redis::sadd($key, $value);
    }
}
