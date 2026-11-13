# Phase 4 : Intégration CI — Résumé du plan 04-01

**Règles déclarées dans `dependencies.php` (`Rules::noCycles()`, `Rules::stableDependencies($tolerance)`, `Rules::maxDistanceFromMainSequence($max)->for(...)`), évaluées sur le `Report` filtré ; violations à empreinte stable dans les rendus table et JSON, et exit 1 dès qu'une règle est violée.**

## Accomplissements
- `app/Core/Rules/` : interface `Rule` (`id()`, `description()`, `evaluate(Report): list<Violation>`), `Violation` (`final readonly` : `rule`, `fingerprint`, `message`, `?file`), fabriques statiques sur `final readonly class Rules` (l'interface s'appelle `Rule`, d'où `Rules::noCycles()` et non `Rule::noCycles()` comme dans le brief).
- Trois règles :
  - `NoCycles` (`no-cycles`) : une violation par cycle (classes et composants), empreinte `no-cycles:class:A,B` / `no-cycles:component:X,Y` (membres déjà triés par `Cycle`) ; fichier = celui du premier membre présent dans le rapport pour un cycle de classes, `null` sinon.
  - `StableDependencies` (`stable-dependencies`) : une violation par `SdpViolation` dont `delta > tolerance` (strict), empreinte `stable-dependencies:From->To`.
  - `MaxDistanceFromMainSequence` (`max-distance`) : violation si `distance !== null && distance > max` ; `for(string|list<string>)` immuable restreint les composants (tous sans `for`). Empreinte `max-distance:Component`.
  - Messages en anglais, valeurs mesurées arrondies à 2 décimales ; les empreintes n'en dépendent jamais.
- `ClassMetrics::$file` (après `kind`) : chemin relatif à `basePath`. `AnalysisPipeline` rend relatifs, juste après le parsing, les fichiers des `ParsedClass` et des `ParseError` via un seul `relativePath()` (le préfixe `realpath` est calculé une fois). Golden régénéré : le diff n'ajoute que des clés `file`.
- `Config::rules(list<Rule>)` + propriété `rules` (vide par défaut).
- `Renderer::render(Report, list<ReportSection>, ?list<Violation> $violations = null)` : **`null` = aucune règle configurée**, `[]` = règles configurées et toutes respectées.
  - Table : section `Rule violations` (colonnes `Rule`, `Message`, ou `No rule violation.`) après les sections et avant les erreurs, affichée seulement si `$violations !== null`.
  - JSON (schéma toujours `1`) : clé `violations` toujours présente (`[]` sans règle), `[ {rule, fingerprint, message, file} ]`, placée avant `errors`.
- `AnalyzeCommand` : évalue les règles de la config (`evaluateRules()`), rend, puis retourne `FAILURE` (1) s'il y a au moins une violation ; docblock des codes de sortie 0 / 1 / 2 mis à jour.
- `dependencies.php` : composant `Core\Rules` ajouté et règles `noCycles()` + `stableDependencies()` ; le projet reste vert (exit 0, aucun cycle, aucune violation SDP).
- Fixture `tests/Fixtures/Core/Config/rules.php` (projet Shop : `noCycles`, `stableDependencies`, `maxDistanceFromMainSequence(0.5)->for('Shop\Catalog')`).
- Tests : `tests/Core/Rules/` (10 tests), `ConfigTest`, `TableRendererTest`, `JsonRendererTest`, `AnalyzeTest` (exit 0 sans règle, exit 0 sur le projet, exit 1 en table et JSON, exit 2 conservé). Suite complète : **227 passed, 1 skipped** ; `composer healthcheck` vert ; `pint --test` OK.

## Codes de sortie observés
- `php class-dependencies-analyzer analyze` → 0
- `php class-dependencies-analyzer analyze --config=tests/Fixtures/Core/Config/rules.php` → 1 (table et `--format=json`)
- `php class-dependencies-analyzer analyze --format=xml` → 2

## Décisions
- Les fichiers des classes sont relativisés dès le parsing : `Node::$file` devient lui aussi relatif (il n'est lu nulle part ailleurs), ce qui garde un seul point de calcul.
- `file` n'est pas ajouté aux classes du JSON (non demandé) ; il ne sert qu'aux violations (SARIF en 04-03).

## Déviations par rapport au plan
1. **Composant `Core\Rules` ajouté à `dependencies.php`** : sans lui, `App\Core\Rules` aurait été résolu en `App\Core` (profondeur 2), seul module du cœur non nommé.
2. **Tests Feature via `Artisan::call()`** pour les sorties à plusieurs assertions : `expectsOutputToContain` ne vérifie qu'une sous-chaîne par appel à `doWrite`, or le rendu est écrit en une fois.

## Préparation de la suite
- 04-02 (baseline) : s'appuie sur `Violation::$fingerprint` ; l'évaluation est isolée dans `AnalyzeCommand::evaluateRules()`.
- 04-03 (SARIF) : `Violation::$file` est relatif à la racine.

---
*Phase : 04-integration-ci*
*Terminé : 2026-11-13*
