<?php

use App\Core\Analysis\AnalysisOptions;
use App\Core\Analysis\AnalysisPipeline;
use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Parser\ClassKind;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Core\Rendering\JsonRenderer;
use App\Core\Rendering\ReportSection;
use App\Core\Report\Report;
use App\Core\Rules\BaselineResult;
use App\Core\Rules\Violation;

beforeEach(function () {
    $this->report = (new AnalysisPipeline(new PhpParserClassParser))->run(new AnalysisOptions(
        paths: ['Project', 'Parser/Invalid.php'],
        basePath: realpath(dirname(__DIR__, 2).'/Fixtures/Core'),
    ));
});

it('renders valid json with the schema version and the summary', function () {
    $data = json_decode((new JsonRenderer)->render($this->report, []), true, flags: JSON_THROW_ON_ERROR);

    expect($data['schema'])->toBe(1)
        ->and($data['summary'])->toBe(['files' => 12, 'classes' => 11, 'errors' => 1])
        ->and($data['errors'])->toHaveCount(1)
        ->and($data['errors'][0]['file'])->toEndWith('Parser/Invalid.php');
});

it('contains only the requested sections', function () {
    $data = json_decode((new JsonRenderer)->render($this->report, [ReportSection::Sdp, ReportSection::Cycles]), true);

    expect(array_keys($data))->toBe(['schema', 'summary', 'cycles', 'sdp', 'violations', 'errors']);
});

it('always renders the violations, empty when no rule is configured', function () {
    $violations = [new Violation('max-distance', 'max-distance:Shop\Catalog', 'Too far.')];

    expect(json_decode((new JsonRenderer)->render($this->report, []), true)['violations'])->toBe([])
        ->and(json_decode((new JsonRenderer)->render($this->report, [], $violations), true)['violations'])->toBe([[
            'rule' => 'max-distance',
            'fingerprint' => 'max-distance:Shop\Catalog',
            'message' => 'Too far.',
            'file' => null,
        ]]);
});

it('renders the baseline counts only when a baseline is used', function () {
    $violations = [new Violation('max-distance', 'max-distance:Shop\Catalog', 'Too far.')];
    $baseline = new BaselineResult($violations, 2, ['no-cycles:component:A,B']);

    $data = json_decode((new JsonRenderer)->render($this->report, [], $violations, $baseline), true);

    expect(array_keys($data))->toBe(['schema', 'summary', 'violations', 'baseline', 'errors'])
        ->and($data['baseline'])->toBe(['baselined' => 2, 'stale' => ['no-cycles:component:A,B']])
        ->and(json_decode((new JsonRenderer)->render($this->report, [], $violations), true))->not->toHaveKey('baseline');
});

it('renders components rounded to 4 decimals', function () {
    $data = json_decode((new JsonRenderer)->render($this->report, [ReportSection::Components]), true);

    expect($data['components'][0])->toBe([
        'name' => 'Shop\Billing',
        'classes' => [
            'Shop\Billing\HasTaxes',
            'Shop\Billing\Invoice',
            'Shop\Billing\PaymentGateway',
            'Shop\Billing\StripeGateway',
        ],
        'classCount' => 3,
        'abstractCount' => 1,
        'abstractness' => 0.3333,
        'afferent' => 1,
        'efferent' => 1,
        'instability' => 0.5,
        'distance' => 0.1667,
    ]);
});

it('renders classes with a stable kebab-case kind', function () {
    $data = json_decode((new JsonRenderer)->render($this->report, [ReportSection::Classes]), true);

    $kinds = array_column($data['classes'], 'kind', 'fqcn');

    expect($kinds['Shop\Billing\HasTaxes'])->toBe('trait')
        ->and($kinds['Shop\Billing\Invoice'])->toBe('class')
        ->and($kinds['Shop\Billing\PaymentGateway'])->toBe('interface')
        ->and($kinds['Shop\Ordering\OrderStatus'])->toBe('enum')
        ->and($data['classes'][2])->toBe([
            'fqcn' => 'Shop\Billing\PaymentGateway',
            'kind' => 'interface',
            'afferent' => 2,
            'efferent' => 1,
            'external' => 0,
            'instability' => 0.3333,
        ]);
});

it('renders an abstract class kind as abstract-class', function () {
    $report = new Report(
        classes: ['App\\Base' => new ClassMetrics('App\\Base', ClassKind::AbstractClass, 'Base.php', 0, 0, 0, 0.0)],
        components: [],
        cycles: [],
        sdpViolations: [],
        errors: [],
        duplicates: [],
        fileCount: 1,
    );

    $data = json_decode((new JsonRenderer)->render($report, [ReportSection::Classes]), true);

    expect($data['classes'][0]['kind'])->toBe('abstract-class');
});

it('renders cycles and sdp violations', function () {
    $data = json_decode((new JsonRenderer)->render($this->report, [ReportSection::Cycles, ReportSection::Sdp]), true);

    expect($data['cycles'][0])->toBe([
        'level' => 'class',
        'size' => 2,
        'members' => ['Shop\Catalog\Category', 'Shop\Catalog\Product'],
    ])->and($data['sdp'])->toBe([[
        'from' => 'Shop\Billing',
        'fromInstability' => 0.5,
        'to' => 'Shop\Ordering',
        'toInstability' => 0.8,
        'delta' => 0.3,
    ]]);
});

it('keeps null for a non-measurable metric and zero fractions', function () {
    $report = new Report(
        classes: [],
        components: [
            'App\Isolated' => new ComponentMetrics('App\Isolated', [], 0, 0, null, 0, 0, null, null),
        ],
        cycles: [],
        sdpViolations: [],
        errors: [],
        duplicates: [],
        fileCount: 0,
    );

    $component = json_decode((new JsonRenderer)->render($report, [ReportSection::Components]), true)['components'][0];

    expect($component['abstractness'])->toBeNull()
        ->and($component['instability'])->toBeNull()
        ->and($component['distance'])->toBeNull()
        ->and((new JsonRenderer)->render($this->report, [ReportSection::Components]))->toContain('"instability": 0.0');
});
