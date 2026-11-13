<?php

use App\Core\Report\BaselineResult;
use App\Core\Report\Violation;
use App\Core\Rules\Baseline;

function baselineViolation(string $fingerprint): Violation
{
    return new Violation('no-cycles', $fingerprint, "Violation {$fingerprint}.");
}

it('keeps only the violations unknown to the baseline', function () {
    $known = baselineViolation('no-cycles:component:A,B');
    $new = baselineViolation('no-cycles:component:C,D');

    expect(Baseline::fromViolations([$known])->apply([$known, $new]))
        ->toEqual(new BaselineResult([$new], 1, []));
});

it('reports the baseline entries matching no violation as stale, sorted', function () {
    $baseline = Baseline::fromFingerprints(['z', 'kept', 'a']);

    expect($baseline->apply([baselineViolation('kept')]))
        ->toEqual(new BaselineResult([], 1, ['a', 'z']));
});

it('reports every violation as new with an empty baseline', function () {
    $violations = [baselineViolation('a'), baselineViolation('b')];

    expect(Baseline::fromFingerprints([])->apply($violations))
        ->toEqual(new BaselineResult($violations, 0, []));
});
