# Phase 2 : Analyseurs — Résumé du plan 02-02

**Détection des cycles de classes et de composants par un Tarjan itératif générique (`StronglyConnectedComponents`), et des violations du Stable Dependencies Principle entre composants (`SdpAnalyzer`), en réutilisant `ComponentAnalyzer` sans recalculer l'instabilité.**

## Accomplissements
- `StronglyConnectedComponents::find(array<string, list<string>> $adjacency): list<list<string>>` : Tarjan itératif (pile d'appels explicite, aucune récursion), indépendant du graphe ; cibles absentes des clés ignorées ; chaque SCC triée, SCC triées par taille décroissante puis par premier membre. Tout l'état de travail est local à l'appel.
- `CycleLevel` (enum `string`) : `Class_ = 'class'`, `Component = 'component'`.
- `Cycle` (`final readonly`) : `list<string> $members`, `CycleLevel $level`, `int $size` (déduit de `members`).
- `CycleAnalyzer(ComponentAnalyzer)::analyze(DependencyGraph): list<Cycle>` : SCC de taille > 1 au niveau classe (adjacence `dependenciesOf`), puis au niveau composant (adjacence `ComponentAnalyzer::dependencies()`).
- `SdpViolation` (`final readonly`) : `from`, `fromInstability`, `to`, `toInstability`, `delta` (= I(cible) − I(source), > 0).
- `SdpAnalyzer(ComponentAnalyzer)::analyze(DependencyGraph): list<SdpViolation>` : violation si I(cible) > I(source), sans tolérance ; tri par delta décroissant, puis `from`, puis `to`. Instabilités lues dans `ComponentAnalyzer::analyze()`.
- Suite complète : **161 passed, 1 skipped** (341 assertions), dont 15 nouveaux tests Core (34 assertions) ; test d'architecture vert ; Pint OK.

## Fichiers créés
- `app/Core/Analysis/StronglyConnectedComponents.php`, `CycleLevel.php`, `Cycle.php`, `CycleAnalyzer.php`
- `app/Core/Analysis/SdpViolation.php`, `SdpAnalyzer.php`
- `tests/Core/Analysis/StronglyConnectedComponentsTest.php` (7 tests), `CycleAnalyzerTest.php` (4 tests), `SdpAnalyzerTest.php` (4 tests)

## Décisions prises
- Pas de gestion des auto-boucles : `GraphBuilder` retire les auto-dépendances, seules les SCC de taille > 1 sont des cycles (la mention « + auto-boucles » de la roadmap est donc sans objet).
- `analyze()` renvoie d'abord les cycles de classes, puis ceux de composants, chacun dans l'ordre déterministe de `StronglyConnectedComponents`.
- Tris par `strcmp` explicite (et non par comparaison de tableaux) pour éviter toute comparaison numérique de chaînes.
- Le test de la chaîne de 50 000 nœuds vérifie le résultat (50 000 SCC singletons), pas le temps ; un test d'anneau de 50 000 nœuds (une seule SCC) complète la preuve de l'itératif.

## Déviations par rapport au plan
Aucune — plan exécuté tel qu'écrit (les tests « cibles absentes » et « anneau de 50 000 nœuds » s'ajoutent aux vérifications prévues).

## Problèmes rencontrés
Aucun.

## Préparation de la suite
- 02-03 : `Report` peut agréger `CouplingAnalyzer`, `ComponentAnalyzer`, `CycleAnalyzer` et `SdpAnalyzer`, tous sans état et construits sur le même `DependencyGraph`.
- La tolérance SDP et la règle `noCycles` restent à la phase 4.

---
*Phase : 02-analyseurs*
*Terminé : 2026-11-13*
