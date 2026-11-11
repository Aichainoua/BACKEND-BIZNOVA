<?php

namespace App\Features\ReferenceData\Database\Seeders;

use App\Features\ReferenceData\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        Sector::query()->upsert([
            ['code' => 'commerce', 'label' => 'Commerce'],
            ['code' => 'digital', 'label' => 'Numérique'],
            ['code' => 'services', 'label' => 'Services'],
        ], ['code'], ['label']);
    }
}
