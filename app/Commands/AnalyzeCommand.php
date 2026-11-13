<?php

namespace App\Commands;

use App\Core\Analysis\AnalysisPipeline;
use App\Core\Cache\CachedClassParser;
use App\Core\Cache\ParseCache;
use App\Core\Config\Config;
use App\Core\Config\ConfigException;
use App\Core\Config\ConfigLoader;
use App\Core\Parser\ClassParser;
use App\Core\Rendering\JsonRenderer;
use App\Core\Rendering\Renderer;
use App\Core\Rendering\ReportSection;
use App\Core\Rendering\SarifRenderer;
use App\Core\Rendering\TableRenderer;
use App\Core\Report\BaselineResult;
use App\Core\Report\Report;
use App\Core\Report\Violation;
use App\Core\Rules\BaselineException;
use App\Core\Rules\BaselineFile;
use App\Core\Rules\Rule;
use Composer\InstalledVersions;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeCommand extends AbstractCommand
{
    /**
     * Exit codes: 0 (SUCCESS) no new rule violation (or baseline generated), 1 (FAILURE) at least one rule
     * violation absent from the baseline, 2 (ERROR) invalid configuration, option or baseline file.
     */
    public const ERROR = 2;

    protected $signature = 'analyze
        {paths?* : Files or directories to analyze, replace the configured paths}
        {--config= : Configuration file, defaults to dependencies.php in the current directory}
        {--report= : Comma-separated sections: classes, components, cycles, sdp}
        {--format=table : Output format: table, json or sarif}
        {--output= : Write the report to this file instead of the standard output}
        {--only= : Comma-separated FQCN globs to keep, replace the configured "only"}
        {--exclude= : Comma-separated FQCN globs to ignore, replace the configured "ignore"}
        {--baseline= : Baseline file, replace the configured baseline}
        {--generate-baseline= : Write every current violation to this baseline file, defaults to dependencies-baseline.json}
        {--no-cache : Neither read nor write the parse cache}
        {--clear-cache : Delete the parse cache before the analysis}
    ';

    protected $description = 'Analyze class and component dependencies';

    public function handle(ConfigLoader $loader, ClassParser $parser): int
    {
        try {
            $sections = $this->makeSections();
            $configFile = $this->option('config') ?? $loader->find(getcwd());
            $config = $this->mergeCliOptions($configFile === null ? Config::make() : $loader->load($configFile));
            $renderer = $this->makeRenderer($configFile, $config);

            $cache = $this->makeCache($config);
            $pipeline = new AnalysisPipeline($cache === null ? $parser : new CachedClassParser($parser, $cache));

            $report = $pipeline->run($config->toOptions(getcwd()));
            $cache?->save();

            $violations = $this->evaluateRules($config->rules, $report);

            if ($this->input->hasParameterOption('--generate-baseline', true)) {
                return $this->generateBaseline($violations ?? []);
            }

            $baseline = $this->applyBaseline($violations, $config);
            $violations = $baseline?->new ?? $violations;

            $this->write($renderer->render($report, $sections, $violations, $baseline));
        } catch (ConfigException|BaselineException|InvalidArgumentException $e) {
            $this->output->getErrorStyle()->writeln("<error>{$e->getMessage()}</error>");

            return self::ERROR;
        }

        return $violations === null || $violations === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return ParseCache|null Null when the cache is disabled by the configuration or by --no-cache
     */
    private function makeCache(Config $config): ?ParseCache
    {
        if ($config->cache === false) {
            return null;
        }

        $cache = new ParseCache($config->cache === true ? ParseCache::FILENAME : $config->cache, $this->cacheSignature());

        if ($this->option('clear-cache')) {
            $cache->clear();
        }

        if ($this->option('no-cache')) {
            return null;
        }

        $cache->load();

        return $cache;
    }

    /**
     * A new version of the tool or of php-parser may parse differently: it invalidates the whole cache.
     */
    private function cacheSignature(): string
    {
        return config('app.version').'|'.InstalledVersions::getPrettyVersion('nikic/php-parser');
    }

    /**
     * @param  list<Rule>  $rules
     * @return list<Violation>|null Null when no rule is configured
     */
    private function evaluateRules(array $rules, Report $report): ?array
    {
        if ($rules === []) {
            return null;
        }

        return array_merge(...array_map(static fn (Rule $rule): array => $rule->evaluate($report), $rules));
    }

    /**
     * @param  list<Violation>  $violations
     */
    private function generateBaseline(array $violations): int
    {
        $file = $this->option('generate-baseline') ?? BaselineFile::FILENAME;

        (new BaselineFile)->write($file, $violations);

        $this->info(sprintf('Baseline written to %s with %d violation(s).', $file, count($violations)));

        return self::SUCCESS;
    }

    /**
     * @param  list<Violation>|null  $violations
     * @return BaselineResult|null Null when no rule or no baseline is configured
     */
    private function applyBaseline(?array $violations, Config $config): ?BaselineResult
    {
        $file = $this->option('baseline') ?? $config->baseline;

        if ($violations === null || $file === null) {
            return null;
        }

        return (new BaselineFile)->read($file)->apply($violations);
    }

    private function mergeCliOptions(Config $config): Config
    {
        $paths = $this->argument('paths');

        if ($paths !== []) {
            $config = $config->paths($paths);
        }

        if ($this->option('only') !== null) {
            $config = $config->only($this->optionToList('only'));
        }

        if ($this->option('exclude') !== null) {
            $config = $config->ignore($this->optionToList('exclude'));
        }

        return $config;
    }

    private function makeRenderer(?string $configFile, Config $config): Renderer
    {
        $format = $this->option('format');

        return match ($format) {
            'table' => new TableRenderer,
            'json' => new JsonRenderer,
            'sarif' => new SarifRenderer((string) config('app.version'), $this->sarifFallbackLocation($configFile, $config)),
            default => throw new InvalidArgumentException("Unknown format \"{$format}\", expected one of: table, json, sarif."),
        };
    }

    /**
     * Location of the violations without file: the configuration file used, otherwise the first analyzed path,
     * relative to the current directory (code scanning rejects a result without physical location).
     */
    private function sarifFallbackLocation(?string $configFile, Config $config): string
    {
        $location = $configFile ?? $config->paths[0] ?? '.';
        $prefix = getcwd().'/';

        return str_starts_with($location, $prefix) ? substr($location, strlen($prefix)) : $location;
    }

    /**
     * @return list<ReportSection>
     */
    private function makeSections(): array
    {
        if ($this->option('report') === null) {
            return ReportSection::defaults();
        }

        return ReportSection::fromList($this->optionToList('report'));
    }

    private function write(string $rendered): void
    {
        $file = $this->option('output');

        if ($file === null) {
            $this->output->write($rendered, false, OutputInterface::OUTPUT_RAW);

            return;
        }

        file_put_contents($file, $rendered);

        $this->info("Report written to {$file}.");
    }
}
