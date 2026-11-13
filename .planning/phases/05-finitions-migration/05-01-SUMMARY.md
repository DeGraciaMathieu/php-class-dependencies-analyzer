# Phase 5 : Finitions & migration — Résumé du plan 05-01

**Cache de parsing indexé par empreinte `sha1` du contenu (`CachedClassParser` décorant le port `ClassParser`, `ParseCache` sérialisé dans `.dependencies-cache`), activé par défaut, avec `--no-cache`, `--clear-cache` et `Config::cache()` : un second run sans modification ne reparse aucun fichier.**

## Accomplissements
- `ParseCache(string $file, string $signature)` (`App\Core\Cache`) : `load()`, `get($path, $hash): ?ParseResult`, `put($path, $hash, $result)`, `save()`, `clear()`.
  - Format du fichier : `serialize(['format' => 1, 'signature' => string, 'entries' => [chemin absolu => ['hash' => sha1, 'result' => ParseResult]]])`.
  - Format ou signature différents, fichier absent, illisible ou corrompu → cache vide, sans warning ni exception (le cache est réécrit au `save()`).
  - `unserialize` limité à `ParseResult`, `ParsedClass`, `ParseError`, `ClassKind` ; une entrée qui n'est pas un `ParseResult` est ignorée.
  - `save()` n'écrit que les entrées lues ou écrites pendant le run (les fichiers disparus sont purgés), via un fichier temporaire puis `rename`.
- `CachedClassParser(ClassParser $inner, ParseCache $cache)` : clé = `realpath`, validité = `sha1` du contenu ; un fichier illisible est délégué sans cache. Compteurs `hits()` / `misses()`.
- `Config::cache(bool|string)` : `true` par défaut (`ParseCache::FILENAME` = `.dependencies-cache`), `false`, ou un chemin relatif à la racine.
- `AnalyzeCommand` : reçoit le `ClassParser` injecté et construit le pipeline autour du décorateur quand le cache est actif ; `save()` juste après l'analyse. Signature = `config('app.version')|version de nikic/php-parser` (`Composer\InstalledVersions`, hors Core) ; la version du format est portée par `ParseCache::FORMAT`.
  - `--no-cache` : ni lecture ni écriture ; `--clear-cache` : supprime le fichier avant l'analyse (combinable avec `--no-cache`).
- `.gitignore` : `.dependencies-cache` ; `docs/ci.md` : section « Parse cache », options, `->cache()` dans l'exemple de config.
- Dogfooding : composant `Core\Cache` ajouté à `dependencies.php` (I = 0.83, dépend de `Core\Parser`) ; `analyze` → exit 0, aucune violation.

## Vérification
- Second run sur `app/` (178 fichiers) avec le cache réel : `hits=178 misses=0` (script de contrôle rejouant la commande).
- Temps (`php class-dependencies-analyzer analyze app`) : ~1,0–1,2 s sans cache ou au premier run, ~0,4 s au second (dont l'amorçage de Laravel Zero).
- Test Feature : un parser espion lié au conteneur compte 0 appel au second run, sortie JSON identique ; `--no-cache` n'écrit rien ; `--clear-cache` supprime le fichier ; `Config::cache('custom-cache')` écrit au chemin configuré.
- Suite complète : **280 passed, 1 skipped** (+24 tests) ; `pint --test` OK sur les fichiers touchés ; `composer healthcheck` vert ; architecture verte.

## Fichiers créés/modifiés
- `app/Core/Cache/ParseCache.php`, `app/Core/Cache/CachedClassParser.php`
- `tests/Core/Cache/ParseCacheTest.php`, `tests/Core/Cache/CachedClassParserTest.php`
- `app/Core/Config/Config.php`, `tests/Core/Config/ConfigTest.php`
- `app/Commands/AnalyzeCommand.php`, `tests/Feature/AnalyzeTest.php` (`--no-cache` sur les tests existants + bloc « parse cache » en dossier temporaire)
- `dependencies.php`, `.gitignore`, `docs/ci.md`

## Décisions
- Lecture du cache sous un gestionnaire d'erreurs muet (`set_error_handler`) plutôt que `@` : PHPUnit signalait les notices d'`unserialize` malgré `@`.
- Un échec d'écriture du cache est ignoré (fichier temporaire supprimé) : le cache reste une optimisation.

## Déviations par rapport au plan
Aucune. `tests/Core/ArchitectureTest.php` n'a pas eu besoin d'être modifié.

## Limites connues
- `composer healthcheck` (commandes `analyze app`) laisse un `.dependencies-cache` à la racine, ignoré par Git. Les tests n'en écrivent pas.
- En dev, `app.version` vient de `git describe` : le cache est invalidé à chaque commit (stable dans la PHAR).

---
*Phase : 05-finitions-migration*
*Terminé : 2026-11-13*
