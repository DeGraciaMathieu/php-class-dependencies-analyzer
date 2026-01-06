<?php

declare(strict_types=1);

namespace App\Presenter\Analyze\Class\Bubble;

use App\Application\Analyze\AnalyzeMetric;

class BubbleMapper
{
    /**
     * @param array<AnalyzeMetric> $metrics
     * @return array<string, array{classes: int, interfaces: int, abstracts: int, total: int, relations: array<string>, metrics: array{efferent_coupling: float, afferent_coupling: float, instability: float, loc_total: int, ccn_total: int}}>
     */
    public function from(array $metrics): array
    {
        $foldersData = [];

        foreach ($metrics as $metric) {
            $folderPath = $this->fqcnToFolderPath($metric->name());
            $dependencies = $metric->dependencies();

            if (!isset($foldersData[$folderPath])) {
                $foldersData[$folderPath] = [
                    'classes' => 0,
                    'interfaces' => 0,
                    'abstracts' => 0,
                    'total' => 0,
                    'relations' => [],
                    'metrics' => [
                        'efferent_coupling' => 0.0,
                        'afferent_coupling' => 0.0,
                        'instability' => 0.0,
                        'loc_total' => 0,
                        'ccn_total' => 0,
                    ],
                ];
            }

            // Compter le type de classe
            if ($metric->isInterface()) {
                $foldersData[$folderPath]['interfaces']++;
            } elseif ($metric->abstract()) {
                $foldersData[$folderPath]['abstracts']++;
            } else {
                $foldersData[$folderPath]['classes']++;
            }

            $foldersData[$folderPath]['total']++;

            // Ajouter les métriques de couplage
            $foldersData[$folderPath]['metrics']['efferent_coupling'] += $metric->efferentCoupling();
            $foldersData[$folderPath]['metrics']['afferent_coupling'] += $metric->afferentCoupling();
            $foldersData[$folderPath]['metrics']['instability'] += $metric->instability();

            // Convertir les dépendances en chemins de dossiers et les ajouter aux relations
            foreach ($dependencies as $dependency) {
                $dependencyFolderPath = $this->fqcnToFolderPath($dependency);
                
                // Ne pas ajouter de relation vers le même dossier
                if ($dependencyFolderPath !== $folderPath && !in_array($dependencyFolderPath, $foldersData[$folderPath]['relations'], true)) {
                    $foldersData[$folderPath]['relations'][] = $dependencyFolderPath;
                }
            }
        }

        // Calculer les moyennes d'instabilité par dossier
        foreach ($foldersData as $folderPath => &$data) {
            $count = $data['total'];
            if ($count > 0) {
                $data['metrics']['instability'] = number_format($data['metrics']['instability'] / $count, 2);
            }
        }

        return $foldersData;
    }

    private function fqcnToFolderPath(string $fqcn): string
    {
        // Séparer le namespace et le nom de classe
        $parts = explode('\\', $fqcn);
        
        // Retirer le nom de classe (dernier élément)
        array_pop($parts);
        
        if (empty($parts)) {
            return '';
        }

        // Convertir en chemin de dossier
        $path = implode('/', $parts);
        
        // Mettre en minuscule la première partie (ex: App -> app)
        $firstSlash = strpos($path, '/');
        if ($firstSlash !== false) {
            $firstPart = substr($path, 0, $firstSlash);
            $rest = substr($path, $firstSlash);
            return strtolower($firstPart) . $rest;
        }

        return strtolower($path);
    }
}

