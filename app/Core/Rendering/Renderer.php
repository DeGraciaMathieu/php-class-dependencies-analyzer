<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Report\Report;
use App\Core\Rules\Violation;

interface Renderer
{
    /**
     * @param  list<ReportSection>  $sections
     * @param  list<Violation>|null  $violations  Null when no rule is configured, empty when every rule passes
     */
    public function render(Report $report, array $sections, ?array $violations = null): string;
}
