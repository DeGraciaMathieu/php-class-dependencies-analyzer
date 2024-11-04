<?php

namespace Tests\Builders;

class WeaknessResponseBuilder
{
    public static function build(): WeaknessResponse
    {
        return new WeaknessResponse(1, []);
    }
}
