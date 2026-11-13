<?php

use App\Core\Parser\SourceScanner;

beforeEach(function () {
    $this->basePath = sys_get_temp_dir().'/source-scanner-'.uniqid();

    $files = [
        'src/B.php',
        'src/A.php',
        'src/Sub/C.php',
        'src/readme.md',
        'src/script.phpx',
        'vendor/package/D.php',
    ];

    foreach ($files as $file) {
        $directory = dirname($this->basePath.'/'.$file);

        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        file_put_contents($this->basePath.'/'.$file, '<?php');
    }

    $this->basePath = realpath($this->basePath);
});

afterEach(function () {
    exec('rm -rf '.escapeshellarg($this->basePath));
});

it('finds php files recursively in a stable order', function () {
    $files = (new SourceScanner)->scan(['.'], [], $this->basePath);

    expect($files)->toBe([
        $this->basePath.'/src/A.php',
        $this->basePath.'/src/B.php',
        $this->basePath.'/src/Sub/C.php',
        $this->basePath.'/vendor/package/D.php',
    ]);
});

it('excludes files matching globs relative to the base path', function () {
    $files = (new SourceScanner)->scan(['.'], ['vendor/*', 'src/Sub/*'], $this->basePath);

    expect($files)->toBe([
        $this->basePath.'/src/A.php',
        $this->basePath.'/src/B.php',
    ]);
});

it('accepts single files and absolute paths without duplicates', function () {
    $files = (new SourceScanner)->scan(
        ['src/B.php', $this->basePath.'/src', 'src/readme.md'],
        [],
        $this->basePath,
    );

    expect($files)->toBe([
        $this->basePath.'/src/A.php',
        $this->basePath.'/src/B.php',
        $this->basePath.'/src/Sub/C.php',
    ]);
});

it('throws when a path does not exist', function () {
    (new SourceScanner)->scan(['missing'], [], $this->basePath);
})->throws(InvalidArgumentException::class, '/missing] does not exist.');
