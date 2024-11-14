<?php

it('can run the weakness command', function () {
    $this->artisan('weakness app')->assertSuccessful();
});

it('can run the weakness command with only option', function () {
    $this->artisan("weakness app --only='App\Application'")->assertSuccessful();
});

it('can run the weakness command with exclude option', function () {
    $this->artisan("weakness app --exclude='App\Application'")->assertSuccessful();
});

it('can run the weakness command with limit option', function () {
    $this->artisan("weakness app --limit=10")->assertSuccessful();
});

it('can run the weakness command with min-delta option', function () {
    $this->artisan("weakness app --min-delta=10")->assertSuccessful();
});
