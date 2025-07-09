<?php

namespace App\Library;

use App\Models\Menu;
use App\Models\Module;
use App\Models\RoleMenuList;
use App\Models\RoleModuleList;
use App\Models\User;

class RoleManagementLibrary
{
    public function getUserAccess(int $userID): array|false
    {
        // get current user data
        $user = User::select('users.role_id', 'is_superadmin')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->where('users.id', '=', $userID)
                    ->first();
        
        // false if empty
        if (empty($user))
        {
            return false;
        }
        
        // if superadmin then access all modules and menu
        if ($user->is_superadmin === 1)
        {
            $menus   = Menu::select('name', 'route_name', 'icon', 'sort_order')->orderBy('sort_order', 'ASC')->get();
            $modules = Module::select('name')->get();
            
        } else {

            $menus   = RoleMenuList::select('menus.name', 'menus.route_name', 'menus.icon', 'menus.sort_order')
                                   ->where('role_id', '=', $user->role_id)
                                   ->join('menus', 'menu_id', '=', 'menus.id')
                                   ->get();

            $modules = RoleModuleList::select('modules.name')
                                     ->where('role_id', '=', $user->role_id)
                                     ->join('modules', 'module_id', '=', 'modules.id')
                                     ->get();
        }

        $access = [
            'menus'   => empty($menus) ? [] : $menus->toArray(),
            'modules' => empty($menus) ? [] : $modules->toArray(),
        ];

        // return
        return $access;
    }

    //==============================================================================================

    public function validateAccess(int $userID, string $routeName): bool
    {
        // get access
        $access = $this->getUserAccess($userID);

        // set to session
        session(['access', $access]);

        // set
        return in_array($routeName, array_column($access['modules'], 'name'));
    }

    //==============================================================================================
}