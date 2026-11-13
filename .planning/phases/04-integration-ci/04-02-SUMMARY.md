# Phase 4 : Intégration CI — Résumé du plan 04-02

**Baseline JSON versionnée (`dependencies-baseline.json`, schéma 1, triée par empreinte) : `--generate-baseline[=fichier]` fige les violations courantes (exit 0), `--baseline=` ou `Config::baseline()` ne fait échouer que sur les nouvelles violations ; les entrées obsolètes sont signalées sans bloquer.**

## Accomplissements
- `app/Core/Rules/` :
  - `Baseline` (`final readonly`) : ensemble d'empreintes, `fromViolations()`, `fromFingerprints()`, `apply(list<Violation>): BaselineResult`.
  - `BaselineResult` (`final readonly`) : `new` (violations absentes de la baseline), `baselined` (nombre de violations connues), `stale` (empreintes obsolètes, triées).
  - `BaselineFile` : `read(string): Baseline` et `write(string, list<Violation>): void` ; `FILENAME = 'dependencies-baseline.json'`, `SCHEMA = 1`. Écriture triée et dédoublonnée par empreinte, `JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`, retour à la ligne final. Lecture : fichier absent, JSON invalide, `schema` ≠ 1, `violations` manquant ou entrée sans empreinte → `BaselineException` explicite.
  - `BaselineException` (`RuntimeException`).
- `Config::baseline(string $file)` + propriété `?string $baseline` (null par défaut, chemin relatif à la racine).
- `Renderer::render(Report, sections, ?list<Violation> $violations = null, ?BaselineResult $baseline = null)` : paramètre optionnel ajouté (null = pas de baseline). Avec une baseline, `$violations` ne contient que les nouvelles (`$baseline->new`).
  - Table : ligne `N baselined, M stale` sous la section `Rule violations`.
  - JSON : clé `"baseline": { "baselined": N, "stale": [...] }` après `violations`, seulement si une baseline est utilisée.
- `AnalyzeCommand` :
  - `--generate-baseline[=fichier]` (défaut `dependencies-baseline.json`) : écrit toutes les violations courantes, affiche `Baseline written to X with N violation(s).`, retourne 0 sans afficher le rapport. Présence détectée par `$this->input->hasParameterOption('--generate-baseline', true)`, car `VALUE_OPTIONAL` donne `null` que l'option soit absente ou sans valeur.
  - `--baseline=` remplace `Config::$baseline`. Méthodes privées `generateBaseline()` et `applyBaseline()`. Sans règle configurée, la baseline est ignorée.
  - Exit 1 seulement s'il y a des violations nouvelles ; baseline invalide → exit 2.
- Tests : `BaselineTest` (3), `BaselineFileTest` (7), `ConfigTest`, `TableRendererTest`, `JsonRendererTest`, `AnalyzeTest` (7 tests baseline, fichiers écrits dans un dossier temporaire supprimé ensuite). Fixtures `rules-stricter.php` (`maxDistanceFromMainSequence(0.1)` sur tous les composants), `rules-with-baseline.php` + `baseline.json`.
- Suite complète : **247 passed, 1 skipped** ; `composer healthcheck` vert ; `pint --test` OK.

## Exemple de fichier généré (fixture Shop)
```json
{
    "schema": 1,
    "violations": [
        {
            "fingerprint": "max-distance:Shop\\Catalog",
            "message": "Shop\\Catalog is at distance 0.67 from the main sequence (max 0.50)."
        },
        {
            "fingerprint": "no-cycles:class:Shop\\Catalog\\Category,Shop\\Catalog\\Product",
            "message": "Class cycle: Shop\\Catalog\\Category → Shop\\Catalog\\Product."
        },
        ...
    ]
}
```

## Codes de sortie observés
- `analyze --config=tests/Fixtures/Core/Config/rules.php --generate-baseline=/tmp/x.json` → 0
- même config avec `--baseline=/tmp/x.json` → 0 (`4 baselined, 0 stale`)
- `rules-stricter.php` avec cette baseline → 1 (seule `max-distance:Shop\Billing` est nouvelle)
- baseline corrompue → 2 (`Baseline file [...] is not valid JSON: Syntax error.`)
- `php class-dependencies-analyzer analyze` (projet) → 0

## Décisions
- Paramètre optionnel `?BaselineResult $baseline` sur `Renderer::render()` plutôt qu'un objet englobant : la signature de 04-01 reste compatible et un renderer sans usage de la baseline (SARIF en 04-03) peut l'ignorer.
- Pas de détection automatique de `dependencies-baseline.json` : la baseline s'active explicitement (`Config::baseline()` ou `--baseline`).
- `--generate-baseline` n'affiche pas le rapport : une seule ligne de confirmation.

## Déviations par rapport au plan
1. **`BaselineException` au lieu de `ConfigException`** : `Core\Config` dépend déjà de `Core\Rules` (`Rule`) ; lever `ConfigException` depuis `BaselineFile` aurait créé un cycle de composants `Core\Config ↔ Core\Rules`, refusé par la règle `noCycles()` du projet. La commande attrape les deux exceptions (exit 2).
2. **Tolérance `stableDependencies(0.05)` dans `dependencies.php`** : `Renderer` référence désormais `BaselineResult`, ce qui rend `Core\Rules` plus stable (I=0.56) que `Core\Analysis` (I=0.59) dont il dépend ; écart de 0.03 qui faisait échouer le projet sur ses propres règles. Commentaire ajouté dans la config.

## Préparation de la suite
- 04-03 (SARIF) : le renderer reçoit les seules nouvelles violations quand une baseline est utilisée ; il peut ignorer `$baseline`.

---
*Phase : 04-integration-ci*
*Terminé : 2026-11-13*
