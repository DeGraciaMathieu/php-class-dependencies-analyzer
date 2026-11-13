# Phase 1 : Fondations — Résumé du plan 01-03

**`DependencyGraph` immuable construit en O(n·d) par `GraphBuilder`, avec index inversé et séparation interne / externe / filtrée ; `Filter` à globs ancrés combinant `only` et `exclude`.**

## Accomplissements
- `Node` (`final readonly`) : FQCN, `ClassKind`, fichier.
- `DependencyGraph` (`final readonly`, aucune méthode mutante) : `nodes()`, `has()`, `node()`, `dependenciesOf()`, `dependentsOf()`, `externalDependenciesOf()`, `filteredOutDependenciesOf()`, `efferent()`, `afferent()`, `count()`, `duplicates()`, `subgraph(Filter)`.
- `GraphBuilder::build(iterable<ParsedClass>)` : un passage sur des clés de tableau (pas d'`in_array`), nœuds triés (`ksort`), listes triées (`SORT_STRING`), auto-dépendances retirées, doublon de FQCN : premier gardé, signalé dans `duplicates()`.
- `Filter` : glob → `#^…$#` (`*` = `.+`, `preg_quote` avec `#`), `only` puis `exclude`, chaînes vides ignorées.
- `subgraph()` : dépendances vers des nœuds écartés rangées dans `filteredOut` (jamais dans `external`), arêtes entrantes depuis des nœuds écartés retirées de `in`, graphe d'origine intact.
- Suite complète : **123 passed, 1 skipped** (242 assertions), dont 60 tests Core ; Pint OK.

## Validation (checkpoint)
Graphe construit sur `app/` : 142 fichiers → 142 nœuds, 0 erreur, 0 doublon. Top Ca : `AnalyzePresenter` (11), `AnalyzeMetric` (10), `Transformer` (9). Les classes jerowork apparaissent en externe sans impacter Ce. Sous-graphe `App\Core\*` : 12 nœuds.

API validée par l'utilisateur. Décision : **les métriques dépendant du graphe complet (Ca) sont calculées avant filtrage** ; le sous-graphe reste cohérent (comportement actuel, documenté dans le docblock de `subgraph()`).

## Fichiers créés
- `app/Core/Graph/Node.php`, `DependencyGraph.php`, `GraphBuilder.php`, `Filter.php`
- `tests/Core/Graph/DependencyGraphTest.php` (15 tests, dont volume 5 000 × 10), `tests/Core/Graph/FilterTest.php` (7 tests)

## Décisions prises
- Méthodes `*Of()` sur FQCN inconnu → `[]` ; seul `node()` lève `InvalidArgumentException`.
- `count()` simple, sans implémenter `Countable`.
- Constructeur de `DependencyGraph` public (utilisé par `GraphBuilder` et `subgraph()`).
- `subgraph()` ne garde dans `duplicates()` que les FQCN retenus ; `filteredOut` se cumule sur des sous-graphes successifs.

## Déviations par rapport au plan

**1. [Ajustement de test] Graphe de test à 5 classes**
- Une classe `D → A` a été ajoutée pour couvrir un index inversé à plusieurs entrées : `afferent('A') === 2` (au lieu de 1 prévu), `afferent('B') === 1` vérifié en plus.

---

**Total des déviations :** 1 mineure.
**Impact sur le plan :** aucun.

## Problèmes rencontrés
Aucun.

## Préparation de la suite
- Phase 2 : calculer Ca/Ce/I sur le graphe complet, puis appliquer `Filter` à l'affichage.
- `externalDependenciesOf()` mélange vendor et classes natives PHP ; à distinguer en phase 2 seulement si une métrique l'exige.

---
*Phase : 01-fondations*
*Terminé : 2026-11-13*
