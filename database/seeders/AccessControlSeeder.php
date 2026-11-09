<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.access' => 'Access administration',
            'admin.dashboard.view' => 'View admin dashboard',
            'users.manage' => 'Manage users',
            'sellers.review' => 'Review seller applications',
            'catalog.manage' => 'Manage catalog',
            'banners.manage' => 'Manage homepage banners',
            'homepage.manage' => 'Manage homepage layout and product shelves',
            'appearance.manage' => 'Manage site header and footer appearance',
            'orders.manage' => 'Manage orders',
            'finance.read' => 'Read financial reports',
            'finance.post' => 'Post financial journals',
            'themes.manage' => 'Manage themes',
            'extensions.review' => 'Review extensions',
            'pages.publish' => 'Publish pages',
            'settings.manage' => 'Manage settings',
        ];

        foreach ($permissions as $key => $name) {
            Permission::firstOrCreate(['key' => $key], ['name' => $name]);
        }

        $role = Role::firstOrCreate(
            ['key' => 'super-admin'],
            ['name' => 'Super Administrator', 'description' => 'Initial platform administrator', 'is_system' => true]
        );

        $role->permissions()->syncWithoutDetaching(Permission::pluck('id')->all());
    }
}
