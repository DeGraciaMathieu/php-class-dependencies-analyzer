<?php

use App\Core\Analysis\SdpViolation;
use App\Core\Report\Report;
use App\Core\Report\Violation;
use App\Core\Rules\Rules;

/**
 * @param  list<SdpViolation>  $violations
 */
function reportWithSdpViolations(array $violations): Report
{
    return new Report([], [], [], $violations, [], [], 0);
}

it('reports every sdp violation without tolerance', function () {
    $report = reportWithSdpViolations([new SdpViolation('Billing', 0.5, 'Ordering', 0.8, 0.3)]);

    expect(Rules::stableDependencies()->evaluate($report))->toEqual([
        new Violation(
            'stable-dependencies',
            'stable-dependencies:Billing->Ordering',
            'Billing (I=0.50) depends on the less stable Ordering (I=0.80), delta 0.30.',
            ruleDescription: 'Components depend on more stable components (tolerance 0.00).',
        ),
    ]);
});

it('only reports deltas strictly greater than the tolerance', function () {
    $report = reportWithSdpViolations([
        new SdpViolation('A', 0.5, 'B', 0.7, 0.2),
        new SdpViolation('C', 0.2, 'D', 0.6, 0.4),
        new SdpViolation('E', 0.1, 'F', 0.3, 0.19),
    ]);

    $fingerprints = array_column(Rules::stableDependencies(0.2)->evaluate($report), 'fingerprint');

    expect($fingerprints)->toBe(['stable-dependencies:C->D']);
});

it('keeps the same fingerprint when the measured values change', function () {
    $before = Rules::stableDependencies()->evaluate(reportWithSdpViolations([new SdpViolation('A', 0.5, 'B', 0.8, 0.3)]));
    $after = Rules::stableDependencies()->evaluate(reportWithSdpViolations([new SdpViolation('A', 0.1, 'B', 0.9, 0.8)]));

    expect($after[0]->fingerprint)->toBe($before[0]->fingerprint)
        ->and($after[0]->message)->not->toBe($before[0]->message);
});
