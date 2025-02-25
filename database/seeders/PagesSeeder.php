<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'id' => 1,
                'name' => 'Dashboard',
                'position' => 1,
                'route_name' => 'dashboard',
                'icon' => 'bi bi-grid',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'name' => 'Users',
                'position' => 2,
                'route_name' => 'users',
                'icon' => 'bi bi-person',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'name' => 'Roles',
                'position' => 3,
                'route_name' => 'roles',
                'icon' => 'bi bi-person',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'name' => 'Organization Units',
                'position' => 4,
                'route_name' => 'orgunit',
                'icon' => 'bi bi-person',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'name' => 'Courses',
                'position' => 5,
                'route_name' => 'courses',
                'icon' => 'bi bi-person',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'name' => 'Groups',
                'position' => 6,
                'route_name' => 'groups',
                'icon' => 'bi bi-person',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'name' => 'Documents',
                'position' => 7,
                'route_name' => 'documents',
                'icon' => 'bi bi-person',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'name' => 'Folders',
                'position' => 8,
                'route_name' => 'folders',
                'icon' => 'bi bi-person',
                'parent_page_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
