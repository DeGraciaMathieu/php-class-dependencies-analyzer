<?php

declare(strict_types=1);

namespace App\Core\Rendering;

/**
 * Minimal ASCII table: left-aligned columns separated by two spaces, a dashed line under the headers.
 * ANSI escape sequences in the cells are ignored when computing the column widths.
 *
 * @internal
 */
final readonly class TextTable
{
    private const SEPARATOR = '  ';

    private const ANSI_ESCAPE_PATTERN = '/\e\[[0-9;]*m/';

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    public function __construct(
        private array $headers,
        private array $rows,
    ) {}

    public function render(): string
    {
        $widths = $this->columnWidths();
        $dashes = array_map(static fn (int $width): string => str_repeat('-', $width), $widths);

        $lines = [
            $this->line($this->headers, $widths),
            $this->line($dashes, $widths),
        ];

        foreach ($this->rows as $row) {
            $lines[] = $this->line($row, $widths);
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return list<int>
     */
    private function columnWidths(): array
    {
        $widths = array_map(self::visibleLength(...), $this->headers);

        foreach ($this->rows as $row) {
            foreach ($row as $index => $cell) {
                $widths[$index] = max($widths[$index], self::visibleLength($cell));
            }
        }

        return $widths;
    }

    /**
     * @param  list<string>  $cells
     * @param  list<int>  $widths
     */
    private function line(array $cells, array $widths): string
    {
        $padded = array_map(
            static fn (string $cell, int $width): string => $cell.str_repeat(' ', $width - self::visibleLength($cell)),
            $cells,
            $widths,
        );

        return rtrim(implode(self::SEPARATOR, $padded));
    }

    private static function visibleLength(string $cell): int
    {
        return mb_strlen((string) preg_replace(self::ANSI_ESCAPE_PATTERN, '', $cell));
    }
}
