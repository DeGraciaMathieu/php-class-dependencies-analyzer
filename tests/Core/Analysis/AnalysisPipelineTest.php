<?php

use App\Core\Analysis\AnalysisOptions;
use App\Core\Analysis\AnalysisPipeline;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\CycleLevel;
use App\Core\Analysis\SdpViolation;
use App\Core\Graph\Filter;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Core\Report\Report;

function runPipeline(array $paths = ['Project'], Filter $filter = new Filter): Report
{
    $options = new AnalysisOptions(
        paths: $paths,
        basePath: dirname(__DIR__, 2).'/Fixtures/Core',
        filter: $filter,
    );

    return (new AnalysisPipeline(new PhpParserClassParser))->run($options);
}

it('reports every entry without filter', function () {
    $report = runPipeline();

    expect($report->fileCount)->toBe(11)
        ->and($report->classes)->toHaveCount(11)
        ->and(array_keys($report->components))->toBe(['Shop\Billing', 'Shop\Catalog', 'Shop\Ordering'])
        ->and($report->cycles)->toHaveCount(2)
        ->and($report->sdpViolations)->toHaveCount(1)
        ->and($report->errors)->toBe([])
        ->and($report->duplicates)->toBe([]);
});

it('computes metrics on the full graph before filtering', function () {
    $full = runPipeline();
    $filtered = runPipeline(filter: new Filter(only: ['Shop\Catalog\*']));

    expect(array_keys($filtered->classes))->toBe(['Shop\Catalog\Category', 'Shop\Catalog\Product', 'Shop\Catalog\ProductRepository'])
        ->and($filtered->classes['Shop\Catalog\Product']->afferent)->toBe(3)
        ->and($filtered->classes['Shop\Catalog\Product'])->toEqual($full->classes['Shop\Catalog\Product'])
        ->and($filtered->components['Shop\Catalog'])->toEqual($full->components['Shop\Catalog']);
});

it('keeps components having at least one retained class', function () {
    $report = runPipeline(filter: new Filter(only: ['Shop\Ordering\PlaceOrder', 'Shop\Billing\HasTaxes']));

    expect(array_keys($report->components))->toBe(['Shop\Billing', 'Shop\Ordering']);
});

it('keeps cycles having at least one retained member', function () {
    $members = static fn (Report $report): array => array_map(
        static fn (Cycle $cycle): array => [$cycle->level, $cycle->members],
        $report->cycles,
    );

    expect($members(runPipeline(filter: new Filter(only: ['Shop\Catalog\Product']))))->toBe([
        [CycleLevel::Class_, ['Shop\Catalog\Category', 'Shop\Catalog\Product']],
    ])->and($members(runPipeline(filter: new Filter(only: ['Shop\Billing\*']))))->toBe([
        [CycleLevel::Component, ['Shop\Billing', 'Shop\Ordering']],
    ]);
});

it('keeps sdp violations whose source or target is retained', function (string $only, int $count) {
    $report = runPipeline(filter: new Filter(only: [$only]));

    expect(array_map(static fn (SdpViolation $violation): string => $violation->from.' -> '.$violation->to, $report->sdpViolations))
        ->toBe(array_fill(0, $count, 'Shop\Billing -> Shop\Ordering'));
})->with([
    'source retained' => ['Shop\Billing\*', 1],
    'target retained' => ['Shop\Ordering\*', 1],
    'neither retained' => ['Shop\Catalog\*', 0],
]);

it('reports invalid files relative to the base path and still analyses the others', function () {
    $report = runPipeline(['Project', 'Parser/Invalid.php']);

    expect($report->fileCount)->toBe(12)
        ->and($report->errors)->toHaveCount(1)
        ->and($report->errors[0]->file)->toBe('Parser/Invalid.php')
        ->and($report->classes)->toHaveCount(11);
});
