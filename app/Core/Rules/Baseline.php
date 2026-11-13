<?php

declare(strict_types=1);

namespace App\Core\Rules;

use App\Core\Report\BaselineResult;
use App\Core\Report\Violation;

/**
 * Set of accepted violation fingerprints.
 */
final readonly class Baseline
{
    /**
     * @param  array<string, true>  $fingerprints
     */
    private function __construct(
        private array $fingerprints,
    ) {}

    /**
     * @param  list<string>  $fingerprints
     */
    public static function fromFingerprints(array $fingerprints): self
    {
        return new self(array_fill_keys($fingerprints, true));
    }

    /**
     * @param  list<Violation>  $violations
     */
    public static function fromViolations(array $violations): self
    {
        return self::fromFingerprints(array_map(static fn (Violation $violation): string => $violation->fingerprint, $violations));
    }

    /**
     * @param  list<Violation>  $violations
     */
    public function apply(array $violations): BaselineResult
    {
        $new = [];
        $matched = [];

        foreach ($violations as $violation) {
            if (isset($this->fingerprints[$violation->fingerprint])) {
                $matched[$violation->fingerprint] = true;
            } else {
                $new[] = $violation;
            }
        }

        $stale = array_map('strval', array_keys(array_diff_key($this->fingerprints, $matched)));
        sort($stale, SORT_STRING);

        return new BaselineResult($new, count($violations) - count($new), $stale);
    }
}
