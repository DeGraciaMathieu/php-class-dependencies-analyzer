<?php

declare(strict_types=1);

namespace App\Core\Rules;

use App\Core\Report\Report;

/**
 * Constraint checked on a report, built with the Rules factories in a dependencies.php file.
 */
interface Rule
{
    public function id(): string;

    public function description(): string;

    /**
     * @return list<Violation>
     */
    public function evaluate(Report $report): array;
}
