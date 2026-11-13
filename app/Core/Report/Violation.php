<?php

declare(strict_types=1);

namespace App\Core\Report;

/**
 * Breach of a rule.
 *
 * - fingerprint: stable identifier, built from the rule and the subject only, never from measured values
 * - message: human-readable, measured values rounded to 2 decimals
 * - file: path relative to the project root when a file is relevant, null otherwise
 * - ruleDescription: description of the rule that produced the violation (SARIF rule metadata), null when unknown
 */
final readonly class Violation
{
    public function __construct(
        public string $rule,
        public string $fingerprint,
        public string $message,
        public ?string $file = null,
        public ?string $ruleDescription = null,
    ) {}
}
