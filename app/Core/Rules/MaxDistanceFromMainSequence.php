<?php

declare(strict_types=1);

namespace App\Core\Rules;

use App\Core\Analysis\ComponentMetrics;
use App\Core\Report\Report;
use App\Core\Report\Violation;

/**
 * One violation per component whose distance from the main sequence is strictly greater than the maximum.
 * A non-measurable distance is ignored. Fingerprint: "max-distance:Component".
 */
final readonly class MaxDistanceFromMainSequence implements Rule
{
    private const ID = 'max-distance';

    /**
     * @param  list<string>  $components  Checked component names, every component when empty
     */
    public function __construct(
        private float $max,
        private array $components = [],
    ) {}

    /**
     * @param  string|list<string>  $components
     */
    public function for(string|array $components): self
    {
        return new self($this->max, (array) $components);
    }

    public function id(): string
    {
        return self::ID;
    }

    public function description(): string
    {
        return sprintf('Components stay within %.2f of the main sequence.', $this->max);
    }

    public function evaluate(Report $report): array
    {
        $breaches = array_filter(
            $report->components,
            fn (ComponentMetrics $component): bool => $this->isChecked($component->name)
                && $component->distance !== null
                && $component->distance > $this->max,
        );

        return array_values(array_map(fn (ComponentMetrics $component): Violation => new Violation(
            rule: self::ID,
            fingerprint: sprintf('%s:%s', self::ID, $component->name),
            message: sprintf(
                '%s is at distance %.2f from the main sequence (max %.2f).',
                $component->name,
                $component->distance,
                $this->max,
            ),
            ruleDescription: $this->description(),
        ), $breaches));
    }

    private function isChecked(string $component): bool
    {
        return $this->components === [] || in_array($component, $this->components, true);
    }
}
