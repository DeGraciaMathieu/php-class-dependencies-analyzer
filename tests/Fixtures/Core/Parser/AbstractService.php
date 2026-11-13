<?php

namespace Tests\Fixtures\Core\Parser;

abstract class AbstractService
{
    abstract public function run(): \Fixture\Domain\Result;
}
