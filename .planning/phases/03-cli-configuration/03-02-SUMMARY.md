# Phase 3 : CLI & configuration — Résumé du plan 03-02

**`TableRenderer` (tableaux ASCII alignés, 2 décimales, `—` pour une métrique non mesurable) et `JsonRenderer` (schema 1, camelCase, 4 décimales, `null` conservé), en PHP pur dans `App\Core\Rendering`, pilotés par l'enum `ReportSection`.**

## Accomplissements
- `ReportSection` (enum string `classes` / `components` / `cycles` / `sdp`) : `defaults()` → `components`, `cycles`, `sdp` ; `fromList(list<string>)` lève `InvalidArgumentException` en listant les valeurs valides ; `ordered(list<self>)` remet les sections demandées dans l'ordre de l'enum (sans doublon), partagé par les deux renderers.
- `Renderer` : `render(Report $report, list<ReportSection> $sections): string`. L'écriture sur la sortie reste à la commande (03-03).
- `TextTable` (`@internal`) : en-têtes + lignes → colonnes alignées à gauche (largeur via `mb_strlen`), séparateur de deux espaces, ligne de tirets, espaces de fin supprimés. Pas de couleurs, pas de bordures.
- `TableRenderer` : ligne de résumé `Files: N, classes: N, errors: N`, puis un bloc titré par section ; section vide → `No class.` / `No component.` / `No cycle.` / `No SDP violation.` ; erreurs de parsing listées en fin (`Errors` puis `- fichier: message`) si présentes.
- `JsonRenderer` : structure documentée dans le docblock ; `schema`, `summary` et `errors` toujours présents, les autres clés seulement si demandées ; `JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR`. Constante publique `JsonRenderer::SCHEMA = 1`.
- `kind` en JSON : forme kebab-case stable `class`, `abstract-class`, `interface`, `trait`, `enum`, calculée par un `match` explicite dans `JsonRenderer` (jamais dérivée du nom des cases de `ClassKind`, pour que le schéma ne bouge pas si l'enum est renommé).
- Aucun arrondi hors de `App\Core\Rendering` (vérifié par grep) ; aucune dépendance framework (test d'architecture vert).
- Suite complète : **198 passed, 1 skipped** (442 assertions), +15 tests ; Pint OK sur les nouveaux fichiers.

Sortie table sur le projet fixture `Shop\` (toutes les sections) :

```
Files: 12, classes: 11, errors: 1

Classes
Class                           Ca  Ce  Ext  I
------------------------------  --  --  ---  ----
Shop\Billing\HasTaxes           1   0   0    0.00
Shop\Billing\Invoice            3   2   1    0.40
Shop\Billing\PaymentGateway     2   1   0    0.33
Shop\Billing\StripeGateway      0   2   1    1.00
Shop\Catalog\Category           1   1   0    0.50
Shop\Catalog\Product            3   1   2    0.25
Shop\Catalog\ProductRepository  1   1   0    0.50
Shop\Ordering\Order             3   2   1    0.40
Shop\Ordering\OrderRepository   1   1   0    0.50
Shop\Ordering\OrderStatus       1   0   0    0.00
Shop\Ordering\PlaceOrder        0   5   1    1.00

Components
Component      Nc  Na  A     Ca  Ce  I     D
-------------  --  --  ----  --  --  ----  ----
Shop\Billing   3   1   0.33  1   1   0.50  0.17
Shop\Catalog   3   1   0.33  2   0   0.00  0.67
Shop\Ordering  4   1   0.25  1   4   0.80  0.05

Cycles
Level      Size  Members
---------  ----  --------------------------------------------
class      2     Shop\Catalog\Category → Shop\Catalog\Product
component  2     Shop\Billing → Shop\Ordering

SDP violations
Dependency                                  Δ
------------------------------------------  ----
Shop\Billing (0.50) → Shop\Ordering (0.80)  0.30

Errors
- /…/tests/Fixtures/Core/Parser/Invalid.php: Syntax error, unexpected '}', expecting T_VARIABLE on line 8
```

## Fichiers créés/modifiés
- `app/Core/Rendering/ReportSection.php`, `Renderer.php`, `TextTable.php`, `TableRenderer.php`, `JsonRenderer.php`
- `tests/Core/Rendering/TableRendererTest.php` (8 tests), `JsonRendererTest.php` (7 tests)
- `.planning/ROADMAP.md` : 03-02 coché, Phase 3 → 2/3

## Décisions prises
- Colonne `Class` / `Component` / `Dependency` ajoutée en tête des tableaux (le plan ne listait que les colonnes de métriques).
- Toutes les colonnes alignées à gauche : un seul mode d'alignement garde `TextTable` minimal.
- Les chemins d'erreurs sont rendus tels que fournis par le `Report` (absolus) ; les rendre relatifs relève de la commande ou d'un plan ultérieur.
- Le cas `abstract-class` est testé sur un `Report` construit à la main : le projet fixture ne contient pas de classe abstraite.

## Déviations par rapport au plan
- Ajout de `ReportSection::ordered()` (non listé au plan) pour factoriser « dans l'ordre de l'enum » entre les deux renderers. Aucune autre déviation.

## Préparation de la suite
- 03-03 : la commande convertit `--report` via `ReportSection::fromList()` (défaut `ReportSection::defaults()`), choisit `TableRenderer` ou `JsonRenderer` selon `--format` et écrit la chaîne retournée sur la sortie ou dans `--output`.

---
*Phase : 03-cli-configuration*
*Terminé : 2026-11-13*
