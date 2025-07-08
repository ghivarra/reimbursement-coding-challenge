<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Reimbursement;
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
            ['name' => 'role.index', 'description' => 'Mengakses semua jenis role.'],
            ['name' => 'role.find', 'description' => 'Mengakses satu role.'],
            ['name' => 'role.create', 'description' => 'Membuat role.'],
            ['name' => 'role.update', 'description' => 'Memperbaharui data role.'],
            ['name' => 'role.delete', 'description' => 'Menghapus role.'],
            ['name' => 'role.update.modules', 'description' => 'Menambahkan/memperbaharui module dan fitur pada role.'],
            ['name' => 'role.update.menus', 'description' => 'Menambahkan/memperbaharui menu pada role.'],

            // users
            ['name' => 'user.index', 'description' => 'Mengakses semua data user.'],
            ['name' => 'user.find', 'description' => 'Mengakses data satu user.'],
            ['name' => 'user.create', 'description' => 'Membuat user.'],
            ['name' => 'user.update', 'description' => 'Memperbaharui data user.'],
            ['name' => 'user.delete', 'description' => 'Menghapus user.'],

            // menu & modul
            ['name' => 'menu.find.all', 'description' => 'Menampilkan semua data menu.'],
            ['name' => 'module.find.all', 'description' => 'Menampilkan semua data modul.'],

            // reimbursement category
            ['name' => 'reimbursement.category.index', 'description' => 'Mengakses semua data kategori reimbursement.'],
            ['name' => 'reimbursement.category.find', 'description' => 'Mengakses data satu kategori reimbursement.'],
            ['name' => 'reimbursement.category.create', 'description' => 'Membuat kategori reimbursement.'],
            ['name' => 'reimbursement.category.update', 'description' => 'Memperbaharui data kategori reimbursement.'],
            ['name' => 'reimbursement.category.delete', 'description' => 'Menghapus kategori reimbursement.'],
            ['name' => 'reimbursement.category.check.limit', 'description' => 'Mengecek limit yang tersisa pada kategori reimbursement.'],

            // reimbursement
            ['name' => 'reimbursement.main.index.with.removed', 'description' => 'Mengakses semua data termasuk yang sudah dihapus.'],
            ['name' => 'reimbursement.main.index.all', 'description' => 'Menarik semua data kecuali yang sudah dihapus'],
            ['name' => 'reimbursement.main.index.self', 'description' => 'Mengakses data buatan sendiri'],

            ['name' => 'reimbursement.main.find.with.removed', 'description' => 'Mengakses satu data termasuk yang sudah dihapus.'],
            ['name' => 'reimbursement.main.find.all', 'description' => 'Menarik semua data kecuali yang sudah dihapus'],
            ['name' => 'reimbursement.main.find.self', 'description' => 'Mengakses data buatan sendiri'],

            ['name' => 'reimbursement.main.create', 'description' => 'Membuat reimbursement'], // employee only
            ['name' => 'reimbursement.main.update', 'description' => 'Merevisi reimbursement'], // employee only
            ['name' => 'reimbursement.main.delete', 'description' => 'Menghapus reimbursement'], // employee only
            ['name' => 'reimbursement.main.respond', 'description' => 'Memberikan respon penerimaan atau penolakan terhadap reimbursement'], // manager

            // reimbursement logs
            ['name' => 'reimbursement.log.find.with.removed', 'description' => 'Mengakses satu data log dari pengajuan termasuk yang sudah dihapus.'],
            ['name' => 'reimbursement.log.find.all', 'description' => 'Menarik semua data log dari pengajuan kecuali yang sudah dihapus'],
            ['name' => 'reimbursement.log.find.self', 'description' => 'Mengakses data log dari pengajuan buatan sendiri'],

        ];

        // insert modules
        Module::insert($modules);
    }

    //===================================================================================================
}
