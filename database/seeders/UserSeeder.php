<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@developer.com',
            'password' => Hash::make('Password!2'),
            'jabatan_id' => $developerJabatan->id ?? null, // Assign jabatan_id
        ]);
    }
}
