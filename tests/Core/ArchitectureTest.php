<?php

arch('core only depends on itself and php-parser')
    ->expect('App\Core')
    ->toOnlyUse('PhpParser');

arch('core uses strict types')
    ->expect('App\Core')
    ->toUseStrictTypes();

arch('only the php-parser adapter depends on php-parser')
    ->expect('App\Core')
    ->not->toUse('PhpParser')
    ->ignoring('App\Core\Parser\PhpParser');
