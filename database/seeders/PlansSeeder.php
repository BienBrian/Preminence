<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Pisti SaaS — Default Subscription Plans
     *
     * Modules array maps to App\Services\ModuleService::MODULES
     * Features array covers non-module binary flags
     */
    public function run(): void
    {
        $plans = [
            [
                'name'               => 'Free',
                'slug'               => 'free',
                'price'              => 0.00,
                'billing_cycle'      => 'monthly',
                'max_users'          => 20,
                'max_sms_per_month'  => 50,
                'max_storage_mb'     => 100,
                'trial_days'         => 0,
                'sort_order'         => 1,
                'is_active'          => true,
                'modules'            => [
                    'people'         => true,
                    'attendance'     => true,
                    'events'         => true,
                    'spiritual'      => true,
                    'finance'        => false,
                    'mpesa'          => false,
                    'sms'            => false,
                    'email'          => false,
                    'website'        => false,
                    'shop'           => false,
                    'media'          => false,
                    'reports'        => false,
                    'discipleship'   => false,
                    'api_access'     => false,
                ],
                'features' => [
                    'custom_domain'      => false,
                    'priority_support'   => false,
                    'data_export'        => false,
                ],
            ],
            [
                'name'               => 'Starter',
                'slug'               => 'starter',
                'price'              => 2000.00,
                'billing_cycle'      => 'monthly',
                'max_users'          => 100,
                'max_sms_per_month'  => 500,
                'max_storage_mb'     => 1024,       // 1 GB
                'trial_days'         => 14,
                'sort_order'         => 2,
                'is_active'          => true,
                'modules'            => [
                    'people'         => true,
                    'attendance'     => true,
                    'events'         => true,
                    'spiritual'      => true,
                    'finance'        => true,
                    'mpesa'          => false,
                    'sms'            => true,
                    'email'          => true,
                    'website'        => true,
                    'shop'           => false,
                    'media'          => true,
                    'reports'        => true,
                    'discipleship'   => false,
                    'api_access'     => false,
                ],
                'features' => [
                    'custom_domain'      => false,
                    'priority_support'   => false,
                    'data_export'        => true,
                ],
            ],
            [
                'name'               => 'Pro',
                'slug'               => 'pro',
                'price'              => 5000.00,
                'billing_cycle'      => 'monthly',
                'max_users'          => 500,
                'max_sms_per_month'  => 2000,
                'max_storage_mb'     => 5120,       // 5 GB
                'trial_days'         => 14,
                'sort_order'         => 3,
                'is_active'          => true,
                'modules'            => [
                    'people'         => true,
                    'attendance'     => true,
                    'events'         => true,
                    'spiritual'      => true,
                    'finance'        => true,
                    'mpesa'          => true,
                    'sms'            => true,
                    'email'          => true,
                    'website'        => true,
                    'shop'           => true,
                    'media'          => true,
                    'reports'        => true,
                    'discipleship'   => true,
                    'api_access'     => false,
                ],
                'features' => [
                    'custom_domain'      => false,
                    'priority_support'   => false,
                    'data_export'        => true,
                ],
            ],
            [
                'name'               => 'Enterprise',
                'slug'               => 'enterprise',
                'price'              => 15000.00,
                'billing_cycle'      => 'monthly',
                'max_users'          => 999999,     // Unlimited
                'max_sms_per_month'  => 10000,
                'max_storage_mb'     => 51200,      // 50 GB
                'trial_days'         => 30,
                'sort_order'         => 4,
                'is_active'          => true,
                'modules'            => [
                    'people'         => true,
                    'attendance'     => true,
                    'events'         => true,
                    'spiritual'      => true,
                    'finance'        => true,
                    'mpesa'          => true,
                    'sms'            => true,
                    'email'          => true,
                    'website'        => true,
                    'shop'           => true,
                    'media'          => true,
                    'reports'        => true,
                    'discipleship'   => true,
                    'api_access'     => true,
                ],
                'features' => [
                    'custom_domain'      => true,
                    'priority_support'   => true,
                    'data_export'        => true,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('✅ Plans seeded: Free, Starter, Pro, Enterprise');
    }
}
