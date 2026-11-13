# Phase 2 : Analyseurs — Résumé du plan 02-01

**`CouplingAnalyzer` (Ca, Ce interne, dépendances externes, I) par classe et `ComponentAnalyzer` (Nc, Na, A, Ca, Ce, I, D selon Martin) par composant, résolus par `ComponentResolver` (globs nommés puis repli sur N segments de namespace), en `float` bruts et sans état.**

## Accomplissements
- `ClassMetrics` (`final readonly`) : `fqcn`, `kind`, `afferent`, `efferent`, `externalCount`, `instability`.
- `CouplingAnalyzer::analyze(DependencyGraph): array<string, ClassMetrics>` : un passage sur les nœuds (déjà triés par FQCN), via `afferent()` / `efferent()` / `externalDependenciesOf()` ; I = Ce / (Ca + Ce) avec Ce interne, 0 si Ca + Ce = 0.
- `ComponentResolver(array $definitions = [], int $depth = 2)::resolve(string): string` : une instance `Filter` (`only`) par définition, la première qui correspond l'emporte ; sinon les `depth` premiers segments du namespace (namespace complet s'il est plus court, `\` sans namespace).
- `ComponentMetrics` (`final readonly`) : `name`, `classes` (traits inclus), `classCount`, `abstractCount`, `abstractness`, `afferent`, `efferent`, `instability`, `distance`.
- `ComponentAnalyzer(ComponentResolver)` : `analyze(DependencyGraph): array<string, ComponentMetrics>` trié par nom ; `dependencies(DependencyGraph): array<string, list<string>>` (composant → composants dont il dépend, listes triées, entrée vide pour un composant sans dépendance). Ca/Ce en classes distinctes, arêtes internes ignorées, traits hors Nc/Na mais comptés pour le couplage.
- Définitions de Ca/Ce/I/A/D dans les docblocks de `ClassMetrics` et `ComponentMetrics`.
- Suite complète : **146 passed, 1 skipped** (307 assertions), dont 23 nouveaux tests Core (65 assertions) ; test d'architecture vert ; Pint OK.

## Fichiers créés
- `app/Core/Analysis/ClassMetrics.php`, `CouplingAnalyzer.php`
- `app/Core/Analysis/ComponentResolver.php`, `ComponentMetrics.php`, `ComponentAnalyzer.php`
- `tests/Core/Analysis/CouplingAnalyzerTest.php` (5 tests), `ComponentResolverTest.php` (10 tests, dataset inclus), `ComponentAnalyzerTest.php` (8 tests)

## Décisions prises
- Pas de tri supplémentaire dans `CouplingAnalyzer` : l'ordre des nœuds du graphe (trié par FQCN) est conservé.
- Un composant uniquement composé de traits : Nc = 0 → A = 0, I = 0, D = 1.
- Les enums comptent dans Nc (seuls les traits sont exclus).
- Le calcul de I (une ligne) est dupliqué dans les deux analyseurs plutôt que d'introduire une classe utilitaire hors plan.

## Déviations par rapport au plan

### Problèmes corrigés automatiquement

**1. [Règle 2 - Manque critique] Validation des entrées de `ComponentResolver`**
- **Trouvé pendant :** Tâche 2 (ComponentResolver)
- **Problème :** `Filter` ignore les globs vides et correspond alors à tout : une définition sans glob (`''`, `[]`, `['']`) aurait absorbé toutes les classes ; une profondeur < 1 n'a pas de sens.
- **Correctif :** `InvalidArgumentException` levée à la construction dans ces deux cas.
- **Fichiers modifiés :** `app/Core/Analysis/ComponentResolver.php`
- **Vérification :** 2 tests (dont un dataset de 3 cas) dans `ComponentResolverTest.php`.

---

**Total des déviations :** 1 (règle 2).
**Impact sur le plan :** aucun élargissement de périmètre ; la validation complète de la configuration reste en phase 3.

## Problèmes rencontrés
Aucun.

## Préparation de la suite
- 02-02 : `SdpAnalyzer` peut s'appuyer sur `ComponentAnalyzer::dependencies()` et sur l'instabilité de `ComponentMetrics` ; les cycles de composants sur le même graphe de composants.
- `GraphBuilder` retire les auto-dépendances : les auto-boucles prévues pour `CycleAnalyzer` ne seront pas visibles dans le graphe actuel.

---
*Phase : 02-analyseurs*
*Terminé : 2026-11-13*
