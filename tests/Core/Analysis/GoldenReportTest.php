<?php

use App\Core\Analysis\AnalysisOptions;
use App\Core\Analysis\AnalysisPipeline;
use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\SdpViolation;
use App\Core\Parser\ParseError;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Core\Report\Report;

/**
 * Floats are rounded here only, so that the golden file stays readable and stable.
 */
function normalizeReport(Report $report): array
{
    return [
        'fileCount' => $report->fileCount,
        'classes' => array_map(static fn (ClassMetrics $class): array => [
            'kind' => $class->kind->name,
            'file' => $class->file,
            'afferent' => $class->afferent,
            'efferent' => $class->efferent,
            'externalCount' => $class->externalCount,
            'instability' => round($class->instability, 4),
        ], $report->classes),
        'components' => array_map(static fn (ComponentMetrics $component): array => [
            'classes' => $component->classes,
            'classCount' => $component->classCount,
            'abstractCount' => $component->abstractCount,
            'abstractness' => round($component->abstractness, 4),
            'afferent' => $component->afferent,
            'efferent' => $component->efferent,
            'instability' => round($component->instability, 4),
            'distance' => round($component->distance, 4),
        ], $report->components),
        'cycles' => array_map(static fn (Cycle $cycle): array => [
            'level' => $cycle->level->value,
            'members' => $cycle->members,
        ], $report->cycles),
        'sdpViolations' => array_map(static fn (SdpViolation $violation): array => [
            'from' => $violation->from,
            'fromInstability' => round($violation->fromInstability, 4),
            'to' => $violation->to,
            'toInstability' => round($violation->toInstability, 4),
            'delta' => round($violation->delta, 4),
        ], $report->sdpViolations),
        'errors' => array_map(static fn (ParseError $error): array => [
            'file' => $error->file,
            'message' => $error->message,
        ], $report->errors),
        'duplicates' => $report->duplicates,
    ];
}

it('matches the golden report of the fixture project', function () {
    $basePath = realpath(dirname(__DIR__, 2).'/Fixtures/Core');
    $goldenFile = __DIR__.'/golden-report.json';

    $report = (new AnalysisPipeline(new PhpParserClassParser))->run(new AnalysisOptions(
        paths: ['Project', 'Parser/Invalid.php'],
        basePath: $basePath,
    ));

    $json = json_encode(normalizeReport($report), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION).PHP_EOL;

    if (getenv('UPDATE_GOLDEN') === '1') {
        file_put_contents($goldenFile, $json);
    }

    expect($json)->toBe(file_get_contents($goldenFile));
});
