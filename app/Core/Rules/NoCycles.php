<?php

declare(strict_types=1);

namespace App\Core\Rules;

use App\Core\Analysis\Cycle;
use App\Core\Analysis\CycleLevel;
use App\Core\Report\Report;

/**
 * One violation per reported cycle, between classes or between components.
 * Fingerprint: "no-cycles:class:A,B" or "no-cycles:component:X,Y" (sorted members).
 */
final readonly class NoCycles implements Rule
{
    private const ID = 'no-cycles';

    public function id(): string
    {
        return self::ID;
    }

    public function description(): string
    {
        return 'No dependency cycle between classes or components.';
    }

    public function evaluate(Report $report): array
    {
        return array_map(static fn (Cycle $cycle): Violation => new Violation(
            rule: self::ID,
            fingerprint: sprintf('%s:%s:%s', self::ID, $cycle->level->value, implode(',', $cycle->members)),
            message: sprintf('%s cycle: %s.', ucfirst($cycle->level->value), implode(' → ', $cycle->members)),
            file: self::file($cycle, $report),
        ), $report->cycles);
    }

    /**
     * File of the first reported member of a class cycle.
     */
    private static function file(Cycle $cycle, Report $report): ?string
    {
        if ($cycle->level !== CycleLevel::Class_) {
            return null;
        }

        foreach ($cycle->members as $member) {
            if (isset($report->classes[$member])) {
                return $report->classes[$member]->file;
            }
        }

        return null;
    }
}
