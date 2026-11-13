# php-class-dependencies-analyzer — v1.0

**One-liner** : un graphe de dépendances PHP calculé une fois, exposant les métriques de Robert C. Martin (classe et composant), les cycles et les violations SDP, exploitable en CI.

## Current State (2026-11-13)

**Livré :** v0.7.0 — 4 commandes (`weakness`, `cyclic`, `analyze:class`, `analyze:component`), ~4 200 lignes, Laravel Zero, PHAR committée.

**Problèmes connus :**
- `PhpAstClassDependenciesParser` utilise l'API php-parser v5 alors que le lock est en 4.19 (jerowork impose `^4.15`).
- Ce compte le vendor, Ca non → instabilité biaisée ; « abstractness » non conforme à Martin.
- O(n²·d) sur l'instabilité, arrondis dans le Domain, état mutable à appeler dans le bon ordre.
- `--only`/`--exclude` non combinables et non ancrés ; `--limit` appliqué avant `--min-delta`.
- Exit code toujours 0 → inutilisable en CI.

## v1.0 Goals

**Scope :**
- `DependencyGraph` immuable (index inversé, dépendances internes / externes séparées).
- Parser unique php-parser + `NameResolver` (un seul port : `ClassParser`).
- Métriques : Ca/Ce/I par classe ; Na/Nc/A/I/D par composant (globs).
- SDP entre composants, cycles via Tarjan (SCC).
- Commande unique `analyze --report=… --format=table|json|sarif|html`.
- Config `dependencies.php` (paths, exclude, components, rules), codes de sortie 0/1/2.
- Baseline, cache par hash de fichier, renderer HTML Cytoscape.

**Success Criteria :**
- [x] `php class-dependencies-analyzer analyze app` produit les 4 rapports sur le projet lui-même.
- [x] Exit 1 quand une règle échoue, 0 avec une baseline couvrant les violations existantes, 2 sur erreur.
- [x] SARIF valide (schéma 2.1.0) ; JSON versionné (`"schema": 1`).
- [x] Second run avec cache sans modification : aucun fichier reparsé.
- [x] `App\Core` n'importe ni `Illuminate\*` ni `LaravelZero\*` (test d'architecture Pest).
- [x] Ancien code et jerowork supprimés, php-parser en `^5`, `composer healthcheck` vert.

## Constraints

- PHP ≥ 8.2, Laravel Zero conservé pour la CLI et le build PHAR.
- Nouveau code dans `app/Core` en parallèle de l'ancien ; l'ancien reste fonctionnel jusqu'à la phase 5.
- Tant que jerowork est présent, le parser Core doit fonctionner avec php-parser 4.19 (et v5).
- Aucune nouvelle dépendance Composer sans validation.

## Out of Scope

- Règles de couches « A ne dépend pas de B » (terrain de Deptrac / PHPArkitect).
- Verdicts subjectifs (« good / risky / suffering »).
- Analyse de fonctions hors classes, de fichiers non PHP, support de PHP < 8.2.
