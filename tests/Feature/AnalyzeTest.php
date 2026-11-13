<?php

use Illuminate\Support\Facades\Artisan;

function analyzeJson(array $parameters): array
{
    Artisan::call('analyze', [...$parameters, '--format' => 'json']);

    return json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
}

it('can run the analyze command', function () {
    $this->artisan('analyze app')
        ->expectsOutputToContain('Components')
        ->assertExitCode(0);
});

it('can run the analyze command with the json format', function () {
    $json = analyzeJson(['paths' => ['app']]);

    expect($json['schema'])->toBe(1)
        ->and($json)->toHaveKeys(['summary', 'components', 'cycles', 'sdp', 'violations', 'errors']);
});

it('can run the analyze command with a report section', function () {
    $json = analyzeJson(['paths' => ['app'], '--report' => 'classes']);

    expect($json)->toHaveKey('classes')
        ->and($json)->not->toHaveKey('components');
});

it('can run the analyze command with only option', function () {
    $json = analyzeJson(['paths' => ['app'], '--report' => 'classes', '--only' => 'App\Core\Config\*']);

    expect(array_column($json['classes'], 'fqcn'))->not->toBeEmpty()->each->toStartWith('App\Core\Config\\');
});

it('can run the analyze command with exclude option', function () {
    $json = analyzeJson(['paths' => ['app'], '--report' => 'classes', '--exclude' => 'App\Core\*']);

    expect(array_column($json['classes'], 'fqcn'))->not->toBeEmpty()->each->not->toStartWith('App\Core\\');
});

it('can write the report to a file', function () {
    $file = tempnam(sys_get_temp_dir(), 'analyze');

    try {
        $this->artisan("analyze app --format=json --output={$file}")
            ->expectsOutputToContain("Report written to {$file}.")
            ->assertExitCode(0);

        expect(json_decode(file_get_contents($file), true)['schema'])->toBe(1);
    } finally {
        unlink($file);
    }
});

it('can run the analyze command with a configuration file', function () {
    $json = analyzeJson(['--config' => 'tests/Fixtures/Core/Config/project.php']);

    expect($json['summary']['classes'])->toBe(11);
});

it('succeeds on the project with its own rules', function () {
    $this->artisan('analyze')
        ->expectsOutputToContain('No rule violation.')
        ->assertExitCode(0);
});

it('succeeds without rule even when the project has cycles', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/project.php']);

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('Cycles')->not->toContain('Rule violations');
});

it('fails with exit code 1 and lists the violations when a rule is violated', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php']);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())
        ->toContain('Rule violations')
        ->toContain('no-cycles            Component cycle: Shop\Billing → Shop\Ordering.')
        ->toContain('max-distance         Shop\Catalog is at distance 0.67 from the main sequence (max 0.50).');
});

it('renders the violations in json with exit code 1', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--format' => 'json']);
    $json = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(1)
        ->and(array_column($json['violations'], 'fingerprint'))->toBe([
            'no-cycles:class:Shop\Catalog\Category,Shop\Catalog\Product',
            'no-cycles:component:Shop\Billing,Shop\Ordering',
            'stable-dependencies:Shop\Billing->Shop\Ordering',
            'max-distance:Shop\Catalog',
        ])
        ->and($json['violations'][0]['file'])->toBe('tests/Fixtures/Core/Project/Catalog/Category.php');
});

it('fails with exit code 2', function (string $command, string $message) {
    $this->artisan($command)
        ->expectsOutputToContain($message)
        ->assertExitCode(2);
})->with([
    'missing path' => ['analyze missing', 'does not exist'],
    'unknown format' => ['analyze app --format=xml', 'Unknown format "xml"'],
    'unknown section' => ['analyze app --report=foo', 'Unknown report section "foo"'],
    'invalid configuration' => ['analyze --config=tests/Fixtures/Core/Config/returns-array.php', 'must return'],
]);
