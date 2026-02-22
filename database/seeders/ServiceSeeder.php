<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $createdBy = $admin?->id;

        // Create specific services
        Service::factory()->active()->create([
            'name' => 'Web Development',
            'description' => 'Full-stack web application development including frontend and backend.',
            'created_by' => $createdBy,
        ]);

        Service::factory()->active()->create([
            'name' => 'Mobile App Development',
            'description' => 'Native and cross-platform mobile application development for iOS and Android.',
            'created_by' => $createdBy,
        ]);

        Service::factory()->active()->create([
            'name' => 'Cloud Services',
            'description' => 'Cloud infrastructure setup, migration, and management on AWS, Azure, or GCP.',
            'created_by' => $createdBy,
        ]);

        Service::factory()->active()->create([
            'name' => 'IT Consulting',
            'description' => 'Strategic IT consulting and digital transformation advisory services.',
            'created_by' => $createdBy,
        ]);

        Service::factory()->active()->create([
            'name' => 'UI/UX Design',
            'description' => 'User interface and user experience design for web and mobile applications.',
            'created_by' => $createdBy,
        ]);

        Service::factory()->active()->create([
            'name' => 'Data Analytics',
            'description' => 'Business intelligence, data visualization, and analytics solutions.',
            'created_by' => $createdBy,
        ]);

        Service::factory()->inactive()->create([
            'name' => 'Legacy System Support',
            'description' => 'Maintenance and support for legacy systems (limited availability).',
            'created_by' => $createdBy,
        ]);
    }
}
