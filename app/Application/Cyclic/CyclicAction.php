<?php

namespace App\Application\Cyclic;

use Throwable;
use App\Application\Cyclic\CyclicRequest;
use App\Application\Cyclic\CyclicPresenter;
use App\Application\Cyclic\CyclicResponseMapper;
use App\Domain\Ports\Repositories\FileRepository;

class CyclicAction
{
    public function __construct(
        private FileRepository $fileRepository,
        private CyclicResponseMapper $mapper,
    ) {}

    public function execute(CyclicRequest $request, CyclicPresenter $presenter): void
    {
        try {

            $presenter->hello();

            $fileAggregator = $this->fileRepository->find($request->path);

            $dependencyAggregator = $fileAggregator->getAllDependencies();

            $dependencyAggregator->filterClasses($request->only, $request->exclude);

            $cycles = $dependencyAggregator->detectCycles();

            $presenter->present(
                $this->mapper->from($cycles),
            );

        } catch (Throwable $e) {
            $presenter->error($e);
        }
    }
}
