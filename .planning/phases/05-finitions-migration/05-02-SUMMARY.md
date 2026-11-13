# Phase 5 : Finitions & migration — Résumé du plan 05-02

**Rendu `--format=html` : page autonome Cytoscape 3.21.1 avec vues composants et classes, cycles et violations SDP en rouge, panneau de détail au clic. Remplace les options `--graph` des anciennes commandes.**

## Accomplissements
- `Report` : `componentDependencies` et `classDependencies` (`array<string, list<string>>`), en fin de constructeur avec `[]` par défaut, restreintes aux entrées retenues (source et cible) par `AnalysisPipeline::retainDependencies()`.
- `HtmlRenderer` + template voisin `app/Core/Rendering/html-report.html` (embarqué par `box.json` via `app`) :
  - données injectées dans `<script type="application/json">` (`JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT`), lues par `JSON.parse`, texte inséré via `textContent` uniquement ;
  - vue composants (taille ∝ √Nc, couleur selon D, gris si D null) et vue classes (nœuds parents par composant) ;
  - cycles (contour et arêtes pleines) et violations SDP (arêtes pointillées) en rouge ; clic → atténuation du reste et panneau (métriques, « Depends on » / « Used by » cliquables) ;
  - légende, thème clair/sombre, message si Cytoscape ne charge pas ou si le rapport est vide ;
  - `$sections` ignoré : la page affiche toujours tout (docblock).
- `AnalyzeCommand` : `--format=html` exige `--output` (exit 2 sinon, contrôlé avant l'analyse) ; message de format inconnu complété.
- Suite complète : **287 passed, 1 skipped** ; `composer healthcheck` vert ; `analyze` sur le projet → exit 0.

## Validation (checkpoint)
Pages `report-project.html` (14 composants, 0 cycle) et `report-shop.html` (2 cycles, 1 violation SDP, 4 violations de règles) ouvertes et validées par l'utilisateur.

## Déviations par rapport au plan
1. **[Règle 1]** `wheelSensitivity` retiré (avertissement Cytoscape en console).
2. **[Règle 2]** `html` ajouté au tableau des options de `docs/ci.md`.
3. Golden régénéré : le diff n'ajoute que les deux blocs d'arêtes.

## Préparation de la suite
- 05-03 peut supprimer les vues Blade et les options `--graph` : le HTML les remplace.

---
*Phase : 05-finitions-migration*
*Terminé : 2026-11-13*
