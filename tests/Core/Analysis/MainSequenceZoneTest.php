<?php

use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\MainSequenceZone;

function zoneComponent(?float $abstractness, ?float $instability): ComponentMetrics
{
    return new ComponentMetrics('App', [], 1, 0, $abstractness, 1, 1, $instability, null);
}

it('places a component far from the main sequence in its zone', function (?float $abstractness, ?float $instability, ?MainSequenceZone $zone) {
    expect(MainSequenceZone::of(zoneComponent($abstractness, $instability)))->toBe($zone);
})->with([
    'stable and concrete' => [0.0, 0.0, MainSequenceZone::Pain],
    'unstable and abstract' => [1.0, 1.0, MainSequenceZone::Uselessness],
    'on the main sequence' => [0.0, 1.0, null],
    'at distance 0.5 exactly' => [0.25, 0.25, null],
    'just past distance 0.5' => [0.2, 0.25, MainSequenceZone::Pain],
    'abstractness not measurable' => [null, 0.0, null],
    'instability not measurable' => [0.0, null, null],
]);
