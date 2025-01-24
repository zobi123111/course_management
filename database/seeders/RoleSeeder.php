<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::truncate();
        $roles = [
            [
                'role_name'     => "Administrator"
            ],
            [
                'role_name'     => "Instructor"
            ],
            [
                'role_name'     => "Student"
            ]
     

        ];
    
        DB::table('roles')->insert($roles);
    }
}
