<?php

use App\Core\Report\BaselineResult;
use App\Core\Report\Violation;
use App\Core\Rules\BaselineException;
use App\Core\Rules\BaselineFile;

beforeEach(function () {
    $this->file = sys_get_temp_dir().'/baseline-'.uniqid().'.json';
});

afterEach(function () {
    if (is_file($this->file)) {
        unlink($this->file);
    }
});

it('writes a json file sorted by fingerprint', function () {
    (new BaselineFile)->write($this->file, [
        new Violation('stable-dependencies', 'stable-dependencies:B->A', 'B → A.'),
        new Violation('no-cycles', 'no-cycles:component:A,B', 'Component cycle: A → B.'),
    ]);

    expect(file_get_contents($this->file))->toBe(<<<'JSON'
        {
            "schema": 1,
            "violations": [
                {
                    "fingerprint": "no-cycles:component:A,B",
                    "message": "Component cycle: A → B."
                },
                {
                    "fingerprint": "stable-dependencies:B->A",
                    "message": "B → A."
                }
            ]
        }

        JSON);
});

it('reads back the written violations', function () {
    $violations = [
        new Violation('no-cycles', 'no-cycles:component:A,B', 'Component cycle: A → B.'),
        new Violation('max-distance', 'max-distance:App\Domain', 'Too far.', 'src/Domain.php'),
    ];

    (new BaselineFile)->write($this->file, $violations);

    expect((new BaselineFile)->read($this->file)->apply($violations))
        ->toEqual(new BaselineResult([], 2, []));
});

it('rejects an invalid baseline file', function (?string $content, string $message) {
    if ($content !== null) {
        file_put_contents($this->file, $content);
    }

    expect(fn () => (new BaselineFile)->read($this->file))
        ->toThrow(BaselineException::class, $message);
})->with([
    'missing' => [null, 'not found'],
    'invalid json' => ['{', 'is not valid JSON'],
    'unsupported schema' => ['{"schema": 2, "violations": []}', 'must declare "schema": 1'],
    'missing violations' => ['{"schema": 1}', 'must contain a "violations" list'],
    'entry without fingerprint' => ['{"schema": 1, "violations": [{"message": "x"}]}', 'entry without fingerprint'],
]);
