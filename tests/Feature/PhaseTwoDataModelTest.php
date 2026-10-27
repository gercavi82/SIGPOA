<?php

use App\Models\Catalog;
use App\Models\CatalogItem;
use App\Models\PlanningPeriod;
use Illuminate\Database\QueryException;

test('catalog codes are unique within a catalog', function () {
    $catalog = Catalog::query()->create(['code' => 'FUENTES', 'name' => 'Fuentes']);
    CatalogItem::query()->create(['catalog_id' => $catalog->id, 'code' => '001', 'name' => 'Fiscal']);
    expect(fn () => CatalogItem::query()->create(['catalog_id' => $catalog->id, 'code' => '001', 'name' => 'Duplicada']))->toThrow(QueryException::class);
});

test('planning period casts dates and active flag', function () {
    $period = PlanningPeriod::query()->create(['year' => 2029, 'starts_at' => '2029-01-01', 'ends_at' => '2029-12-31', 'is_active' => true]);
    expect($period->starts_at->year)->toBe(2029)->and($period->is_active)->toBeTrue();
});
