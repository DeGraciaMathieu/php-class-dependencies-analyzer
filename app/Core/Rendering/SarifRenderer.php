<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Parser\ParseError;
use App\Core\Report\BaselineResult;
use App\Core\Report\Report;
use App\Core\Report\Violation;

/**
 * SARIF 2.1.0 log for code scanning (GitHub, GitLab): only the rule violations (the new ones when a baseline is used)
 * and the parsing errors, the metric sections are ignored.
 *
 * - one "error" result per violation, located in its file or, without file (component level), in the fallback location
 * - one "warning" result per parsing error, rule "parse-error"
 * - tool.driver.rules lists the rules present in the results, described by Violation::$ruleDescription
 */
final readonly class SarifRenderer implements Renderer
{
    private const SCHEMA = 'https://json.schemastore.org/sarif-2.1.0.json';

    private const VERSION = '2.1.0';

    private const TOOL_NAME = 'php-class-dependencies-analyzer';

    private const INFORMATION_URI = 'https://github.com/DeGraciaMathieu/php-class-dependencies-analyzer';

    private const PARSE_ERROR_RULE = 'parse-error';

    private const FINGERPRINT_KEY = 'dependencyViolation/v1';

    /**
     * @param  string  $fallbackLocation  Path relative to the project root, used by the violations without file
     */
    public function __construct(
        private string $toolVersion,
        private string $fallbackLocation,
    ) {}

    public function render(Report $report, array $sections, ?array $violations = null, ?BaselineResult $baseline = null): string
    {
        $violations ??= [];

        $log = [
            '$schema' => self::SCHEMA,
            'version' => self::VERSION,
            'runs' => [[
                'tool' => [
                    'driver' => [
                        'name' => self::TOOL_NAME,
                        'version' => $this->toolVersion,
                        'informationUri' => self::INFORMATION_URI,
                        'rules' => $this->rules($violations, $report->errors),
                    ],
                ],
                'results' => [
                    ...array_map($this->violationResult(...), $violations),
                    ...array_map($this->parseErrorResult(...), $report->errors),
                ],
            ]],
        ];

        return json_encode(
            $log,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        ).PHP_EOL;
    }

    /**
     * One entry per rule present in the results, in order of first appearance.
     *
     * @param  list<Violation>  $violations
     * @param  list<ParseError>  $errors
     * @return list<array<string, mixed>>
     */
    private function rules(array $violations, array $errors): array
    {
        $rules = [];

        foreach ($violations as $violation) {
            $rules[$violation->rule] ??= $this->rule($violation->rule, $violation->ruleDescription ?? $violation->rule);
        }

        if ($errors !== []) {
            $rules[self::PARSE_ERROR_RULE] = $this->rule(self::PARSE_ERROR_RULE, 'The file cannot be parsed.');
        }

        return array_values($rules);
    }

    /**
     * @return array<string, mixed>
     */
    private function rule(string $id, string $description): array
    {
        return [
            'id' => $id,
            'shortDescription' => ['text' => $description],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function violationResult(Violation $violation): array
    {
        return [
            'ruleId' => $violation->rule,
            'level' => 'error',
            'message' => ['text' => $violation->message],
            'locations' => $this->locations($violation->file ?? $this->fallbackLocation),
            'partialFingerprints' => [self::FINGERPRINT_KEY => $violation->fingerprint],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseErrorResult(ParseError $error): array
    {
        return [
            'ruleId' => self::PARSE_ERROR_RULE,
            'level' => 'warning',
            'message' => ['text' => $error->message],
            'locations' => $this->locations($error->file),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function locations(string $file): array
    {
        return [['physicalLocation' => ['artifactLocation' => ['uri' => $file]]]];
    }
}
