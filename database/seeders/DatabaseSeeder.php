<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Folder;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed plans
        $this->call(SubscriptionPlanSeeder::class);

        $proPlan = SubscriptionPlan::where('slug', 'professional')->first();

        // 2. Demo Company: Global Import & Export SARL
        $company = Company::firstOrCreate(
            ['email' => 'contact@globalimport.com'],
            [
                'name'      => 'Global Import & Export SARL',
                'phone'     => '+212 600-000000',
                'address'   => '120 Boulevard Zerktouni, Casablanca',
                'country'   => 'Morocco',
                'plan_id'   => $proPlan->id,
                'is_active' => true,
            ]
        );

        // 3. Demo Owner User
        $owner = User::firstOrCreate(
            ['email' => 'owner@docuscan.test'],
            [
                'company_id' => $company->id,
                'name'       => 'Karim Benali',
                'password'   => Hash::make('password123'),
                'role'       => User::ROLE_OWNER,
                'phone'      => '+212 611-111111',
                'is_active'  => true,
            ]
        );

        // 4. Demo Employee User
        User::firstOrCreate(
            ['email' => 'sarah@docuscan.test'],
            [
                'company_id' => $company->id,
                'name'       => 'Sarah Amrani',
                'password'   => Hash::make('password123'),
                'role'       => User::ROLE_EMPLOYEE,
                'phone'      => '+212 622-222222',
                'is_active'  => true,
            ]
        );

        // 5. Default Business Folders requested by user
        $folders = [
            ['name' => 'Factures',         'color' => '#10b981', 'icon' => 'receipt',     'description' => 'Invoices & Billing receipts'],
            ['name' => 'Documents Douane', 'color' => '#ef4444', 'icon' => 'shield',      'description' => 'Customs declarations & clearance certificates'],
            ['name' => 'Fournisseurs',     'color' => '#f59e0b', 'icon' => 'truck',       'description' => 'Supplier contracts, purchase orders and manifests'],
            ['name' => 'Transport',        'color' => '#3b82f6', 'icon' => 'navigation', 'description' => 'Bills of Lading, CMR, freight shipping notes'],
            ['name' => 'Contrats',         'color' => '#8b5cf6', 'icon' => 'file-text',  'description' => 'Legal agreements & commercial contracts'],
        ];

        foreach ($folders as $folderData) {
            Folder::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'name'       => $folderData['name'],
                ],
                [
                    'user_id'     => $owner->id,
                    'color'       => $folderData['color'],
                    'icon'        => $folderData['icon'],
                    'description' => $folderData['description'],
                ]
            );
        }
    }
}
