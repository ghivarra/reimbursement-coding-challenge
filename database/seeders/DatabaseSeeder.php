<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Module;
use App\Models\ReimbursementStatus;
use App\Models\Role;
use App\Models\RoleMenuList;
use App\Models\RoleModuleList;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        $this->createDefaultMenu();
        $this->createDefaultRoleAndUser();
        $this->grantModuleAndMenu();
        $this->createStatuses();
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
            ['name' => 'reimbursement.main.index.with.removed', 'description' => 'Mengakses semua data termasuk yang sudah dihapus.'], // admin
            ['name' => 'reimbursement.main.index.approver', 'description' => 'Menarik semua data approver kecuali yang sudah dihapus'], // manager
            ['name' => 'reimbursement.main.index.self', 'description' => 'Mengakses data buatan sendiri'], // employee
            ['name' => 'reimbursement.main.index.submitted', 'description' => 'Mengakses data yang sudah diajukan'], // manager
            ['name' => 'reimbursement.main.index.all', 'description' => 'Mengakses semua data kecuali yang sudah dihapus'], // admin & manager
            ['name' => 'reimbursement.main.index.archive', 'description' => 'Mengakses hanya data yang sudah dihapus'], // admin

            ['name' => 'reimbursement.main.find.with.removed', 'description' => 'Mengakses satu data termasuk yang sudah dihapus.'],
            ['name' => 'reimbursement.main.find.approver', 'description' => 'Menarik satu data approver kecuali yang sudah dihapus'],
            ['name' => 'reimbursement.main.find.self', 'description' => 'Mengakses data buatan sendiri'],

            ['name' => 'reimbursement.main.create', 'description' => 'Membuat reimbursement'], // employee only
            ['name' => 'reimbursement.main.update', 'description' => 'Merevisi reimbursement'], // employee only
            ['name' => 'reimbursement.main.delete', 'description' => 'Menghapus reimbursement'], // employee only
            ['name' => 'reimbursement.main.respond', 'description' => 'Memberikan respon penerimaan atau penolakan terhadap reimbursement'], // manager

            // reimbursement logs
            ['name' => 'reimbursement.log.find.with.removed', 'description' => 'Mengakses satu data log dari pengajuan termasuk yang sudah dihapus.'],
            ['name' => 'reimbursement.log.find.approver', 'description' => 'Menarik satu data log approver dari pengajuan kecuali yang sudah dihapus'],
            ['name' => 'reimbursement.log.find.self', 'description' => 'Mengakses data log dari pengajuan buatan sendiri'],

            // views
            ['name' => 'view.approval', 'description' => 'Mengakses halaman persetujuan/approval'],
            ['name' => 'view.approval.examine', 'description' => 'Mengakses halaman pengecekan pengajuan reimbursement'],
            ['name' => 'view.application', 'description' => 'Mengakses halaman pengajuan reimbursement'],
            ['name' => 'view.application.create', 'description' => 'Mengakses halaman pembuatan reimbursement'],
            ['name' => 'view.application.update', 'description' => 'Mengakses halaman revisi reimbursement'],
            ['name' => 'view.role', 'description' => 'Mengakses halaman manajemen akses/role'],
            ['name' => 'view.role.update.module', 'description' => 'Mengakses halaman update modul manajemen akses/role'],
            ['name' => 'view.role.update.menu', 'description' => 'Mengakses halaman update menu manajemen akses/role'],
            ['name' => 'view.archive', 'description' => 'Mengakses halaman arsip/pengajuan yang sudah dihapus'],
            ['name' => 'view.user', 'description' => 'Mengakses halaman user'],
            ['name' => 'view.category', 'description' => 'Mengakses kategori reimbursement'],

        ];

        // insert modules
        Module::insert($modules);
    }

    //===================================================================================================

    private function createDefaultMenu()
    {
        $menus = [
            ['name' => 'Persetujuan', 'route_name' => 'view.approval', 'icon' => 'ClipboardPenLine', 'sort_order' => 1],
            ['name' => 'Pengajuan', 'route_name' => 'view.application', 'icon' => 'FileText', 'sort_order' => 2],
            ['name' => 'Arsip', 'route_name' => 'view.archive', 'icon' => 'Archive', 'sort_order' => 3],
            ['name' => 'Kategori', 'route_name' => 'view.category', 'icon' => 'ChartBarStacked', 'sort_order' => 4],
            ['name' => 'Pengguna', 'route_name' => 'view.user', 'icon' => 'UserRoundCog', 'sort_order' => 5],
            ['name' => 'Role', 'route_name' => 'view.role', 'icon' => 'ShieldCheckIcon', 'sort_order' => 6],
        ];

        // add menus
        Menu::insert($menus);
    }

    //===================================================================================================

    private function createDefaultRoleAndUser()
    {
        $now   = date('Y-m-d H:i:s');
        $roles = [
            ['name' => 'Admin', 'is_superadmin' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Employee', 'is_superadmin' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manager', 'is_superadmin' => 0, 'created_at' => $now, 'updated_at' => $now],
        ];

        Role::insert($roles);

        // add user
        $users = [
            [
                'name'       => 'Bayu Aji',
                'password'   => Hash::make('User*12345'),
                'role_id'    => 2,
                'email'      => 'bayu.aji@ghivarra.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Seno Susilo',
                'password'   => Hash::make('User*12345'),
                'role_id'    => 3,
                'email'      => 'seno.susilo@ghivarra.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Angga Restu',
                'password'   => Hash::make('User*12345'),
                'role_id'    => 4,
                'email'      => 'angga@ghivarra.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        User::insert($users);
    }

    //===================================================================================================

    private function grantModuleAndMenu()
    {
        // get all role
        $roles = Role::all();

        // grant module
        foreach ($roles as $role):

            if ($role->name === 'Admin')
            {
                $modules = [
                    'role.index',
                    'role.find',
                    'role.create',
                    'role.update',
                    'role.delete',
                    'role.update.modules',
                    'role.update.menus',
                    'user.index',
                    'user.find',
                    'user.create',
                    'user.update',
                    'user.delete',
                    'menu.find.all',
                    'module.find.all',
                    'reimbursement.category.index',
                    'reimbursement.category.find',
                    'reimbursement.category.create',
                    'reimbursement.category.update',
                    'reimbursement.category.delete',
                    'reimbursement.category.check.limit',
                    'reimbursement.main.index.all',
                    'reimbursement.main.index.archive',
                    'reimbursement.main.index.with.removed',
                    'reimbursement.main.find.with.removed',
                    'reimbursement.log.find.with.removed',
                    'view.application',
                    'view.role',
                    'view.role.update.module',
                    'view.role.update.menu',
                    'view.archive',
                    'view.user',
                    'view.category',
                ];

                $menus = [
                    'Pengajuan',
                    'Arsip',
                    'Kategori',
                    'Pengguna',
                    'Role',
                ];
            }

            if ($role->name === 'Employee')
            {
                $modules = [
                    'reimbursement.category.check.limit',
                    'reimbursement.main.index.self',
                    'reimbursement.main.find.self',
                    'reimbursement.main.create',
                    'reimbursement.main.update',
                    'reimbursement.main.delete',
                    'reimbursement.log.find.self',
                    'view.application',
                    'view.application.create',
                    'view.application.update',
                ];

                $menus = [
                    'Pengajuan',
                ];
            }

            if ($role->name === 'Manager')
            {
                $modules = [
                    'reimbursement.category.check.limit',
                    'reimbursement.main.index.all',
                    'reimbursement.main.index.approver',
                    'reimbursement.main.index.submitted',
                    'reimbursement.main.find.approver',
                    'reimbursement.main.respond',
                    'reimbursement.log.find.approver',
                    'view.approval',
                    'view.approval.examine',
                    'view.application',
                ];

                $menus = [
                    'Persetujuan',
                    'Pengajuan',
                ];
            }

            if ($role->name !== 'Superadmin')
            {
                // grant menus and modules
                $this->grantModules($modules, $role->id);
                $this->grantMenus($menus, $role->id);
            }

        endforeach;
    }

    //===================================================================================================

    private function grantModules(array $modules, int $roleID)
    {
        $data       = Module::select('id')->whereIn('name', $modules)->get()->toArray();
        $insertData = [];

        foreach ($data as $module):

            array_push($insertData, [
                'role_id'   => $roleID,
                'module_id' => $module['id'],
            ]);

        endforeach;

        // insert
        RoleModuleList::insert($insertData);
    }

    //===================================================================================================

    private function grantMenus(array $menus, int $roleID)
    {
        $data       = Menu::select('id')->whereIn('name', $menus)->get()->toArray();
        $insertData = [];

        foreach ($data as $menu):

            array_push($insertData, [
                'role_id' => $roleID,
                'menu_id' => $menu['id'],
            ]);

        endforeach;

        // insert
        RoleMenuList::insert($insertData);
    }

    //===================================================================================================

    private function createStatuses(): void
    {
        $statuses = [
            ['name' => 'Diajukan', 'action' => 'diajukan', 'template' => 'Pengajuan reimbursement {$name} sudah {$action} oleh {$owner}.'],
            ['name' => 'Dikembalikan', 'action' => 'dikembalikan', 'template' => 'Pengajuan reimbursement {$name} {$action} dan harus direvisi oleh {$owner}.'],
            ['name' => 'Revisi', 'action' => 'direvisi', 'template' => 'Pengajuan reimbursement {$name} sudah {$action} dan diajukan kembali oleh {$owner}.'],
            ['name' => 'Disetujui', 'action' => 'disetujui', 'template' => 'Pengajuan reimbursement {$name} telah {$action} oleh {$approver}.'],
            ['name' => 'Ditolak', 'action' => 'ditolak', 'template' => 'Pengajuan reimbursement {$name} telah {$action} oleh {$approver}.'],
        ];

        ReimbursementStatus::insert($statuses);
    }

    //===================================================================================================
}
