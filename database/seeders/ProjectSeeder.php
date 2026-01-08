<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            ['name' => 'Sky Wall', 'description' => 'Sky Wall Project', 'is_active' => true],
            ['name' => 'Eldeco Green', 'description' => 'Eldeco Green Project', 'is_active' => true],
            ['name' => 'Jashn Elevate', 'description' => 'Jashn Elevate Project', 'is_active' => true],
        ];

        foreach ($projects as $project) {
            Project::firstOrCreate(
                ['name' => $project['name']],
                $project
            );
        }
    }
}

