# Roadmap: php-class-dependencies-analyzer v1.0

## Overview

On construit un cœur pur (`app/Core`) à côté de l'ancien code : parser → graphe immuable → analyseurs → `Report`. On l'expose ensuite via une commande unique `analyze` et une config `dependencies.php`. On ajoute les fonctions CI (règles, baseline, SARIF), puis cache et HTML. Enfin, on supprime l'ancien code et on passe à php-parser v5.

## Workflow Git

- **Une branche et une PR par phase** : `v1/01-fondations`, `v1/02-analyseurs`, `v1/03-cli-configuration`, `v1/04-integration-ci`, `v1/05-finitions-migration`.
- Chaque branche part de `main` une fois la PR de la phase précédente mergée ; sinon elle part de la branche précédente (PR empilée, base = branche précédente).
- Un commit par plan au minimum (style des messages : anglais, impératif à la 3ᵉ personne, ex. `Adds dependency graph builder`).
- La PR est ouverte en fin de phase (`gh pr create`), avec en description les SUMMARY des plans de la phase. Le commentaire `What moved` sert à relire la PR.
- La PR de la phase 1 embarque aussi la mise en place : `.planning/` et le workflow `what-moved`.

## Phases

- [x] **Phase 1 : Fondations** — parser unique, scan des sources, `DependencyGraph` immuable
- [x] **Phase 2 : Analyseurs** — couplage classe, métriques composant, Tarjan, SDP, `Report`
- [x] **Phase 3 : CLI & configuration** — `dependencies.php`, commande `analyze`, renderers table/json
- [ ] **Phase 4 : Intégration CI** — règles, codes de sortie, baseline, SARIF
- [ ] **Phase 5 : Finitions & migration** — cache, HTML Cytoscape, suppression de l'ancien code, release

## Phase Details

### Phase 1 : Fondations
**Goal** : à partir d'un dossier, obtenir un `DependencyGraph` correct et testé, sans dépendance au framework.
**Depends on** : rien
**Plans** : 3

- [x] 01-01 : Socle — suite de tests verte, squelette `app/Core`, test d'architecture
- [x] 01-02 : Parser — port `ClassParser`, `ParsedClass`, adapter php-parser + `SourceScanner`
- [x] 01-03 : Graphe — `DependencyGraph`, `GraphBuilder`, `Filter` (globs ancrés)

### Phase 2 : Analyseurs
**Goal** : toutes les métriques et détections calculées sur le graphe, réunies dans un `Report` immuable.
**Depends on** : Phase 1
**Plans** : 3

- [x] 02-01 : `CouplingAnalyzer` (Ca, Ce interne/externe, I) + `ComponentResolver` + `ComponentAnalyzer` (Na, Nc, A, I, D)
- [x] 02-02 : `CycleAnalyzer` (Tarjan itératif, SCC de taille > 1, classes et composants) + `SdpAnalyzer` (entre composants)
- [x] 02-03 : `Report` + `AnalysisPipeline` (scan → parse → graph → analyzers) + test golden sur un projet fixture

### Phase 3 : CLI & configuration
**Goal** : `php class-dependencies-analyzer analyze <path>` utilisable, avec ou sans `dependencies.php`.
**Depends on** : Phase 2
**Plans** : 3

- [x] 03-01 : `Config` (builder fluide immuable) + `ConfigLoader` (`dependencies.php`, erreurs explicites)
- [x] 03-02 : Renderers `table` et `json` (schema 1), sections `classes` / `components` / `cycles` / `sdp`
- [x] 03-03 : Commande `analyze` (`--config`, `--report`, `--format`, `--output`, `--only`, `--exclude`), exit 0/2, tests Feature, healthcheck — checkpoint

### Phase 4 : Intégration CI
**Goal** : l'outil peut faire échouer une CI de façon fiable et progressive.
**Depends on** : Phase 3
**Plans** : 3

- [x] 04-01 : Règles (`noCycles`, `stableDependencies(tolerance)`, `maxDistanceFromMainSequence(max)->for()`), `Violation` à identifiant stable, exit 1
- [ ] 04-02 : Baseline (`--generate-baseline`, `--baseline`, entrées obsolètes signalées)
- [ ] 04-03 : Renderer SARIF 2.1.0 + exemple de workflow GitHub dans la doc

### Phase 5 : Finitions & migration
**Goal** : v1.0 publiable — performante, visuelle, sans code legacy.
**Depends on** : Phase 4
**Plans** : 3

- [ ] 05-01 : Cache par hash (clé : contenu + version outil + version parser), `--no-cache`, `--clear-cache`
- [ ] 05-02 : Renderer HTML Cytoscape (classes et composants, SCC en surbrillance) — checkpoint visuel
- [ ] 05-03 : Suppression de l'ancien code et de jerowork, php-parser `^5`, `UPGRADE.md`, README, healthcheck, build PHAR — checkpoint release

## Cible de structure

```
app/Core/
├── Parser/      ClassParser (port), ParsedClass, ClassKind, ParseResult, SourceScanner
│   └── PhpParser/   PhpParserClassParser, DependencyCollector (adapter)
├── Graph/       DependencyGraph, Node, GraphBuilder, Filter
├── Analysis/    CouplingAnalyzer, ComponentResolver, ComponentAnalyzer, CycleAnalyzer, SdpAnalyzer
├── Report/      Report + sections (lecture seule)
├── Config/      Config, ConfigLoader
├── Rules/       Rule, Violation, règles concrètes, Baseline
├── Rendering/   Renderer, TableRenderer, JsonRenderer, SarifRenderer, HtmlRenderer
└── Cache/       ParseCache
app/Commands/AnalyzeCommand.php   (seul point d'entrée Laravel Zero du nouveau code)
```

## Progress

| Phase | Plans Complete | Status | PR | Completed |
|-------|----------------|--------|----|-----------|
| 1. Fondations | 3/3 | Complete | #8 | 2026-11-13 |
| 2. Analyseurs | 3/3 | Complete | #9 | 2026-11-13 |
| 3. CLI & configuration | 3/3 | Complete | #10 | 2026-11-13 |
| 4. Intégration CI | 1/3 | In progress | - | - |
| 5. Finitions & migration | 0/3 | Not started | - | - |
