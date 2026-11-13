<?php

use App\Core\Analysis\AnalysisOptions;
use App\Core\Analysis\AnalysisPipeline;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Core\Rendering\ReportSection;
use App\Core\Rendering\SarifRenderer;
use App\Core\Report\Report;
use App\Core\Report\Violation;

function renderSarif(Report $report, ?array $violations = null): array
{
    $rendered = (new SarifRenderer('1.2.3', 'dependencies.php'))->render($report, ReportSection::defaults(), $violations);

    return json_decode($rendered, true, flags: JSON_THROW_ON_ERROR);
}

beforeEach(function () {
    $this->report = (new AnalysisPipeline(new PhpParserClassParser))->run(new AnalysisOptions(
        paths: ['Project'],
        basePath: realpath(dirname(__DIR__, 2).'/Fixtures/Core'),
    ));

    $this->violations = [
        new Violation('no-cycles', 'no-cycles:class:A,B', 'Class cycle: A → B.', 'src/A.php', 'No cycle.'),
        new Violation('no-cycles', 'no-cycles:component:X,Y', 'Component cycle: X → Y.', ruleDescription: 'No cycle.'),
        new Violation('max-distance', 'max-distance:X', 'Too far.'),
    ];
});

it('renders a sarif 2.1.0 log with the tool driver', function () {
    $sarif = renderSarif($this->report, $this->violations);

    expect($sarif['$schema'])->toBe('https://json.schemastore.org/sarif-2.1.0.json')
        ->and($sarif['version'])->toBe('2.1.0')
        ->and($sarif['runs'])->toHaveCount(1)
        ->and($sarif['runs'][0]['tool']['driver'])->toMatchArray([
            'name' => 'php-class-dependencies-analyzer',
            'version' => '1.2.3',
            'informationUri' => 'https://github.com/DeGraciaMathieu/php-class-dependencies-analyzer',
        ]);
});

it('describes each rule present in the violations once', function () {
    $rules = renderSarif($this->report, $this->violations)['runs'][0]['tool']['driver']['rules'];

    expect($rules)->toBe([
        ['id' => 'no-cycles', 'shortDescription' => ['text' => 'No cycle.']],
        ['id' => 'max-distance', 'shortDescription' => ['text' => 'max-distance']],
    ]);
});

it('renders one error result per violation with its fingerprint', function () {
    $results = renderSarif($this->report, $this->violations)['runs'][0]['results'];

    expect($results)->toHaveCount(3)
        ->and($results[0])->toBe([
            'ruleId' => 'no-cycles',
            'level' => 'error',
            'message' => ['text' => 'Class cycle: A → B.'],
            'locations' => [['physicalLocation' => ['artifactLocation' => ['uri' => 'src/A.php']]]],
            'partialFingerprints' => ['dependencyViolation/v1' => 'no-cycles:class:A,B'],
        ]);
});

it('locates a violation without file in the fallback location', function () {
    $results = renderSarif($this->report, $this->violations)['runs'][0]['results'];

    expect($results[1]['locations'][0]['physicalLocation']['artifactLocation']['uri'])->toBe('dependencies.php')
        ->and($results[2]['locations'][0]['physicalLocation']['artifactLocation']['uri'])->toBe('dependencies.php');
});

it('renders the parsing errors as warnings of the parse-error rule', function () {
    $report = (new AnalysisPipeline(new PhpParserClassParser))->run(new AnalysisOptions(
        paths: ['Parser/Invalid.php'],
        basePath: realpath(dirname(__DIR__, 2).'/Fixtures/Core'),
    ));

    $run = renderSarif($report, $this->violations)['runs'][0];
    $error = end($run['results']);

    expect(array_column($run['tool']['driver']['rules'], 'id'))->toBe(['no-cycles', 'max-distance', 'parse-error'])
        ->and($error['ruleId'])->toBe('parse-error')
        ->and($error['level'])->toBe('warning')
        ->and($error['locations'][0]['physicalLocation']['artifactLocation']['uri'])->toBe('Parser/Invalid.php');
});

it('gives every result a rule declared by the driver and a physical location', function () {
    $run = renderSarif($this->report, $this->violations)['runs'][0];
    $ruleIds = array_column($run['tool']['driver']['rules'], 'id');

    foreach ($run['results'] as $result) {
        expect($ruleIds)->toContain($result['ruleId'])
            ->and($result['locations'][0]['physicalLocation']['artifactLocation']['uri'])->toBeString()->not->toBeEmpty();
    }
});

it('renders no result without violation nor error', function (?array $violations) {
    $run = renderSarif($this->report, $violations)['runs'][0];

    expect($run['results'])->toBe([])
        ->and($run['tool']['driver']['rules'])->toBe([]);
})->with([
    'no rule configured' => [null],
    'every rule passes' => [[]],
]);
