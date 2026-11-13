# Phase 1 : Fondations — Résumé du plan 01-01

**Suite verte (66 tests), parser PhpAst v5 non branché supprimé, namespace `App\Core` créé et protégé par une testsuite `Core` avec trois règles `arch()` (dépendances limitées à `PhpParser`, `strict_types`, php-parser réservé à l'adapter).**

## Accomplissements
- `composer install` exécuté ; aucune nouvelle dépendance, php-parser reste en 4.19.4.
- Parser `PhpAstClassDependenciesParser` (API v5, non branché) et son test supprimés ; fixture `tests/Fixtures/Php85/ModernClass.php` conservée pour 01-02.
- `app/Core/` créé, testsuite `Core` ajoutée et exécutée par `composer test`.
- Test d'architecture actif, vérifié par des fichiers sondes temporaires (dépendances Illuminate, LaravelZero, Laravel\Prompts, Jerowork, `App\Domain`, `PhpParser` hors adapter, fichier sans `strict_types` : tous détectés, puis supprimés).

## Échecs initiaux constatés
Aucun : après `composer install`, `vendor/bin/pest` donnait **65 passed, 1 skipped** (105 assertions). Le test `PhpAstClassDependenciesParserTest` passait, car php-parser 4.18+ fournit déjà `ParserFactory::createForNewestSupportedVersion()`. Le test ignoré (`analyze:class --graph`, ouverture d'un navigateur) l'était déjà avant.

État final : **66 passed, 1 skipped** (98 assertions) : 63 Feature/Unit + 3 Core.

## Fichiers supprimés
- `app/Infrastructure/Analyze/Adapters/PhpParser/AstClassAnalysis.php`
- `app/Infrastructure/Analyze/Adapters/PhpParser/DependencyCollectorVisitor.php`
- `app/Infrastructure/Analyze/Adapters/PhpParser/PhpAstClassDependenciesParser.php`
- `tests/Unit/Infrastructure/Analyze/PhpAstClassDependenciesParserTest.php`

## Fichiers créés/modifiés
- `app/Core/.gitkeep` : squelette du namespace `App\Core` (un namespace vide ne fait pas échouer `arch()`, donc pas besoin d'un fichier réel).
- `tests/Core/ArchitectureTest.php` : règles d'architecture du cœur.
- `phpunit.xml.dist` : testsuite `Core` (`./tests/Core`). Les tests Core n'utilisent pas `Tests\TestCase`.
- `app/Providers/AppServiceProvider.php` : imports morts retirés (`GraphMapper`, `CytoscapeGraphMapper`) ; `FileAggregator` est utilisé, il reste.

## Décisions prises
- `.gitkeep` plutôt que `ClassKind.php` : `arch()` passe sur un namespace vide.
- Le fichier de configuration PHPUnit est `phpunit.xml.dist` (le plan citait `phpunit.xml`).

## Déviations par rapport au plan

### Corrections automatiques

**1. [Règle 1 - Bug] `not->toUse(['Illuminate', 'LaravelZero', 'Laravel', 'Jerowork', …])` ne détecte pas les dépendances vendor**
- **Constaté pendant :** Task 2 (vérification avec `use Illuminate\Support\Str;`)
- **Problème :** Pest 2.36 (pest-plugin-arch) construit la couche de dépendance à partir des répertoires PSR-4 de Composer. Une racine comme `Illuminate`, `LaravelZero` ou `Jerowork` ne correspond à aucun préfixe complet (`Illuminate\Support\`, `LaravelZero\Framework\`…). La couche est donc vide ou partielle et la règle passe toujours. Étendre la liste aux préfixes PSR-4 enregistrés fait scanner `vendor/illuminate/*` et plante (`Trait "Illuminate\Queue\InteractsWithQueue" not found`).
- **Correctif :** la règle devient `arch()->expect('App\Core')->toOnlyUse('PhpParser')`. Elle est plus stricte : le cœur ne peut dépendre que de lui-même, de php-parser et des classes natives PHP. Elle couvre donc le framework, l'ancien code (`App\Domain`, `App\Application`, `App\Infrastructure`, `App\Presenter`, `App\Commands`) et Jerowork. Les règles `toUseStrictTypes()` et `not->toUse('PhpParser')->ignoring('App\Core\Parser\PhpParser')` sont conservées telles quelles. Les deux méthodes existent en Pest 2.36.
- **Fichiers modifiés :** `tests/Core/ArchitectureTest.php`
- **Vérification :** des sondes temporaires (`Illuminate\Support\Str`, `LaravelZero\Framework\Commands\Command`, `Laravel\Prompts\Prompt`, `Jerowork\…\ClassDependencies`, `App\Domain\ValueObjects\Fqcn`) font échouer la règle. `DateTimeInterface` et une classe de `App\Core` passent. `PhpParser\Node` est accepté dans `App\Core\Parser\PhpParser` et refusé ailleurs.
- **Conséquence pour la suite :** toute future dépendance vendor du cœur devra être ajoutée explicitement à `toOnlyUse`.

**2. [Règle 3 - Bloquant] `AppServiceProvider` ne passait pas `pint --test`**
- **Constaté pendant :** vérification finale
- **Problème :** l'ordre des imports et `new_with_parentheses` n'étaient déjà pas conformes avant le plan, et le fichier fait partie des fichiers touchés.
- **Correctif :** `vendor/bin/pint` appliqué à ce seul fichier (imports triés, `new ParserFactory` sans parenthèses).
- **Fichiers modifiés :** `app/Providers/AppServiceProvider.php`
- **Vérification :** `vendor/bin/pint --test` passe sur les fichiers touchés ; les commandes `cyclic`, `weakness` et `analyze:class` fonctionnent toujours.

---

**Total des déviations :** 2 corrections automatiques (1 bug, 1 bloquant), 0 différée.
**Impact sur le plan :** la frontière du cœur est réellement vérifiée (sans la correction 1, la règle principale ne protégeait rien). Pas d'élargissement du périmètre.

## Problèmes rencontrés
Aucun autre.

## Préparation de la suite
- 01-02 peut créer `app/Core/Parser/*` et `app/Core/Parser/PhpParser/*` : le test d'architecture autorise déjà `PhpParser` dans ce seul sous-namespace.
- Le parser legacy (Jerowork + php-parser 4.19) reste branché et fonctionnel jusqu'à la phase 5.

---
*Phase : 01-fondations*
*Terminé : 2026-11-13*
