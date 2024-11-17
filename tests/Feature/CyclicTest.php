<?php

it('can run the cyclic command', function () {
    $this->artisan('cyclic app')->assertSuccessful();
});

it('can run the cyclic command with only option', function () {
    $this->artisan("cyclic app --only='App\Application'")->assertSuccessful();
});

it('can run the cyclic command with exclude option', function () {
    $this->artisan("cyclic app --exclude='App\Application'")->assertSuccessful();
});
