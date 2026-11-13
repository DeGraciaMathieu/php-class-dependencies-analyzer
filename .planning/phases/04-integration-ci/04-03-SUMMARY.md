# Phase 4 : Intégration CI — Résumé du plan 04-03

**Rendu SARIF 2.1.0 (`--format=sarif`) prêt pour `upload-sarif`, guide `docs/ci.md`, et correctif d'architecture détecté par l'outil sur lui-même : `Violation` et `BaselineResult` rejoignent `App\Core\Report`, la règle SDP du projet redevient stricte.**

## Accomplissements
- `SarifRenderer(string $toolVersion, string $fallbackLocation)` :
  - `tool.driver` : nom, version (`config('app.version')`), `informationUri`, une entrée `rules` par id de règle présent (+ `parse-error` si besoin).
  - Un résultat `error` par violation (après baseline) avec `partialFingerprints["dependencyViolation/v1"]` ; un résultat `warning` `parse-error` par erreur de parsing.
  - Toujours une `physicalLocation` : fichier du cycle de classes, sinon fichier de config utilisé, sinon premier chemin analysé.
  - Les sections de métriques ne sont pas reprises.
- `Violation` porte une `?string $ruleDescription` (remplie par chaque règle) pour le `shortDescription` SARIF, sans faire dépendre les renderers de `Rule`.
- `--format=sarif` dans `AnalyzeCommand` (renderer créé après le chargement de la config).
- `docs/ci.md` (anglais) : `dependencies.php` commenté, options, codes de sortie, baseline, formats, workflow GitHub Actions (`upload-sarif@v3`, `if: always()`), variante GitLab (JSON en artefact). Section « v1: `analyze` command » ajoutée au README.
- Suite complète : **256 passed, 1 skipped** ; `composer healthcheck` vert ; `analyze` sur le projet → exit 0.

## Validation (checkpoint)
Parcours CI validé sur la fixture Shop : règles violées → 1 ; `--generate-baseline` → 0 ; relance avec baseline → 0 (`4 baselined, 0 stale`) ; nouvelle violation seule → 1 ; baseline corrompue / format inconnu → 2.

**Violation détectée par l'outil sur lui-même** : `Core\Rules (I=0.50) depends on the less stable Core\Analysis (I=0.59)`. Cause : `Violation` et `BaselineResult` (données de sortie) vivaient dans `Core\Rules`, et chaque renderer en dépendait, ce qui stabilisait artificiellement `Core\Rules` (d'où la tolérance 0.05 ajoutée en 04-02).
Décision de l'utilisateur : **déplacer `Violation` et `BaselineResult` dans `App\Core\Report`** et revenir à `Rules::stableDependencies()` strict. Résultat : aucune violation, `Core\Rules` I = 0.88.

## Fichiers créés/modifiés
- `app/Core/Rendering/SarifRenderer.php`, `tests/Core/Rendering/SarifRendererTest.php`
- `app/Core/Report/Violation.php`, `app/Core/Report/BaselineResult.php` (déplacés depuis `App\Core\Rules`)
- `app/Core/Rules/*` (imports, `ruleDescription`), `app/Core/Rendering/*`, `app/Commands/AnalyzeCommand.php`, `dependencies.php` (tolérance retirée)
- `docs/ci.md`, `README.md`, tests des règles, renderers et Feature

## Déviations par rapport au plan
1. **[Décision checkpoint] Déplacement de `Violation` / `BaselineResult` vers `App\Core\Report`** et retrait de la tolérance 0.05 introduite en 04-02.
2. **`ruleDescription` sur `Violation`** : trois attentes de tests de règles mises à jour.
3. **Pas de test Feature du repli « premier chemin »** : sans fichier de config il n'y a pas de règle, donc rien à localiser ; couvert au niveau du renderer.
4. **Exemple de la doc** : `--generate-baseline=baseline.json` (un dossier `build/` absent faisait échouer l'exemple).

## Limites connues
- Les dépendances déclarées uniquement en docblock ne sont pas collectées (`Core\Report` a Ce = 0 alors que `Report` documente des `ClassMetrics`).
- Les workflows de la doc téléchargent la PHAR de `main`, qui n'aura `analyze` qu'après la release v1 (phase 5).

---
*Phase : 04-integration-ci*
*Terminé : 2026-11-13*
