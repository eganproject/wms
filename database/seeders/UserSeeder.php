<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Jabatan; // Import Jabatan model

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $developerJabatan = Jabatan::where('name', 'Developer')->first();
        $adminJabatan = Jabatan::where('name', 'Admin')->first();

        User::updateOrCreate(
            ['email' => 'superadmin@developer.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Password!2'),
                'jabatan_id' => $developerJabatan->id ?? null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'mawar@admin.com'],
            [
                'name' => 'mawar',
                'password' => Hash::make('Password!2'),
                'jabatan_id' => $adminJabatan->id ?? null,
                'warehouse_id' => 1
            ]
        );

        User::updateOrCreate(
            ['email' => 'melati@admin.com'],
            [
                'name' => 'melati',
                'password' => Hash::make('Password!2'),
                'jabatan_id' => $adminJabatan->id ?? null,
                'warehouse_id' => 2
            ]
        );
    }
}
