<?php

namespace App\Application\Weakness;

use Throwable;
use App\Application\Weakness\WeaknessRequest;
use App\Application\Weakness\WeaknessPresenter;
use App\Domain\Ports\Repositories\FileRepository;

class WeaknessAction
{
    public function __construct(
        private FileRepository $fileRepository,
        private WeaknessResponseMapper $mapper,
    ) {}

    public function execute(WeaknessRequest $request, WeaknessPresenter $presenter): void
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
            $presenter->error($e->getMessage());
        }
    }
}
