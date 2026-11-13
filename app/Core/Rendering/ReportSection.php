<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use InvalidArgumentException;

enum ReportSection: string
{
    case Classes = 'classes';
    case Components = 'components';
    case Cycles = 'cycles';
    case Sdp = 'sdp';

    /**
     * @return list<self>
     */
    public static function defaults(): array
    {
        return [self::Components, self::Cycles, self::Sdp];
    }

    /**
     * @param  list<string>  $names
     * @return list<self>
     */
    public static function fromList(array $names): array
    {
        return array_map(static function (string $name): self {
            return self::tryFrom($name) ?? throw new InvalidArgumentException(sprintf(
                'Unknown report section "%s", expected one of: %s.',
                $name,
                implode(', ', array_column(self::cases(), 'value')),
            ));
        }, array_values($names));
    }

    /**
     * Requested sections in the enum order, without duplicates.
     *
     * @param  list<self>  $sections
     * @return list<self>
     */
    public static function ordered(array $sections): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $case): bool => in_array($case, $sections, true),
        ));
    }
}
