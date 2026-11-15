<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\MainSequenceZone;
use Closure;

/**
 * Text plot of the components on the A/I chart: instability I on the x axis, abstractness A on the y axis,
 * the main sequence A + I = 1 drawn with dots. Points are snapped to a 0.05 x 0.1 grid and numbered per cell,
 * the legend below gives the exact metrics and the zone of each component.
 *
 * @internal
 */
final readonly class MainSequenceChart
{
    private const COLUMNS = 20;

    private const ROWS = 10;

    private const CELL_WIDTH = 2;

    private const SEQUENCE_MARK = '·';

    private const NOT_MEASURABLE = '—';

    /**
     * @param  list<ComponentMetrics>  $components
     * @param  Closure(string): string  $highlight  Applied to the labels of the components in a zone
     */
    public function __construct(
        private array $components,
        private Closure $highlight,
    ) {}

    public function render(): string
    {
        $labels = $this->cellLabels();

        return implode(PHP_EOL, [
            ...$this->plot($labels),
            '',
            $this->legend($labels),
        ]);
    }

    /**
     * Number of each plotted component, shared by the components of a same cell, in the components order.
     *
     * @return array<string, string> Component name => label
     */
    private function cellLabels(): array
    {
        $cells = [];
        $labels = [];

        foreach ($this->components as $component) {
            if ($component->abstractness === null || $component->instability === null) {
                continue;
            }

            $cell = $this->cellKey($component);
            $cells[$cell] ??= (string) (count($cells) + 1);
            $labels[$component->name] = $cells[$cell];
        }

        return $labels;
    }

    /**
     * @param  array<string, string>  $labels
     * @return list<string>
     */
    private function plot(array $labels): array
    {
        $grid = $this->emptyGrid();
        $cellsInZone = $this->cellsInZone();

        foreach ($this->components as $component) {
            if (! isset($labels[$component->name])) {
                continue;
            }

            [$row, $column] = $this->cell($component);
            $label = str_pad($labels[$component->name], self::CELL_WIDTH);

            $grid[$row][$column] = isset($cellsInZone[$this->cellKey($component)]) ? ($this->highlight)($label) : $label;
        }

        $lines = ['A'];

        foreach ($grid as $row => $cells) {
            $axisLabel = match ($row) {
                0 => '1.0',
                self::ROWS / 2 => '0.5',
                self::ROWS => '0.0',
                default => '',
            };

            $lines[] = rtrim(str_pad($axisLabel, 4).'│'.implode('', $cells));
        }

        $width = (self::COLUMNS + 1) * self::CELL_WIDTH;
        $halfWidth = intdiv(self::COLUMNS, 2) * self::CELL_WIDTH;

        $lines[] = '    └'.str_repeat('─', $width);
        $lines[] = '     '.str_pad('0.0', $halfWidth).str_pad('0.5', $halfWidth).'1.0  I';

        return $lines;
    }

    /**
     * @return list<list<string>> Rows from A = 1 to A = 0, each cell CELL_WIDTH characters wide
     */
    private function emptyGrid(): array
    {
        $grid = array_fill(0, self::ROWS + 1, array_fill(0, self::COLUMNS + 1, str_repeat(' ', self::CELL_WIDTH)));

        // Integer arithmetic keeps the halves exact, so the dotted line steps regularly.
        for ($column = 0; $column <= self::COLUMNS; $column++) {
            $row = self::ROWS - (int) round((self::COLUMNS - $column) * self::ROWS / self::COLUMNS);
            $grid[$row][$column] = self::SEQUENCE_MARK.' ';
        }

        return $grid;
    }

    /**
     * Cells holding at least one component in a zone, highlighted whole.
     *
     * @return array<string, true>
     */
    private function cellsInZone(): array
    {
        $cells = [];

        foreach ($this->components as $component) {
            if (MainSequenceZone::of($component) !== null) {
                $cells[$this->cellKey($component)] = true;
            }
        }

        return $cells;
    }

    /**
     * @param  array<string, string>  $labels
     */
    private function legend(array $labels): string
    {
        $rows = array_map(function (ComponentMetrics $component) use ($labels): array {
            $zone = MainSequenceZone::of($component);

            return [
                $labels[$component->name] ?? self::NOT_MEASURABLE,
                $zone === null ? $component->name : ($this->highlight)($component->name),
                self::metric($component->instability),
                self::metric($component->abstractness),
                self::metric($component->distance),
                $zone === null ? '' : $zone->value,
            ];
        }, $this->components);

        return (new TextTable(['#', 'Component', 'I', 'A', 'D', 'Zone'], $rows))->render();
    }

    /**
     * @return array{int, int} Row and column of the component in the grid
     */
    private function cell(ComponentMetrics $component): array
    {
        return [
            $this->row((float) $component->abstractness),
            (int) round((float) $component->instability * self::COLUMNS),
        ];
    }

    private function cellKey(ComponentMetrics $component): string
    {
        return implode(':', $this->cell($component));
    }

    private function row(float $abstractness): int
    {
        return self::ROWS - (int) round($abstractness * self::ROWS);
    }

    private static function metric(?float $value): string
    {
        return $value === null ? self::NOT_MEASURABLE : number_format($value, 2, '.', '');
    }
}
