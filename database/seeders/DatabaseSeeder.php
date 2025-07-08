<?php

namespace Database\Seeders;

use App\Models\Module;
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
        $this->createDefaultModules();
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

    private function createDefaultModules(): void
    {
        // add modules
        $modules = [
            // roles
            ['name' => 'role.index', 'description' => 'Endpoint untuk mengakses semua jenis role.'],
            ['name' => 'role.find', 'description' => 'Endpoint untuk mengakses satu role.'],
            ['name' => 'role.create', 'description' => 'Endpoint untuk membuat role.'],
            ['name' => 'role.update', 'description' => 'Endpoint untuk memperbaharui data role.'],
            ['name' => 'role.delete', 'description' => 'Endpoint untuk menghapus role.'],

            // users
            ['name' => 'user.index', 'description' => 'Endpoint untuk mengakses semua data user.'],
            ['name' => 'user.find', 'description' => 'Endpoint untuk mengakses data satu user.'],
            ['name' => 'user.create', 'description' => 'Endpoint untuk membuat user.'],
            ['name' => 'user.update', 'description' => 'Endpoint untuk memperbaharui data user.'],
            ['name' => 'user.delete', 'description' => 'Endpoint untuk menghapus user.'],
        ];

        // insert modules
        Module::insert($modules);
    }

    //===================================================================================================
}
