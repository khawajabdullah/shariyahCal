<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Madhhab;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::findOrCreate('admin', 'web');

        $admin = User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@shariyahcal.test')],
            [
                'name' => env('ADMIN_NAME', 'SRB Administrator'),
                'password' => env('ADMIN_PASSWORD', 'pass12345678'),
            ]
        );

        if (! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }

        $this->seedMadhahib();
        $this->seedLanguages();
    }

    protected function seedMadhahib(): void
    {
        $items = [
            ['name' => 'Hanafi', 'sort_order' => 10],
            ['name' => 'Maliki', 'sort_order' => 20],
            ['name' => 'Hanbali', 'sort_order' => 30],
            ['name' => "Shafi'i", 'sort_order' => 40],
            ['name' => 'Comparative', 'sort_order' => 50],
            ['name' => 'Comparative — AAOIFI Sharia Council', 'sort_order' => 60],
        ];

        foreach ($items as $item) {
            Madhhab::query()->updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }

    protected function seedLanguages(): void
    {
        $items = [
            ['name' => 'Arabic', 'code' => 'ar', 'sort_order' => 10],
            ['name' => 'English', 'code' => 'en', 'sort_order' => 20],
            ['name' => 'French', 'code' => 'fr', 'sort_order' => 30],
            ['name' => 'Malay', 'code' => 'ms', 'sort_order' => 40],
            ['name' => 'Urdu', 'code' => 'ur', 'sort_order' => 50],
        ];

        foreach ($items as $item) {
            Language::query()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
