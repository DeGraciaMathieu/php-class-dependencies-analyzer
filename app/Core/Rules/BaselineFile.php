<?php

declare(strict_types=1);

namespace App\Core\Rules;

use JsonException;

/**
 * Versioned, diffable baseline file, sorted by fingerprint. Only the fingerprint is compared,
 * the message is informative:
 *
 * {
 *   "schema": 1,
 *   "violations": [ { "fingerprint": string, "message": string } ]
 * }
 */
final readonly class BaselineFile
{
    public const FILENAME = 'dependencies-baseline.json';

    public const SCHEMA = 1;

    public function read(string $file): Baseline
    {
        if (! is_file($file)) {
            throw new BaselineException("Baseline file [{$file}] not found.");
        }

        try {
            $data = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BaselineException("Baseline file [{$file}] is not valid JSON: {$e->getMessage()}.", previous: $e);
        }

        if (! is_array($data) || ($data['schema'] ?? null) !== self::SCHEMA) {
            throw new BaselineException("Baseline file [{$file}] must declare \"schema\": ".self::SCHEMA.'.');
        }

        return Baseline::fromFingerprints($this->fingerprints($file, $data['violations'] ?? null));
    }

    /**
     * @param  list<Violation>  $violations
     */
    public function write(string $file, array $violations): void
    {
        $entries = [];

        foreach ($violations as $violation) {
            $entries[$violation->fingerprint] = ['fingerprint' => $violation->fingerprint, 'message' => $violation->message];
        }

        ksort($entries, SORT_STRING);

        $json = json_encode(
            ['schema' => self::SCHEMA, 'violations' => array_values($entries)],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        if (@file_put_contents($file, $json.PHP_EOL) === false) {
            throw new BaselineException("Baseline file [{$file}] cannot be written.");
        }
    }

    /**
     * @return list<string>
     */
    private function fingerprints(string $file, mixed $violations): array
    {
        if (! is_array($violations)) {
            throw new BaselineException("Baseline file [{$file}] must contain a \"violations\" list.");
        }

        return array_map(static function (mixed $entry) use ($file): string {
            if (! is_array($entry) || ! is_string($entry['fingerprint'] ?? null)) {
                throw new BaselineException("Baseline file [{$file}] contains an entry without fingerprint.");
            }

            return $entry['fingerprint'];
        }, array_values($violations));
    }
}
