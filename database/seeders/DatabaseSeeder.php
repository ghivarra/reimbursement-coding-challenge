<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roleID = $this->createRole();
        $this->createDefaultUser($roleID);
    }

    //===================================================================================================

    private function createRole(): int
    {
        // add superadmin roles
        $role = new Role;
        $role->name = "Superadmin";
        $role->is_superadmin = 1;
        $role->save();

        // return
        return $role->id;
    }

    //===================================================================================================

    private function createDefaultUser(int $roleID): void
    {
        // add default superadmin user
        $user = new User;
        $user->name = 'Ghivarra Senandika R';
        $user->email = 'gsenandika@gmail.com';
        $user->password = 'user12345';
        $user->role_id = $roleID;
        $user->save();
    }

    //===================================================================================================
}
