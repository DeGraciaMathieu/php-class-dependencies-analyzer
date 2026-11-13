<?php

use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\CycleLevel;
use App\Core\Parser\ClassKind;
use App\Core\Report\Report;
use App\Core\Report\Violation;
use App\Core\Rules\Rules;

/**
 * @param  list<Cycle>  $cycles
 * @param  array<string, ClassMetrics>  $classes
 */
function reportWithCycles(array $cycles, array $classes = []): Report
{
    return new Report($classes, [], $cycles, [], [], [], 0);
}

it('reports one violation per class and component cycle', function () {
    $report = reportWithCycles(
        cycles: [
            new Cycle(['App\A', 'App\B'], CycleLevel::Class_),
            new Cycle(['Billing', 'Ordering'], CycleLevel::Component),
        ],
        classes: [
            'App\A' => new ClassMetrics('App\A', ClassKind::Class_, 'src/A.php', 1, 1, 0, 0.5),
            'App\B' => new ClassMetrics('App\B', ClassKind::Class_, 'src/B.php', 1, 1, 0, 0.5),
        ],
    );

    expect(Rules::noCycles()->evaluate($report))->toEqual([
        new Violation('no-cycles', 'no-cycles:class:App\A,App\B', 'Class cycle: App\A → App\B.', 'src/A.php', 'No dependency cycle between classes or components.'),
        new Violation('no-cycles', 'no-cycles:component:Billing,Ordering', 'Component cycle: Billing → Ordering.', ruleDescription: 'No dependency cycle between classes or components.'),
    ]);
});

it('uses the file of the first reported member of a class cycle', function () {
    $report = reportWithCycles(
        cycles: [new Cycle(['App\A', 'App\B'], CycleLevel::Class_)],
        classes: ['App\B' => new ClassMetrics('App\B', ClassKind::Class_, 'src/B.php', 1, 1, 0, 0.5)],
    );

    expect(Rules::noCycles()->evaluate($report)[0]->file)->toBe('src/B.php');
});

it('reports nothing without cycle', function () {
    expect(Rules::noCycles()->evaluate(reportWithCycles([])))->toBe([])
        ->and(Rules::noCycles()->id())->toBe('no-cycles');
});
