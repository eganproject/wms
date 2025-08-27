<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Jabatan;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dashboard
        $dashboard = Menu::updateOrCreate(
            ['name' => 'Dashboard'],
            [
                'url' => '/admin/dashboard',
                'icon' => 'fas fa-fire',
                'order' => 1,
            ]
        );

        // Masterdata
        $masterdata = Menu::updateOrCreate(
            ['name' => 'Masterdata'],
            [
                'url' => null,
                'icon' => 'fas fa-database',
                'order' => 2,
            ]
        );

        // Children of Masterdata
        $userMenu = Menu::updateOrCreate(
            ['name' => 'Users'],
            [
                'url' => '/admin/users',
                'icon' => 'fas fa-users',
                'parent_id' => $masterdata->id,
                'order' => 1,
            ]
        );

        $jabatanMenu = Menu::updateOrCreate(
            ['name' => 'Jabatan'],
            [
                'url' => '/admin/jabatans',
                'icon' => 'fas fa-briefcase',
                'parent_id' => $masterdata->id,
                'order' => 2,
            ]
        );

        $permissionMenu = Menu::updateOrCreate(
            ['name' => 'Permissions'],
            [
                'url' => '/admin/permissions',
                'icon' => 'fas fa-key',
                'parent_id' => $masterdata->id,
                'order' => 3,
            ]
        );

        $menusMenu = Menu::updateOrCreate(
            ['name' => 'Menus'],
            [
                'url' => '/admin/menus',
                'icon' => 'fas fa-bars',
                'parent_id' => $masterdata->id,
                'order' => 4,
            ]
        );

        $warehouseMenu = Menu::updateOrCreate(
            ['name' => 'Warehouses'],
            [
                'url' => '/admin/warehouses',
                'icon' => 'fas fa-warehouse',
                'parent_id' => $masterdata->id,
                'order' => 5,
            ]
        );

        // Assign permissions to a role
        $developerJabatan = Jabatan::where('name', 'Developer')->first();

        if ($developerJabatan) {
            $menus = Menu::all();
            foreach ($menus as $menu) {
                Permission::updateOrCreate([
                    'jabatan_id' => $developerJabatan->id,
                    'menu_id' => $menu->id,
                ], [
                    'can_read' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                    'can_approve' => true,
                ]);
            }
        }
    }
}
