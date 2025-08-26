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

        User::updateOrCreate(
            ['email' => 'superadmin@developer.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Password!2'),
                'jabatan_id' => $developerJabatan->id ?? null,
            ]
        );
    }
}
