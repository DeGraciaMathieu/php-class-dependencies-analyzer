<?php

declare(strict_types=1);

namespace App\Core\Analysis;

/**
 * Strongly connected component of more than one member, between classes or between components.
 */
final readonly class Cycle
{
    public int $size;

    /**
     * @param  list<string>  $members  Sorted FQCNs or component names
     */
    public function __construct(
        public array $members,
        public CycleLevel $level,
    ) {
        $this->size = count($members);
    }
}
