# Phase 5 : Finitions & migration — Résumé du plan 05-03

**Ancien code supprimé (158 fichiers, ~5 600 lignes), jerowork et php-file-explorer retirés, php-parser 5.9.0 sans modification du parser Core, `UPGRADE.md` et README v1. Milestone v1.0 prête : tous les critères du brief sont remplis.**

## Accomplissements
- Supprimés (`git rm`) : `app/Presenter` (57), `app/Commands` legacy (21, dont `AbstractCommand`), `app/Infrastructure` (19), `app/Application` (16), `app/Domain` (15), `app/Providers/Custom`, `resources/views` (vues Blade de graphe), `tests/Unit` (17), `tests/Builders` (4), Feature legacy (3), `tests/CreatesApplication.php`, `config/view.php`, `storage/framework/views/.gitignore`.
- `AnalyzeCommand` étend directement `Command` (`optionToList` devenu privé) ; `AppServiceProvider` ne garde que le binding `ClassParser`.
- Composer :
  - retirés de `require` : `jerowork/class-dependencies-parser`, `degraciamathieu/php-file-explorer`, `illuminate/view`, `laravel/prompts` ;
  - `nikic/php-parser` `^5.0` → **v5.9.0** installée ; le parser Core passe tous ses tests sans modification ;
  - `illuminate/view` et `laravel/prompts` restent installés (exigés par `illuminate/console` et `laravel-zero/framework`), mais plus aucun code ne les référence ;
  - mise à jour restreinte aux paquets concernés (sans `-W`).
- `phpunit.xml.dist` / `tests/Pest.php` : suite `Unit` et builders retirés ; `box.json` n'embarque plus `resources`.
- `dependencies.php` : composants `Core\*`, `Commands`, `Providers` ; `.github/what-moved.json` : contextes legacy retirés.
- `healthcheck` : `analyze` en table, json, sarif et html (vers `.healthcheck/`, ignoré par git), `--report` complet, `pest -p`, tous en `--no-cache`.
- `UPGRADE.md` (correspondance des commandes, options supprimées, globs, configuration, changements de métriques, codes de sortie, baseline) ; README réécrit autour d'`analyze` (en-tête, badges, liens et notes conservés).
- PHAR construite localement (`app:build --build-version=1.0.0-dev`, ~22 Mo) : `list` → seulement `analyze` ; `analyze --format=json` → exit 0. La PHAR suivie dans `builds/` est inchangée.
- Suite complète : **224 passed** (plus aucun skip) ; `pint --test` sur tout le projet ; `composer validate` et `composer healthcheck` verts ; `analyze` sur le projet → exit 0, aucune violation.

## Critères du brief
Tous cochés dans `.planning/BRIEF.md` : 4 rapports, exit 0/1/2 avec baseline, SARIF 2.1.0 et JSON `schema: 1`, zéro reparse avec cache, cœur sans framework (test d'architecture), legacy supprimé + php-parser `^5` + healthcheck vert.

## Décision de release (checkpoint)
**L'utilisateur pose lui-même le tag `1.0.0` après le merge de la PR de la phase 5** ; le workflow `build.yml` construit et committe la PHAR.

## Déviations par rapport au plan
1. **[Règle 3] `pint.json`** (preset laravel, exclusion de la fixture volontairement invalide `Fixtures/Core/Parser/Invalid.php`).
2. **[Règle 1]** Formatage Pint de `tests/Fixtures/Php85/ModernClass.php` et `tests/TestCase.php`.
3. Mise à jour Composer sans `-W` pour ne pas faire monter ~30 paquets non concernés.
4. Suppression de `tests/CreatesApplication.php`, redondant avec le `TestCase` de Laravel Zero.

---
*Phase : 05-finitions-migration*
*Terminé : 2026-11-13*
