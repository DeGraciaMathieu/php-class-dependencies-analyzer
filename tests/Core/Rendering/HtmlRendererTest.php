<?php

use App\Core\Analysis\AnalysisOptions;
use App\Core\Analysis\AnalysisPipeline;
use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\CycleLevel;
use App\Core\Parser\ClassKind;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Core\Rendering\HtmlRenderer;
use App\Core\Report\Report;
use App\Core\Report\Violation;

/**
 * Decodes the JSON injected in the report-data script element.
 */
function htmlReportData(string $html): array
{
    preg_match('#<script type="application/json" id="report-data">(.*?)</script>#s', $html, $matches);

    return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
}

beforeEach(function () {
    $this->report = (new AnalysisPipeline(new PhpParserClassParser))->run(new AnalysisOptions(
        paths: ['Project'],
        basePath: realpath(dirname(__DIR__, 2).'/Fixtures/Core'),
    ));
});

it('renders a standalone page loading the pinned cytoscape version', function () {
    $html = (new HtmlRenderer)->render($this->report, []);

    expect($html)->toStartWith('<!DOCTYPE html>')
        ->toContain('<script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.21.1/cytoscape.min.js"></script>')
        ->not->toContain('{{');
});

it('injects the summary, components, classes and their dependencies', function () {
    $data = htmlReportData((new HtmlRenderer)->render($this->report, [], [new Violation('no-cycles', 'fingerprint', 'Cycle.')]));

    expect($data['summary'])->toBe([
        'files' => 11,
        'classes' => 11,
        'components' => 3,
        'cycles' => 2,
        'sdpViolations' => 1,
        'ruleViolations' => 1,
        'errors' => 0,
    ])
        ->and(array_column($data['components'], 'name'))->toBe(['Shop\Billing', 'Shop\Catalog', 'Shop\Ordering'])
        ->and($data['classes'][0])->toBe([
            'fqcn' => 'Shop\Billing\HasTaxes',
            'kind' => 'trait',
            'component' => 'Shop\Billing',
            'afferent' => 1,
            'efferent' => 0,
            'external' => 0,
            'instability' => 0.0,
        ])
        ->and($data['componentDependencies']['Shop\Billing'])->toBe(['Shop\Ordering'])
        ->and($data['classDependencies']['Shop\Catalog\Category'])->toBe(['Shop\Catalog\Product'])
        ->and($data['cycles'])->toBe([
            ['level' => 'class', 'members' => ['Shop\Catalog\Category', 'Shop\Catalog\Product']],
            ['level' => 'component', 'members' => ['Shop\Billing', 'Shop\Ordering']],
        ])
        ->and($data['sdp'])->toBe([['from' => 'Shop\Billing', 'to' => 'Shop\Ordering']]);
});

it('renders a null rule violation count when no rule is configured', function () {
    expect(htmlReportData((new HtmlRenderer)->render($this->report, []))['summary']['ruleViolations'])->toBeNull();
});

it('escapes a class name able to close the script element', function () {
    $fqcn = 'App\Evil</script><script>alert("x")</script>&\'';
    $report = new Report(
        classes: [$fqcn => new ClassMetrics($fqcn, ClassKind::Class_, 'Evil.php', 0, 1, 0, 1.0)],
        components: ['App' => new ComponentMetrics('App', [$fqcn], 1, 0, 0.0, 0, 0, null, null)],
        cycles: [new Cycle([$fqcn, 'App\Other'], CycleLevel::Class_)],
        sdpViolations: [],
        errors: [],
        duplicates: [],
        fileCount: 1,
        componentDependencies: ['App' => []],
        classDependencies: [$fqcn => []],
    );

    $html = (new HtmlRenderer)->render($report, []);

    expect(substr_count($html, '</script>'))->toBe(3)
        ->and($html)->not->toContain('<script>alert')
        ->and(htmlReportData($html)['classes'][0]['fqcn'])->toBe($fqcn);
});
