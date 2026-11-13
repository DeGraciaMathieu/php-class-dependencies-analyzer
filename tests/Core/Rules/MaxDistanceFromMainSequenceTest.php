<?php

use App\Core\Analysis\ComponentMetrics;
use App\Core\Report\Report;
use App\Core\Report\Violation;
use App\Core\Rules\Rules;

/**
 * @param  array<string, ?float>  $distances  Component name => distance
 */
function reportWithDistances(array $distances): Report
{
    $components = [];

    foreach ($distances as $name => $distance) {
        $components[$name] = new ComponentMetrics($name, [], 1, 0, 0.0, 1, 1, 0.5, $distance);
    }

    return new Report([], $components, [], [], [], [], 0);
}

it('reports components strictly further than the maximum', function () {
    $report = reportWithDistances(['A' => 0.5, 'B' => 0.6667, 'C' => 0.2]);

    expect(Rules::maxDistanceFromMainSequence(0.5)->evaluate($report))->toEqual([
        new Violation(
            'max-distance',
            'max-distance:B',
            'B is at distance 0.67 from the main sequence (max 0.50).',
            ruleDescription: 'Components stay within 0.50 of the main sequence.',
        ),
    ]);
});

it('ignores a non-measurable distance', function () {
    expect(Rules::maxDistanceFromMainSequence(0.0)->evaluate(reportWithDistances(['A' => null])))->toBe([]);
});

it('restricts the checked components with for() without changing the original rule', function () {
    $report = reportWithDistances(['A' => 0.9, 'B' => 0.8, 'C' => 0.7]);
    $rule = Rules::maxDistanceFromMainSequence(0.5);

    $fingerprints = static fn (array $violations): array => array_column($violations, 'fingerprint');

    expect($fingerprints($rule->for('B')->evaluate($report)))->toBe(['max-distance:B'])
        ->and($fingerprints($rule->for(['A', 'C'])->evaluate($report)))->toBe(['max-distance:A', 'max-distance:C'])
        ->and($fingerprints($rule->evaluate($report)))->toBe(['max-distance:A', 'max-distance:B', 'max-distance:C']);
});

it('keeps the same fingerprint when the distance changes', function () {
    $rule = Rules::maxDistanceFromMainSequence(0.5);

    expect($rule->evaluate(reportWithDistances(['A' => 0.9]))[0]->fingerprint)
        ->toBe($rule->evaluate(reportWithDistances(['A' => 0.6]))[0]->fingerprint);
});
