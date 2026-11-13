# Phase 3 : CLI & configuration — Résumé du plan 03-01

**`Config` immuable (builder fluide retourné par `dependencies.php`) convertie en `AnalysisOptions`, et `ConfigLoader` qui transforme toute erreur de configuration en `ConfigException` explicite.**

## Accomplissements
- `Config` (`App\Core\Config`) : `final readonly`, constructeur privé, `Config::make()` puis `paths()`, `exclude()`, `only()`, `ignore()`, `components()`, `componentDepth()` ; chaque méthode retourne une nouvelle instance (`with()` privé reconstruisant via `get_object_vars`).
- Lecture des valeurs via les propriétés publiques `readonly` (`$config->paths`, `->exclude`, `->only`, `->ignore`, `->components`, `->componentDepth`), pour la fusion avec la CLI en 03-03. Défauts : tout vide, `componentDepth = 2`.
- `toOptions(string $basePath): AnalysisOptions` : `paths` vide → `ConfigException` ; composants et profondeur validés en instanciant un `ComponentResolver` (seule source de validation), `InvalidArgumentException` convertie en `ConfigException` (`previous` conservé). `only`/`ignore` → `Filter(only, exclude)`.
- `ConfigLoader` : `find(string $directory): ?string` (`<dir>/dependencies.php` ou null) et `load(string $file): Config` (fichier absent, type de retour invalide avec `get_debug_type`, exception levée par le fichier enveloppée avec `previous`). Le `require` s'exécute dans une closure statique.
- `ConfigException extends RuntimeException`.
- Un `dependencies.php` minimal :
  ```php
  <?php

  use App\Core\Config\Config;

  return Config::make()->paths(['app']);
  ```
- Suite complète : **183 passed, 1 skipped** (403 assertions), architecture incluse ; Pint OK sur les nouveaux fichiers.

## Fichiers créés/modifiés
- `app/Core/Config/Config.php`, `ConfigLoader.php`, `ConfigException.php`
- `tests/Core/Config/ConfigTest.php` (6 tests), `ConfigLoaderTest.php` (6 tests)
- `tests/Fixtures/Core/Config/valid/dependencies.php`, `returns-array.php`, `throws.php`

## Décisions prises
- Getters = propriétés publiques `readonly` (même style qu'`AnalysisOptions`) plutôt que des méthodes `getX()` : les noms de méthodes fluides sont déjà pris par les setters.
- `ConfigLoader` est une classe à méthodes d'instance (injectable dans la commande) ; constante `ConfigLoader::FILENAME = 'dependencies.php'`.

## Déviations par rapport au plan
Aucune.

## Préparation de la suite
- 03-03 : la commande cherche `dependencies.php` via `find()` (ou `--config`), charge la `Config`, applique les options CLI par-dessus (`->paths()`, `->only()`, …) puis appelle `toOptions($basePath)`.

---
*Phase : 03-cli-configuration*
*Terminé : 2026-11-13*
