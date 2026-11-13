<?php

use App\Core\Cache\ParseCache;
use App\Core\Parser\ClassParser;
use App\Core\Parser\ParseResult;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Core\Rules\BaselineFile;
use Illuminate\Support\Facades\Artisan;

function analyzeJson(array $parameters): array
{
    Artisan::call('analyze', [...$parameters, '--format' => 'json', '--no-cache' => true]);

    return json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
}

it('can run the analyze command', function () {
    $this->artisan('analyze app --no-cache')
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
        $this->artisan("analyze app --format=json --output={$file} --no-cache")
            ->expectsOutputToContain("Report written to {$file}.")
            ->assertExitCode(0);

        expect(json_decode(file_get_contents($file), true)['schema'])->toBe(1);
    } finally {
        unlink($file);
    }
});

it('writes the html report to a file', function () {
    $file = tempnam(sys_get_temp_dir(), 'analyze');

    try {
        $this->artisan("analyze --config=tests/Fixtures/Core/Config/rules.php --format=html --output={$file} --no-cache")
            ->expectsOutputToContain("Report written to {$file}.")
            ->assertExitCode(1);

        expect(file_get_contents($file))->toStartWith('<!DOCTYPE html>')
            ->toContain('cytoscape/3.21.1/cytoscape.min.js')
            ->toContain('"Shop\\\\Billing"');
    } finally {
        unlink($file);
    }
});

it('can run the analyze command with a configuration file', function () {
    $json = analyzeJson(['--config' => 'tests/Fixtures/Core/Config/project.php']);

    expect($json['summary']['classes'])->toBe(11);
});

it('succeeds on the project with its own rules', function () {
    $this->artisan('analyze --no-cache')
        ->expectsOutputToContain('No rule violation.')
        ->assertExitCode(0);
});

it('succeeds without rule even when the project has cycles', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/project.php', '--no-cache' => true]);

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('Cycles')->not->toContain('Rule violations');
});

it('fails with exit code 1 and lists the violations when a rule is violated', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--no-cache' => true]);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())
        ->toContain('Rule violations')
        ->toContain('no-cycles            Component cycle: Shop\Billing → Shop\Ordering.')
        ->toContain('max-distance         Shop\Catalog is at distance 0.67 from the main sequence (max 0.50).');
});

it('renders the violations in json with exit code 1', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--format' => 'json', '--no-cache' => true]);
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

it('renders the violations in sarif with exit code 1', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--format' => 'sarif', '--no-cache' => true]);
    $run = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR)['runs'][0];
    $uris = array_map(static fn (array $result): string => $result['locations'][0]['physicalLocation']['artifactLocation']['uri'], $run['results']);

    expect($exitCode)->toBe(1)
        ->and(array_column($run['tool']['driver']['rules'], 'id'))->toBe(['no-cycles', 'stable-dependencies', 'max-distance'])
        ->and($uris)->toBe([
            'tests/Fixtures/Core/Project/Catalog/Category.php',
            'tests/Fixtures/Core/Config/rules.php',
            'tests/Fixtures/Core/Config/rules.php',
            'tests/Fixtures/Core/Config/rules.php',
        ]);
});

describe('baseline', function () {
    beforeEach(function () {
        $this->directory = sys_get_temp_dir().'/analyze-baseline-'.uniqid();
        $this->baseline = $this->directory.'/'.BaselineFile::FILENAME;
        mkdir($this->directory);
    });

    afterEach(function () {
        array_map(unlink(...), glob($this->directory.'/*'));
        rmdir($this->directory);
    });

    it('generates a baseline with every current violation and exit code 0', function () {
        $exitCode = Artisan::call('analyze', [
            '--config' => 'tests/Fixtures/Core/Config/rules.php',
            '--generate-baseline' => $this->baseline,
            '--no-cache' => true,
        ]);

        $baseline = json_decode(file_get_contents($this->baseline), true, flags: JSON_THROW_ON_ERROR);

        expect($exitCode)->toBe(0)
            ->and(Artisan::output())->toBe("Baseline written to {$this->baseline} with 4 violation(s).\n")
            ->and($baseline['schema'])->toBe(1)
            ->and(array_column($baseline['violations'], 'fingerprint'))->toBe([
                'max-distance:Shop\Catalog',
                'no-cycles:class:Shop\Catalog\Category,Shop\Catalog\Product',
                'no-cycles:component:Shop\Billing,Shop\Ordering',
                'stable-dependencies:Shop\Billing->Shop\Ordering',
            ]);
    });

    it('generates the baseline in the current directory without value', function () {
        $root = getcwd();
        chdir($this->directory);

        try {
            $this->artisan('analyze', [
                'paths' => [$root.'/tests/Fixtures/Core/Project'],
                '--config' => $root.'/tests/Fixtures/Core/Config/rules.php',
                '--generate-baseline' => null,
                '--no-cache' => true,
            ])->assertExitCode(0);
        } finally {
            chdir($root);
        }

        expect($this->baseline)->toBeFile();
    });

    it('succeeds with a baseline holding every violation', function () {
        Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--generate-baseline' => $this->baseline, '--no-cache' => true]);

        $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--baseline' => $this->baseline, '--no-cache' => true]);

        expect($exitCode)->toBe(0)
            ->and(Artisan::output())->toContain("Rule violations\nNo rule violation.\n4 baselined, 0 stale");
    });

    it('fails only on the new violations', function () {
        Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--generate-baseline' => $this->baseline, '--no-cache' => true]);

        $exitCode = Artisan::call('analyze', [
            '--config' => 'tests/Fixtures/Core/Config/rules-stricter.php',
            '--baseline' => $this->baseline,
            '--format' => 'json',
            '--no-cache' => true,
        ]);
        $json = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

        expect($exitCode)->toBe(1)
            ->and(array_column($json['violations'], 'fingerprint'))->toBe(['max-distance:Shop\Billing'])
            ->and($json['baseline'])->toBe(['baselined' => 4, 'stale' => []]);
    });

    it('reports a fixed violation as stale without failing', function () {
        file_put_contents($this->baseline, json_encode(['schema' => 1, 'violations' => [
            ['fingerprint' => 'no-cycles:class:Shop\Catalog\Category,Shop\Catalog\Product', 'message' => ''],
            ['fingerprint' => 'no-cycles:component:Shop\Billing,Shop\Ordering', 'message' => ''],
            ['fingerprint' => 'no-cycles:component:Shop\Fixed,Shop\Ordering', 'message' => ''],
        ]]));

        $exitCode = Artisan::call('analyze', [
            '--config' => 'tests/Fixtures/Core/Config/rules-with-baseline.php',
            '--baseline' => $this->baseline,
            '--format' => 'json',
            '--no-cache' => true,
        ]);
        $json = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

        expect($exitCode)->toBe(0)
            ->and($json['violations'])->toBe([])
            ->and($json['baseline'])->toBe(['baselined' => 2, 'stale' => ['no-cycles:component:Shop\Fixed,Shop\Ordering']]);
    });

    it('fails with exit code 2 on a corrupted baseline', function () {
        file_put_contents($this->baseline, '{');

        $this->artisan('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules.php', '--baseline' => $this->baseline, '--no-cache' => true])
            ->expectsOutputToContain('is not valid JSON')
            ->assertExitCode(2);
    });
});

describe('parse cache', function () {
    beforeEach(function () {
        $this->root = getcwd();
        $this->directory = sys_get_temp_dir().'/analyze-cache-'.uniqid();
        $this->cache = $this->directory.'/'.ParseCache::FILENAME;
        mkdir($this->directory);
        chdir($this->directory);

        $this->parser = new class($this->app->make(PhpParserClassParser::class)) implements ClassParser
        {
            public int $calls = 0;

            public function __construct(private readonly ClassParser $inner) {}

            public function parse(string $file): ParseResult
            {
                $this->calls++;

                return $this->inner->parse($file);
            }
        };
        $this->app->instance(ClassParser::class, $this->parser);
    });

    afterEach(function () {
        chdir($this->root);
        array_map(unlink(...), glob($this->directory.'/{,.}[!.]*', GLOB_BRACE) ?: []);
        rmdir($this->directory);
    });

    it('parses nothing on a second run without change', function () {
        $parameters = ['paths' => [$this->root.'/tests/Fixtures/Core/Project'], '--format' => 'json'];

        Artisan::call('analyze', $parameters);
        $first = Artisan::output();
        $firstCalls = $this->parser->calls;
        $this->parser->calls = 0;

        Artisan::call('analyze', $parameters);

        expect($firstCalls)->toBeGreaterThan(0)
            ->and($this->parser->calls)->toBe(0)
            ->and(Artisan::output())->toBe($first)
            ->and($this->cache)->toBeFile();
    });

    it('neither reads nor writes the cache with --no-cache', function () {
        $parameters = ['paths' => [$this->root.'/tests/Fixtures/Core/Project'], '--no-cache' => true];

        Artisan::call('analyze', $parameters);
        Artisan::call('analyze', $parameters);

        expect($this->parser->calls)->toBeGreaterThan(0)
            ->and($this->cache)->not->toBeFile();
    });

    it('deletes the cache with --clear-cache', function () {
        file_put_contents($this->cache, 'stale');

        Artisan::call('analyze', ['paths' => [$this->root.'/tests/Fixtures/Core/Project'], '--clear-cache' => true, '--no-cache' => true]);

        expect($this->cache)->not->toBeFile();
    });

    it('writes the cache to the configured file', function () {
        file_put_contents('dependencies.php', sprintf(
            "<?php\n\nreturn App\\Core\\Config\\Config::make()->paths(['%s'])->cache('custom-cache');\n",
            $this->root.'/tests/Fixtures/Core/Project',
        ));

        Artisan::call('analyze');

        expect('custom-cache')->toBeFile()
            ->and($this->cache)->not->toBeFile();
    });
});

it('uses the baseline declared in the configuration', function () {
    $exitCode = Artisan::call('analyze', ['--config' => 'tests/Fixtures/Core/Config/rules-with-baseline.php', '--no-cache' => true]);

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('2 baselined, 0 stale');
});

it('fails with exit code 2', function (string $command, string $message) {
    $this->artisan("{$command} --no-cache")
        ->expectsOutputToContain($message)
        ->assertExitCode(2);
})->with([
    'missing path' => ['analyze missing', 'does not exist'],
    'unknown format' => ['analyze app --format=xml', 'Unknown format "xml", expected one of: table, json, sarif, html.'],
    'html without output' => ['analyze app --format=html', 'The html format requires --output'],
    'unknown section' => ['analyze app --report=foo', 'Unknown report section "foo"'],
    'invalid configuration' => ['analyze --config=tests/Fixtures/Core/Config/returns-array.php', 'must return'],
]);
