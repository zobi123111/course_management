<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        $data = [
            [
                'fname' => "Rohit",
                'lname' => "",
                'email' => 'sandhu065@gmail.com',
                'password' => Hash::make('123456'),
                'role'   =>   "1"
            ]
        ];
        DB::table('users')->insert($data);
    }
}
