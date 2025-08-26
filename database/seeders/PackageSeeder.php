<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = $this->getPackageData();

        foreach ($packages as $package) {
            Package::firstOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }

    protected function getPackageData(): array
    {
        return [
            [
                'name' => 'Standard',
                'monthly_quota' => 20,
                'base_price' => 25000.00,
                'excess_unit_price' => 5000.00,
                'weekend_unit_multiplier' => 2,
                'timings' => [
                    [
                        'start_time' => '09:00',
                        'end_time' => '17:59',
                        'multiplier' => 1
                    ],
                    [
                        'start_time' => '18:00',
                        'end_time' => '23:59',
                        'multiplier' => 2
                    ],
                    [
                        'start_time' => '00:00',
                        'end_time' => '08:59',
                        'multiplier' => 4
                    ]
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium',
                'monthly_quota' => 50,
                'base_price' => 45000.00,
                'excess_unit_price' => 3000.00,
                'weekend_unit_multiplier' => 2,
                'timings' => [
                    [
                        'start_time' => '09:00',
                        'end_time' => '17:59',
                        'multiplier' => 1
                    ],
                    [
                        'start_time' => '18:00',
                        'end_time' => '23:59',
                        'multiplier' => 2
                    ],
                    [
                        'start_time' => '00:00',
                        'end_time' => '08:59',
                        'multiplier' => 4
                    ]
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro',
                'monthly_quota' => 80,
                'base_price' => 75000.00,
                'excess_unit_price' => 1880.00,
                'weekend_unit_multiplier' => 2,
                'timings' => [
                    [
                        'start_time' => '09:00',
                        'end_time' => '17:59',
                        'multiplier' => 1
                    ],
                    [
                        'start_time' => '18:00',
                        'end_time' => '23:59',
                        'multiplier' => 2
                    ],
                    [
                        'start_time' => '00:00',
                        'end_time' => '08:59',
                        'multiplier' => 4
                    ]
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro Plus',
                'monthly_quota' => 110,
                'base_price' => 100000.00,
                'excess_unit_price' => 910.00,
                'weekend_unit_multiplier' => 2,
                'timings' => [
                    [
                        'start_time' => '09:00',
                        'end_time' => '17:59',
                        'multiplier' => 1
                    ],
                    [
                        'start_time' => '18:00',
                        'end_time' => '23:59',
                        'multiplier' => 2
                    ],
                    [
                        'start_time' => '00:00',
                        'end_time' => '08:59',
                        'multiplier' => 4
                    ]
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
