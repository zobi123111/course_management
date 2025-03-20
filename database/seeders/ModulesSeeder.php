<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Module; 

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Module::truncate();
        DB::table('modules')->insert([
            ['name' => 'List', 'route_name' => 'dashboard', 'page_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['name' => 'List', 'route_name' => 'user.index', 'page_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Add', 'route_name' => 'user.store', 'page_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'user.get', 'page_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'user.destroy', 'page_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['name' => 'List', 'route_name' => 'roles.index', 'page_id' => 4, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Add', 'route_name' => 'roles.create', 'page_id' => 4, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'roles.edit', 'page_id' => 4, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'roles.destroy', 'page_id' => 4, 'created_at' => null, 'updated_at' => null],
            ['name' => 'List', 'route_name' => 'orgunit.index', 'page_id' => 5, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Add', 'route_name' => 'orgunit.store', 'page_id' => 5, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'orgunit.edit', 'page_id' => 5, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'orgunit.delete', 'page_id' => 5, 'created_at' => null, 'updated_at' => null],
            ['name' => 'List', 'route_name' => 'course.index', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Add', 'route_name' => 'course.store', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Show', 'route_name' => 'course.show', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'course.edit', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'course.delete', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Lesson Add', 'route_name' => 'lesson.store', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Lesson Show', 'route_name' => 'lesson.show', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Lesson Edit', 'route_name' => 'lesson.edit', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Lesson Delete', 'route_name' => 'lesson.delete', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'sub-lesson List', 'route_name' => 'sublesson.index', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'sub-lesson Add', 'route_name' => 'sublesson.store', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'sub-lesson Edit', 'route_name' => 'sublesson.edit', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'sub-lesson Delete', 'route_name' => 'sublesson.delete', 'page_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['name' => 'ADD', 'route_name' => 'group.store', 'page_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['name' => 'List', 'route_name' => 'group.index', 'page_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'group.edit', 'page_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'group.delete', 'page_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['name' => 'List', 'route_name' => 'document.index', 'page_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Add', 'route_name' => 'document.store', 'page_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Show', 'route_name' => 'document.show', 'page_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'document.edit', 'page_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'document.delete', 'page_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['name' => 'List', 'route_name' => 'folder.index', 'page_id' => 9, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Add', 'route_name' => 'folder.store', 'page_id' => 9, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'folder.edit', 'page_id' => 9, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'folder.delete', 'page_id' => 9, 'created_at' => null, 'updated_at' => null],

            ['name' => 'Add', 'route_name' => 'resource.store', 'page_id' => 10, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Edit', 'route_name' => 'resource.edit', 'page_id' => 10, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Delete', 'route_name' => 'resource.delete', 'page_id' => 10, 'created_at' => null, 'updated_at' => null],
        ]);
    }
}
