<?php

namespace App\Application\Analyze;

use Throwable;
use App\Application\Analyze\AnalyzeRequest;
use App\Application\Analyze\AnalyzePresenter;
use App\Domain\Ports\Repositories\FileRepository;

class AnalyzeAction
{
    public function __construct(
        private FileRepository $fileRepository,
    ) {}

    public function execute(AnalyzeRequest $request, AnalyzePresenter $presenter): void
    {
        try {
            $presenter->hello();

            $fileAggregator = $this->fileRepository->find($request->path);

            $dependencyAggregator = $fileAggregator->getAllDependencies();

            $dependencyAggregator->calculateClassesInstability();

            $dependencyAggregator->removeIgnoredClasses($request->filters);

            $presenter->present(
                ResponseMapper::from($dependencyAggregator),
            );
            
        } catch (Throwable $e) {
            $presenter->error($e->getMessage());
        }
    }
}
