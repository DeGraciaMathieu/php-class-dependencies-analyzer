# Phase 2 : Analyseurs — Résumé du plan 02-03

**`AnalysisPipeline` (scan → parse → graphe → analyseurs sur le graphe complet → filtrage) produisant un `Report` immuable, figé par un test golden sur un projet fixture vérifié à la main.**

## Accomplissements
- `Report` (`App\Core\Report`) : `classes`, `components`, `cycles`, `sdpViolations`, `errors`, `duplicates`, `fileCount`. Aucune logique de présentation.
- `AnalysisOptions(paths, basePath, excludePaths = [], filter = new Filter, components = [], componentDepth = 2)`.
- `AnalysisPipeline(ClassParser)::run(AnalysisOptions): Report` : seul le parser est injecté ; scanner, builder, resolver et analyseurs sont instanciés par le pipeline.
- Filtrage appliqué après calcul : classes par FQCN ; composants si une de leurs classes est retenue ; cycles classe si un membre est retenu, cycles composant si un membre est un composant retenu ; SDP si `from` ou `to` est retenu. `errors`, `duplicates`, `fileCount` non filtrés.
- Projet fixture `Shop\` (11 classes, 3 composants) + `golden-report.json` (`UPDATE_GOLDEN=1` pour régénérer) ; test de mutation effectué (golden en échec puis restauré).
- Suite complète : **171 passed, 1 skipped** (366 assertions).

## Calcul du golden (vérifié à la main)

| Composant | Nc | Na | A | Ca | Ce | I | D |
|---|---|---|---|---|---|---|---|
| Shop\Billing | 3 (trait exclu) | 1 | 1/3 | 1 | 1 | 0.5 | 1/6 |
| Shop\Catalog | 3 | 1 | 1/3 | 2 | 0 | 0 | 2/3 |
| Shop\Ordering | 4 (enum inclus) | 1 | 0.25 | 1 | 4 | 0.8 | 0.05 |

Cycles : classes `[Category, Product]` ; composants `[Shop\Billing, Shop\Ordering]`. SDP : `Billing (0.5) → Ordering (0.8)`, Δ 0.3. Erreur : `Parser/Invalid.php`. 12 fichiers.

## Validation (checkpoint)
Run sur `app/` : 156 classes, 0 erreur, 7 composants, 0 cycle, 0 violation SDP (chaque couche dépend d'une couche plus stable).
Écarts expliqués avec l'existant :
- `cyclic` signalait 2 faux cycles (`Fqcn → Fqcn`, `ClassDependencies → ClassDependencies`) : auto-références, ignorées par le nouveau graphe.
- `weakness` comparait des classes ; le SDP compare désormais des composants.
- `App\Core` (isolé, Ca = Ce = 0) ressortait avec D = 0.96, faux signal.

Décisions de l'utilisateur :
1. **Composant non mesurable** : `instability` null quand Ca + Ce = 0, `abstractness` null quand Nc = 0, `distance` null si A ou I est null ; ignoré par le SDP.
2. **Pas de SDP au niveau classe** : composants seulement (les métriques classe restent dans le rapport).

## Fichiers créés/modifiés
- `app/Core/Report/Report.php`, `app/Core/Analysis/AnalysisOptions.php`, `app/Core/Analysis/AnalysisPipeline.php`
- `app/Core/Analysis/ComponentMetrics.php`, `ComponentAnalyzer.php`, `SdpAnalyzer.php` : A/I/D nullables
- `tests/Core/Analysis/AnalysisPipelineTest.php`, `GoldenReportTest.php`, `golden-report.json`, `ComponentAnalyzerTest.php`
- `tests/Fixtures/Core/Project/**` (11 classes)

## Déviations par rapport au plan
1. **Ordre des paramètres d'`AnalysisOptions`** : `basePath` placé en 2ᵉ position (paramètre obligatoire après un optionnel déprécié en PHP).
2. **Fixture ajusté** pour produire réellement une violation SDP (I(Billing) ≠ I(Ordering)).
3. **[Décision checkpoint] Métriques nullables** pour les composants non mesurables (voir ci-dessus) ; le test « traits seuls » est remplacé par deux tests (traits seuls, composant isolé).

**Impact sur le plan :** les renderers (phase 3) devront afficher `—` pour une métrique null ; les règles de distance (phase 4) ignoreront ces composants.

## Préparation de la suite
- Phase 3 : `AnalyzeCommand` n'a qu'à construire `AnalysisOptions` depuis la config et passer le `Report` aux renderers.
- La profondeur 2 regroupe tout `App\Core` en un composant : la config `components` de la phase 3 permettra de le découper.

---
*Phase : 02-analyseurs*
*Terminé : 2026-11-13*
