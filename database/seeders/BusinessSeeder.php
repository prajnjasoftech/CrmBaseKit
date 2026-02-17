<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        // Create specific test businesses
        Business::factory()->active()->create([
            'name' => 'Acme Corporation',
            'email' => 'contact@acme.example.com',
            'industry' => 'Technology',
        ]);

        Business::factory()->active()->create([
            'name' => 'Global Healthcare Inc',
            'email' => 'info@globalhealth.example.com',
            'industry' => 'Healthcare',
        ]);

        Business::factory()->pending()->create([
            'name' => 'Startup Ventures',
            'email' => 'hello@startupventures.example.com',
            'industry' => 'Finance',
        ]);

        // Create random businesses
        Business::factory()->count(7)->create();
    }
}
