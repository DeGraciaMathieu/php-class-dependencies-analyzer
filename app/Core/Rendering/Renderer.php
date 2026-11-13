<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Report\Report;

interface Renderer
{
    /**
     * @param  list<ReportSection>  $sections
     */
    public function render(Report $report, array $sections): string;
}
