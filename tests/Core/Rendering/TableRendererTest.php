<?php

use App\Core\Analysis\AnalysisOptions;
use App\Core\Analysis\AnalysisPipeline;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Core\Rendering\ReportSection;
use App\Core\Rendering\TableRenderer;
use App\Core\Report\BaselineResult;
use App\Core\Report\Report;
use App\Core\Report\Violation;

beforeEach(function () {
    $this->report = (new AnalysisPipeline(new PhpParserClassParser))->run(new AnalysisOptions(
        paths: ['Project', 'Parser/Invalid.php'],
        basePath: realpath(dirname(__DIR__, 2).'/Fixtures/Core'),
    ));
});

it('renders the summary and only the requested sections', function () {
    $output = (new TableRenderer)->render($this->report, [ReportSection::Cycles]);

    expect($output)->toStartWith('Files: 12, classes: 11, errors: 1')
        ->toContain('Cycles')
        ->not->toContain('Components')
        ->not->toContain('Classes')
        ->not->toContain('SDP violations');
});

it('renders the sections in the enum order', function () {
    $output = (new TableRenderer)->render($this->report, [ReportSection::Sdp, ReportSection::Classes]);

    expect(strpos($output, 'Classes'))->toBeLessThan(strpos($output, 'SDP violations'));
});

it('aligns the columns of the components table', function () {
    $output = (new TableRenderer)->render($this->report, [ReportSection::Components]);

    expect($output)->toContain(implode(PHP_EOL, [
        'Components',
        'Component      Nc  Na  A     Ca  Ce  I     D',
        '-------------  --  --  ----  --  --  ----  ----',
        'Shop\Billing   3   1   0.33  1   1   0.50  0.17',
        'Shop\Catalog   3   1   0.33  2   0   0.00  0.67',
        'Shop\Ordering  4   1   0.25  1   4   0.80  0.05',
    ]));
});

it('renders classes, cycles and sdp violations rounded to 2 decimals', function () {
    $output = (new TableRenderer)->render($this->report, ReportSection::cases());

    expect($output)
        ->toContain('Shop\Billing\PaymentGateway     2   1   0    0.33')
        ->toContain('class      2     Shop\Catalog\Category → Shop\Catalog\Product')
        ->toContain('component  2     Shop\Billing → Shop\Ordering')
        ->toContain('Shop\Billing (0.50) → Shop\Ordering (0.80)  0.30');
});

it('lists the parsing errors at the end', function () {
    $output = (new TableRenderer)->render($this->report, ReportSection::defaults());

    expect($output)->toMatch("/Errors\n- .*Invalid\\.php: Syntax error.*\n$/");
});

it('shows a dash for a non-measurable metric and short messages for empty sections', function () {
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

    $output = (new TableRenderer)->render($report, ReportSection::cases());

    expect($output)
        ->toContain('App\Isolated  0   0   —  0   0   —  —')
        ->toContain("Classes\nNo class.")
        ->toContain("Cycles\nNo cycle.")
        ->toContain("SDP violations\nNo SDP violation.")
        ->not->toContain('Errors');
});

it('lists the rule violations before the parsing errors', function () {
    $violations = [new Violation('no-cycles', 'no-cycles:class:A,B', 'Class cycle: A → B.', 'A.php')];

    $output = (new TableRenderer)->render($this->report, [], $violations);

    expect($output)->toContain(implode(PHP_EOL, [
        'Rule violations',
        'Rule       Message',
        '---------  -------------------',
        'no-cycles  Class cycle: A → B.',
    ]))->and(strpos($output, 'Rule violations'))->toBeLessThan(strpos($output, 'Errors'));
});

it('colors the section titles and the rule names when decorated, keeping the columns aligned', function () {
    $violations = [new Violation('no-cycles', 'no-cycles:class:A,B', 'Class cycle: A → B.', 'A.php')];

    $output = (new TableRenderer(decorated: true))->render($this->report, [], $violations);

    expect($output)->toContain(implode(PHP_EOL, [
        "\e[1;33mRule violations\e[0m",
        'Rule       Message',
        '---------  -------------------',
        "\e[31mno-cycles\e[0m  Class cycle: A → B.",
    ]))->and($output)->toContain("\e[1;33mErrors\e[0m");
});

it('plots the components around the main sequence with a legend', function () {
    $output = (new TableRenderer)->render($this->report, [ReportSection::MainSequence]);

    expect($output)->toContain(implode(PHP_EOL, [
        'Main sequence',
        'A',
        '1.0 │· ·',
    ]))->toContain(implode(PHP_EOL, [
        '    │2                   1       · · 3',
        '    │                                · ·',
        '    │                                    · ·',
        '0.0 │                                        ·',
        '    └──────────────────────────────────────────',
        '     0.0                 0.5                 1.0  I',
        '',
        '#  Component      I     A     D     Zone',
        '-  -------------  ----  ----  ----  ----',
        '1  Shop\Billing   0.50  0.33  0.17',
        '2  Shop\Catalog   0.00  0.33  0.67  pain',
        '3  Shop\Ordering  0.80  0.25  0.05',
    ]));
});

it('colors the components in a zone of the main sequence chart when decorated', function () {
    $output = (new TableRenderer(decorated: true))->render($this->report, [ReportSection::MainSequence]);

    expect($output)->toContain("    │\e[31m2 \e[0m                  1       · · 3")
        ->toContain("2  \e[31mShop\\Catalog\e[0m   0.00  0.33  0.67  pain");
});

it('lists a non-measurable component in the legend without plotting it', function () {
    $report = new Report(
        classes: [],
        components: ['App\Isolated' => new ComponentMetrics('App\Isolated', [], 0, 0, null, 0, 0, null, null)],
        cycles: [],
        sdpViolations: [],
        errors: [],
        duplicates: [],
        fileCount: 0,
    );

    expect((new TableRenderer)->render($report, [ReportSection::MainSequence]))
        ->toContain('—  App\Isolated  —  —  —')
        ->not->toMatch('/│.*\d/');
});

it('renders no escape sequence when not decorated', function () {
    $violations = [new Violation('no-cycles', 'no-cycles:class:A,B', 'Class cycle: A → B.', 'A.php')];

    expect((new TableRenderer)->render($this->report, ReportSection::cases(), $violations))->not->toContain("\e[");
});

it('renders the rule violations section only when rules are configured', function () {
    expect((new TableRenderer)->render($this->report, []))->not->toContain('Rule violations')
        ->and((new TableRenderer)->render($this->report, [], []))->toContain("Rule violations\nNo rule violation.");
});

it('renders the baseline counts after the rule violations only when a baseline is used', function () {
    $baseline = new BaselineResult([], 3, ['a', 'b']);

    expect((new TableRenderer)->render($this->report, [], [], $baseline))
        ->toContain("Rule violations\nNo rule violation.\n3 baselined, 2 stale")
        ->and((new TableRenderer)->render($this->report, [], []))->not->toContain('baselined');
});

it('builds sections from a list of names', function () {
    expect(ReportSection::fromList(['sdp', 'classes']))->toBe([ReportSection::Sdp, ReportSection::Classes])
        ->and(ReportSection::defaults())->toBe([ReportSection::Components, ReportSection::Cycles, ReportSection::Sdp]);
});

it('rejects an unknown section', function () {
    ReportSection::fromList(['components', 'graph']);
})->throws(InvalidArgumentException::class, 'Unknown report section "graph", expected one of: classes, components, cycles, sdp, main-sequence.');
