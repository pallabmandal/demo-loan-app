<?php

namespace App\Traits;
use App\User;
use App\Models\UserRole;
use JwtAuth;
use App\Traits\RolePermissionTrait;

trait PermissionHandler {

    use RolePermissionTrait;

    public static function checkRole($permissionName) {

        $className = debug_backtrace()[1]['class'];
        $className = explode('\\', $className);
        $className = end($className);

        $user = JwtAuth::getAuth();
        
        $roleId = $user['role_id'];
        //super-admin
        if($roleId == config('constants.roles.admin')){
            return true;
        }


        $redisPermissions = 'permissondata_roles';

        //Get Permissions
        $permission = self::getUserRolePermissions($redisPermissions, $roleId);
        
        if (in_array($className . '/' . $permissionName, $permission)) {
            return true;
        } else {
            return false;
        }

    }

    public static function checkClientRole($permissionName) {

        $className = debug_backtrace()[1]['class'];
        $className = explode('\\', $className);
        $className = end($className);

        $user = JwtAuth::getAuth();
        
        $roleId = $user['role_id'];

        if(empty($user['client_id'])){
            return false;
        }

        if(in_array($roleId, config('constants.all_client_permission_roles'))){
            return true;
        }

        $redisPermissions = 'permissondata_roles';

        //Get Permissions
        $permission = self::getUserRolePermissions($redisPermissions, $roleId);
        
        if (in_array($className . '/' . $permissionName, $permission)) {
            return true;
        } else {
            return false;
        }

    }
}