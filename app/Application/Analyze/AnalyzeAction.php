<?php

namespace App\Application\Analyze;

use Throwable;
use App\Application\Analyze\AnalyzeRequest;
use App\Application\Analyze\AnalyzePresenter;
use App\Domain\Ports\Repositories\FileRepository;
use App\Application\Analyze\AnalyzeResponseMapper;

class AnalyzeAction
{
    public function __construct(
        private FileRepository $fileRepository,
        private AnalyzeResponseMapper $mapper,
    ) {}

    public function execute(AnalyzeRequest $request, AnalyzePresenter $presenter): void
    {
        try {

            $presenter->hello();

            $fileAggregator = $this->fileRepository->find($request->path);

            $dependencyAggregator = $fileAggregator->getAllDependencies();

            $dependencyAggregator->calculateClassesInstability();

            $dependencyAggregator->keepOnlyClasses($request->filters);

            $presenter->present(
                $this->mapper->from($dependencyAggregator),
            );

        } catch (Throwable $e) {
            $presenter->error($e);
        }
    }
}
