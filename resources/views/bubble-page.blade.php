<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartographie des dépendances par dossier</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
    <style>
        .container { max-width: 1800px; margin: 0 auto; padding: 16px; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        #chart { height: 800px; min-height: 800px; }
        .legend { max-height: 180px; overflow: auto; }
        .chart-title { margin-bottom: 8px; font-weight: 600; }
        .chart-btn { font-size: 13px; }
        .dep-link { pointer-events: none; }
        .bubble.highlight circle { stroke: #111827; stroke-width: 3; opacity: 1; }
        .bubble.adjacent circle { stroke: #2563eb; stroke-width: 2; opacity: 0.95; }
        .bubble.dimmed { opacity: 0.25; }
        .dep-link.highlight { stroke-width: 3 !important; opacity: 1 !important; }
        .dep-link.dimmed { opacity: 0.15 !important; }
        .header h1 { margin: 0 0 4px; font-size: 24px; }
        .header p { margin: 0; color: #4b5563; font-size: 14px; }
        .main-content { display: flex; gap: 16px; margin-top: 16px; }
        .controls { width: 320px; flex-shrink: 0; }
        .chart-container { flex: 1; min-width: 0; }
        .form-section { background: #f9fafb; border-radius: 8px; padding: 12px 14px; border: 1px solid #e5e7eb; }
        .form-section h3 { margin: 0 0 8px; font-size: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group { display: flex; flex-direction: column; gap: 4px; font-size: 13px; }
        .input-group label { font-weight: 500; color: #374151; }
        .input-group input[type="text"], .input-group select { padding: 6px 8px; border-radius: 4px; border: 1px solid #d1d5db; font-size: 13px; }
        .btn { margin-top: 8px; padding: 6px 10px; font-size: 13px; border-radius: 4px; border: none; background: #2563eb; color: white; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .status { margin-top: 8px; font-size: 12px; display: none; }
        .status.success { color: #15803d; }
        .status.error { color: #b91c1c; }
        .status.info { color: #1d4ed8; }
        .chart-controls { display: none; align-items: center; gap: 6px; margin-bottom: 6px; font-size: 13px; }
        .loading { display: none; align-items: center; gap: 8px; font-size: 13px; color: #4b5563; }
        .spinner { width: 16px; height: 16px; border-radius: 999px; border: 2px solid #e5e7eb; border-top-color: #2563eb; animation: spin 0.6s linear infinite; }
        .empty-state { text-align: center; color: #6b7280; font-size: 14px; padding: 32px 16px; }
        .empty-state svg { width: 40px; height: 40px; margin-bottom: 8px; color: #9ca3af; }
        .tooltip { position: absolute; pointer-events: none; background: rgba(17, 24, 39, 0.9); color: white; padding: 6px 8px; border-radius: 4px; font-size: 11px; max-width: 260px; z-index: 20; }
        .legend { margin-top: 8px; display: flex; flex-wrap: wrap; gap: 4px 10px; font-size: 11px; color: #374151; }
        .legend-item { display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
        .legend-color { width: 10px; height: 10px; border-radius: 999px; border: 1px solid rgba(0,0,0,0.15); }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Cartographie des dépendances par dossier</h1>
            <p>Vue en bulles des relations entre dossiers de premier niveau</p>
        </div>

        <div class="main-content">
            <div class="controls">
                <div class="form-section">
                    <h3>Configuration</h3>
                    <div class="form-group">
                        <div class="input-group">
                            <label for="parentFolder">Dossier parent (optionnel) :</label>
                            <input type="text" id="parentFolder" placeholder="Ex: app/Application">
                        </div>
                        <div class="input-group">
                            <label for="metricSelect">Taille des bulles basée sur :</label>
                            <select id="metricSelect">
                                <option value="total">Total (classes+interfaces+abstracts)</option>
                                <option value="constant">Taille standard</option>
                                <option value="classes">Nombre de classes</option>
                                <option value="interfaces">Nombre d'interfaces</option>
                                <option value="abstracts">Nombre d'abstraites</option>
                                <option value="efferent_coupling">Efferent Coupling (Σ)</option>
                                <option value="afferent_coupling">Afferent Coupling (Σ)</option>
                                <option value="instability_avg">Instabilité (moyenne)</option>
                                <option value="loc_total">Lignes de code (Σ)</option>
                                <option value="ccn_total">Complexité cyclomatique (Σ)</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="colorSelect">Couleur des bulles basée sur :</label>
                            <select id="colorSelect">
                                <option value="group" selected>Par dossier (palette)</option>
                                <option value="instability_avg">Instabilité (moyenne)</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Profondeur des dossiers :</label>
                            <div id="depthRadios" style="display: inline-flex; gap: 10px; align-items: center;">
                                <label style="display: inline-flex; align-items: center; gap: 4px;">
                                    <input type="radio" name="depthSelect" value="1" checked>
                                    1
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px;">
                                    <input type="radio" name="depthSelect" value="2">
                                    2
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px;">
                                    <input type="radio" name="depthSelect" value="3">
                                    3
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px;">
                                    <input type="radio" name="depthSelect" value="4">
                                    4
                                </label>
                            </div>
                        </div>
                        <button class="btn" id="analyzeBtn">Générer</button>
                    </div>
                    <div class="status" id="analysisStatus"></div>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-controls" id="chartControls" style="display: none;">
                    <button class="chart-btn" id="resetZoomBtn" title="Réinitialiser le zoom">🔍 Reset</button>
                    <button class="chart-btn" id="resetFiltersBtn" title="Réafficher toutes les bulles supprimées">🧹 Filtres</button>
                    <button class="chart-btn" id="downloadBtn" title="Télécharger en PNG">📥 PNG</button>
                    <button class="chart-btn" id="downloadSvgBtn" title="Télécharger en SVG">📥 SVG</button>
                    <label style="margin-left: 12px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                        Type dependance:
                        <select id="cycleModeSelect" style="padding: 4px 8px; font-size: 13px;">
                            <option value="all" selected>Toutes</option>
                            <option value="weak">Dépendances faibles uniquement</option>
                            <option value="direct">Cycles directs (A↔B)</option>
                            <option value="multi">Cycles multi (A→B→C→A)</option>
                        </select>
                    </label>
                </div>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Analyse en cours...</p>
                </div>

                <div class="empty-state" id="emptyState">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                    </svg>
                    <h3>Données d'analyse fournies</h3>
                    <p>Les données JSON d'analyse (dossiers → relations) sont fournies automatiquement par l'outil de ligne de commande.</p>
                </div>

                <div class="chart-title" id="chartTitle" style="display: none;"></div>
                <svg id="chart" style="display: none;"></svg>
                <div class="legend" id="legend" style="display: none;"></div>
            </div>
        </div>
    </div>

    <div class="tooltip" id="tooltip" style="opacity: 0;"></div>

    <script>
        // Données injectées par le CLI
        window.bubbleData = @json($dependencies);
    </script>
    <script>
{!! file_get_contents(base_path('resources/views/bubble/js/data-processor.js')) !!}
    </script>
    <script>
{!! file_get_contents(base_path('resources/views/bubble/js/chart-generator.js')) !!}
    </script>
    <script>
{!! file_get_contents(base_path('resources/views/bubble/js/download-handler.js')) !!}
    </script>
    <script>
{!! file_get_contents(base_path('resources/views/bubble/js/main.js')) !!}
    </script>
</body>

</html>


