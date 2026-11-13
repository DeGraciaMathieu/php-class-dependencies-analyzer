<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Report\Report;
use App\Core\Rules\BaselineResult;
use App\Core\Rules\Violation;

interface Renderer
{
    /**
     * @param  list<ReportSection>  $sections
     * @param  list<Violation>|null  $violations  Null when no rule is configured, empty when every rule passes;
     *                                            only the new ones ($baseline->new) when a baseline is used
     * @param  BaselineResult|null  $baseline  Null when no baseline is used
     */
    public function render(Report $report, array $sections, ?array $violations = null, ?BaselineResult $baseline = null): string;
}
