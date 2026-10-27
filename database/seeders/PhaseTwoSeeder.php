<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\PlanningPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhaseTwoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['FASES_POA' => 'Fases POA', 'TIPOS_UNIDAD' => 'Tipos de unidad organizacional', 'TIPOS_ESTRUCTURA_PROGRAMATICA' => 'Tipos de estructura programática'] as $code => $name) {
            Catalog::query()->updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }

        PlanningPeriod::query()->updateOrCreate(['year' => (int) now()->format('Y')], ['starts_at' => now()->startOfYear(), 'ends_at' => now()->endOfYear(), 'is_active' => true]);
    }
}
