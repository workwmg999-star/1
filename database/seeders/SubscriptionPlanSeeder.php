<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'             => 'Free',
                'slug'             => 'free',
                'max_storage_gb'   => 5,
                'max_users'        => 2,
                'max_documents'    => 500,
                'max_file_size_mb' => 10,
                'price_monthly'    => 0.00,
                'price_yearly'     => 0.00,
                'features'         => [
                    '5 GB Cloud Storage',
                    'Up to 2 team members',
                    '500 scanned documents',
                    'Basic camera scan & auto-crop',
                    'PDF export',
                ],
                'is_active'        => true,
                'sort_order'       => 1,
            ],
            [
                'name'             => 'Starter',
                'slug'             => 'starter',
                'max_storage_gb'   => 20,
                'max_users'        => 5,
                'max_documents'    => 2000,
                'max_file_size_mb' => 25,
                'price_monthly'    => 19.00,
                'price_yearly'     => 190.00,
                'features'         => [
                    '20 GB Cloud Storage',
                    'Up to 5 team members',
                    '2,000 scanned documents',
                    'Advanced document filters & contrast',
                    'Multi-page PDF compilation',
                    'Folder hierarchy & tag metadata',
                ],
                'is_active'        => true,
                'sort_order'       => 2,
            ],
            [
                'name'             => 'Professional',
                'slug'             => 'professional',
                'max_storage_gb'   => 100,
                'max_users'        => 20,
                'max_documents'    => -1, // unlimited
                'max_file_size_mb' => 50,
                'price_monthly'    => 49.00,
                'price_yearly'     => 490.00,
                'features'         => [
                    '100 GB Cloud Storage',
                    'Up to 20 team members',
                    'Unlimited documents',
                    'Full-text search & OCR support',
                    'Custom metadata & audit logs',
                    'Priority support',
                ],
                'is_active'        => true,
                'sort_order'       => 3,
            ],
            [
                'name'             => 'Enterprise',
                'slug'             => 'enterprise',
                'max_storage_gb'   => 500,
                'max_users'        => -1, // unlimited
                'max_documents'    => -1, // unlimited
                'max_file_size_mb' => 100,
                'price_monthly'    => 149.00,
                'price_yearly'     => 1490.00,
                'features'         => [
                    '500 GB Cloud Storage',
                    'Unlimited team members',
                    'Unlimited documents',
                    'Dedicated S3 bucket integration',
                    'Custom retention policies & API access',
                    '24/7 dedicated support',
                ],
                'is_active'        => true,
                'sort_order'       => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
