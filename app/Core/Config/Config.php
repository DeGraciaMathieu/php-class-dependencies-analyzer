<?php

declare(strict_types=1);

namespace App\Core\Config;

use App\Core\Analysis\AnalysisOptions;
use App\Core\Analysis\ComponentResolver;
use App\Core\Graph\Filter;
use App\Core\Rules\Rule;
use InvalidArgumentException;

/**
 * Immutable description of an analysis, returned by a dependencies.php file:
 * every fluent method returns a new instance.
 */
final readonly class Config
{
    /**
     * @param  list<string>  $paths  Files or directories, relative to the project root
     * @param  list<string>  $exclude  File globs, see SourceScanner
     * @param  list<string>  $only  FQCN globs kept, see Filter
     * @param  list<string>  $ignore  FQCN globs excluded, see Filter
     * @param  array<string, string|list<string>>  $components  Component name => glob(s), see ComponentResolver
     * @param  list<Rule>  $rules  Checked on the report, none by default
     */
    private function __construct(
        public array $paths = [],
        public array $exclude = [],
        public array $only = [],
        public array $ignore = [],
        public array $components = [],
        public int $componentDepth = 2,
        public array $rules = [],
    ) {}

    public static function make(): self
    {
        return new self;
    }

    /**
     * @param  list<string>  $paths
     */
    public function paths(array $paths): self
    {
        return $this->with(['paths' => $paths]);
    }

    /**
     * @param  list<string>  $globs
     */
    public function exclude(array $globs): self
    {
        return $this->with(['exclude' => $globs]);
    }

    /**
     * @param  list<string>  $globs
     */
    public function only(array $globs): self
    {
        return $this->with(['only' => $globs]);
    }

    /**
     * @param  list<string>  $globs
     */
    public function ignore(array $globs): self
    {
        return $this->with(['ignore' => $globs]);
    }

    /**
     * @param  array<string, string|list<string>>  $components
     */
    public function components(array $components): self
    {
        return $this->with(['components' => $components]);
    }

    public function componentDepth(int $depth): self
    {
        return $this->with(['componentDepth' => $depth]);
    }

    /**
     * @param  list<Rule>  $rules
     */
    public function rules(array $rules): self
    {
        return $this->with(['rules' => $rules]);
    }

    public function toOptions(string $basePath): AnalysisOptions
    {
        if ($this->paths === []) {
            throw new ConfigException('No path to analyze, call paths() in the configuration.');
        }

        $this->validateComponents();

        return new AnalysisOptions(
            paths: $this->paths,
            basePath: $basePath,
            excludePaths: $this->exclude,
            filter: new Filter($this->only, $this->ignore),
            components: $this->components,
            componentDepth: $this->componentDepth,
        );
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function with(array $changes): self
    {
        return new self(...[...get_object_vars($this), ...$changes]);
    }

    /**
     * ComponentResolver remains the single place where components are validated.
     */
    private function validateComponents(): void
    {
        try {
            new ComponentResolver($this->components, $this->componentDepth);
        } catch (InvalidArgumentException $e) {
            throw new ConfigException($e->getMessage(), previous: $e);
        }
    }
}
