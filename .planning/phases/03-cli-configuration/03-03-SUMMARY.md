# Phase 3 : CLI & configuration — Résumé du plan 03-03

**Commande unique `analyze`, seul point d'entrée Laravel Zero du nouveau cœur : config `dependencies.php` surchargée par la CLI, rendus `table` / `json`, codes de sortie 0 et 2, erreurs sur stderr.**

## Accomplissements
- `app/Commands/AnalyzeCommand.php` : `analyze {paths?*} {--config=} {--report=} {--format=table} {--output=} {--only=} {--exclude=}`.
  - Config : `--config`, sinon `dependencies.php` du cwd, sinon `Config::make()` ; `paths` en argument remplacent ceux de la config, `--only` / `--exclude` remplacent `only` / `ignore`.
  - Format et sections validés avant le pipeline ; `--output` écrit dans un fichier et confirme sur une ligne.
  - Seules `ConfigException` et `InvalidArgumentException` sont attrapées → message sur stderr, exit 2 (`ERROR`). Le code 1 est réservé aux violations de règles (phase 4).
- Binding `ClassParser` → `PhpParserClassParser` dans `AppServiceProvider`.
- `dependencies.php` à la racine : `paths(['app'])` et `App\Core` découpé en 6 composants nommés ; les couches historiques résolues à la profondeur 2. Non embarqué dans la PHAR (`box.json` liste ses dossiers).
- `tests/Feature/AnalyzeTest.php` (11 tests) ; 3 entrées ajoutées au `healthcheck`.
- Suite complète : **209 passed, 1 skipped** ; `composer healthcheck` vert.

## Validation (checkpoint)
Sortie `table` sur le projet validée : 165 classes, 12 composants, 0 cycle, 0 violation SDP. `Core\Graph`, `Core\Parser` et `Core\Report` ont un D élevé (objets valeur concrets et stables : zone of pain attendue).
Décision de l'utilisateur : **chemins des erreurs de parsing relatifs à la racine** → `AnalysisPipeline` rend les `ParseError` relatifs à `basePath` ; test du pipeline resserré (`Parser/Invalid.php` exact), normalisation du golden simplifiée.

## Déviations par rapport au plan
1. **[Règle 1 - Bug] `config/view.php`** commençait par une espace avant `<?php` : toutes les commandes (anciennes comprises) écrivaient une espace parasite sur stdout, ce qui cassait le JSON. Espace supprimée.
2. **Erreurs sur stderr** via `getErrorStyle()` : `$this->error()` de Laravel écrit sur stdout.
3. **`OUTPUT_RAW`** pour l'écriture du rendu (pas d'interprétation des balises `<…>` par le formateur Symfony).
4. **Fixture `tests/Fixtures/Core/Config/project.php`** ajoutée pour `--config` (la fixture `valid/` pointe sur un `src` inexistant).
5. **[Décision checkpoint]** chemins d'erreurs relatifs (voir ci-dessus).

Les tests JSON passent par `Artisan::call()` avec des paramètres en tableau : la forme chaîne fait perdre les antislashs (`StringInput` de Symfony).

## Préparation de la suite
- Phase 4 : ajouter `rules()` à `Config`, le code de sortie 1, la baseline et un `SarifRenderer` (les chemins relatifs sont prêts).
- Les `Node::$file` restent absolus (non affichés pour l'instant).

---
*Phase : 03-cli-configuration*
*Terminé : 2026-11-13*
