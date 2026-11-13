# Phase 1 : Fondations — Résumé du plan 01-02

**Port `ClassParser` et adapter unique `PhpParserClassParser` (php-parser 4.19 + `NameResolver`) produisant un `ParsedClass` par classlike nommé, avec `SourceScanner` natif déterministe ; erreurs de syntaxe restituées dans `ParseResult`, jamais levées.**

## Accomplissements
- Modèle immuable du cœur : `ClassKind` (avec `isAbstract()`), `ParsedClass`, `ParseResult`, `ParseError`, port `ClassParser`.
- `SourceScanner::scan()` : PHP natif (`RecursiveDirectoryIterator`), `*.php` uniquement, chemins absolus (`realpath`) dédoublonnés et triés, globs d'exclusion `fnmatch` relatifs à `$basePath` injecté, fichier isolé accepté, `InvalidArgumentException` sur chemin inexistant.
- Adapter `PhpParserClassParser` + visiteur `DependencyCollector` : un `ParsedClass` par classlike nommé (classes anonymes ignorées, leurs dépendances sont attribuées à la classe englobante), fichier sans classlike → résultat vide, `PhpParser\Error` → `ParseError`.
- Contrôle manuel : `app/Domain/Entities/ClassDependencies.php` donne bien ses 6 value objects (`Coupling`, `Fqcn`, `Dependencies`, `IsInterface`, `IsAbstract`, `Abstractness`).
- Suite complète : **101 passed, 1 skipped** (181 assertions), dont **38 tests Core** (3 architecture, 4 scanner, 31 parser). Test d'architecture vert : seul `App\Core\Parser\PhpParser` importe `PhpParser`.

## Positions de dépendances couvertes (une assertion chacune)
`extends`, `implements` (y compris via alias `use … as …`), `use` de trait, attribut, type de propriété nullable, types de paramètres union et intersection, type de retour nullable, `new`, appel statique, constante de classe, propriété statique, `Foo::class`, `instanceof`, `catch` (simple et union). Les extends d'interface et implements d'enum sont couverts par les tests de kinds. Le type des constantes de classe typées (PHP 8.3) est aussi collecté (sans test dédié).

Exclus (vérifié) : `self`, `static`, `parent`, types natifs et scalaires, fonctions (`strlen`, `Fixture\Support\helper`), constantes globales (`PHP_EOL`), la classe elle-même. Les classes natives PHP (`DateTimeInterface`, `ArrayIterator`, `Traversable`…) sont **conservées**, contrairement à `NativeDecliner` (la séparation interne/externe revient au graphe en 01-03).

## Limites de syntaxe en 4.19
Aucune rencontrée : `tests/Fixtures/Php85/ModernClass.php` (enum adossé, `readonly` promu, attributs, union avec `null`) est parsé intégralement ; le test n'est pas `skip`. Seules des API communes à 4.19 et 5.x sont utilisées (`ParserFactory::createForNewestSupportedVersion()`, `NodeTraverser`, `NameResolver`, `ClassLike::$namespacedName`, `Name::toString()`, sous-nœuds `type`/`class`/`types`/`traits`). Aucune API propre à la v4.

## Fichiers créés/modifiés
- `app/Core/Parser/ClassKind.php`, `ParsedClass.php`, `ParseResult.php`, `ParseError.php`, `ClassParser.php`, `SourceScanner.php` : modèle, port et scanner.
- `app/Core/Parser/PhpParser/PhpParserClassParser.php`, `DependencyCollector.php` : adapter php-parser.
- `tests/Core/Parser/SourceScannerTest.php`, `tests/Core/Parser/PhpParserClassParserTest.php` : tests.
- `tests/Fixtures/Core/Parser/*.php` : classe complète, abstraite, interface, trait, enum, multi-classes, sans classlike, invalide.
- `app/Core/.gitkeep` : supprimé (remplacé par de vrais fichiers).

## Décisions prises
- `ParseError` est une classe dédiée (homonyme du `\ParseError` natif, mais dans `App\Core\Parser`).
- `SourceScanner::scan()` renvoie `list<string>` (`array`) plutôt qu'un `iterable` : le tri impose de matérialiser la liste. Les chemins relatifs sont résolus par rapport à `$basePath`, qui doit exister.
- Un fichier illisible produit aussi un `ParseError` plutôt qu'une exception.
- Les dépendances gardent l'ordre de première apparition (dédoublonnées), pour un résultat déterministe.

## Déviations par rapport au plan

### Corrections automatiques

**1. [Correction du plan] Pas de fallback `method_exists` pour la création du parser**
- **Constaté pendant :** Task 2
- **Problème :** le plan prévoyait `method_exists(ParserFactory::class, 'createForNewestSupportedVersion') ? … : create(PREFER_PHP7)`. Or php-parser 4.19.4 (installé, imposé par jerowork `^4.15`) fournit déjà `createForNewestSupportedVersion()`, qui existe aussi en v5.
- **Correctif :** appel direct `(new ParserFactory)->createForNewestSupportedVersion()`, sans branche ni commentaire de compatibilité.
- **Fichiers modifiés :** `app/Core/Parser/PhpParser/PhpParserClassParser.php`
- **Vérification :** suite Core verte en 4.19.4 ; l'appel est identique en v5.

---

**Total des déviations :** 1 correction du plan, 0 différée.
**Impact sur le plan :** code plus simple, passage à php-parser `^5` (05-03) sans modification attendue de l'adapter.

## Problèmes rencontrés
Aucun.

## Préparation de la suite
- 01-03 peut construire le `DependencyGraph` à partir de `ParseResult` / `ParsedClass` ; `ClassKind::isAbstract()` est prêt pour l'abstraction (A).
- Les classes natives et vendor apparaissent dans les dépendances : le graphe devra les marquer externes.

---
*Phase : 01-fondations*
*Terminé : 2026-11-13*
