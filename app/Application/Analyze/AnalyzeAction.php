<?php

namespace App\Application\Analyze;

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
        $fileAggregator = $this->fileRepository->find($request->path);
    }
}
