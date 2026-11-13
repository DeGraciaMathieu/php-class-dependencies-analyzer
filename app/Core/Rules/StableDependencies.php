<?php

declare(strict_types=1);

namespace App\Core\Rules;

use App\Core\Analysis\SdpViolation;
use App\Core\Report\Report;
use App\Core\Report\Violation;

/**
 * One violation per SDP violation whose delta is strictly greater than the tolerance.
 * Fingerprint: "stable-dependencies:From->To".
 */
final readonly class StableDependencies implements Rule
{
    private const ID = 'stable-dependencies';

    public function __construct(
        private float $tolerance = 0.0,
    ) {}

    public function id(): string
    {
        return self::ID;
    }

    public function description(): string
    {
        return sprintf('Components depend on more stable components (tolerance %.2f).', $this->tolerance);
    }

    public function evaluate(Report $report): array
    {
        $breaches = array_filter(
            $report->sdpViolations,
            fn (SdpViolation $violation): bool => $violation->delta > $this->tolerance,
        );

        return array_values(array_map(fn (SdpViolation $violation): Violation => new Violation(
            rule: self::ID,
            fingerprint: sprintf('%s:%s->%s', self::ID, $violation->from, $violation->to),
            message: sprintf(
                '%s (I=%.2f) depends on the less stable %s (I=%.2f), delta %.2f.',
                $violation->from,
                $violation->fromInstability,
                $violation->to,
                $violation->toInstability,
                $violation->delta,
            ),
            ruleDescription: $this->description(),
        ), $breaches));
    }
}
