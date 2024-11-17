<?php

it('can run the analyze:class command', function () {
    $this->artisan('analyze:class app')->assertSuccessful();
});

it('can run the analyze:class command with a custom target', function () {
    $this->artisan('analyze:class app --target=App\Application\Analyze\AnalyzeAction')->assertSuccessful();
});

it('can run the analyze:class command with only a specific namespace', function () {
    $this->artisan('analyze:class app --only=App\Application')->assertSuccessful();
});

it('can run the analyze:class command with exclude a specific namespace', function () {
    $this->artisan('analyze:class app --exclude=App\Application')->assertSuccessful();
});

it('can run the analyze:class command with graph', function () {
    $this->artisan('analyze:class app --graph')->assertSuccessful();
})->skip('need to find a way to bypass graph generation');
