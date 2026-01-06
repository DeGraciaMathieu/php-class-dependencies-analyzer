// Génération
function generateChart() {
  if (!topLevelData) {
    showStatus('analysisStatus', 'error', 'Aucun fichier chargé.');
    return;
  }
  const parentFolder = document.getElementById('parentFolder').value.trim();
  const metricKey = document.getElementById('metricSelect').value;
  const colorKey = document.getElementById('colorSelect')?.value || 'group';
  const selectedDepthRadio = document.querySelector('input[name="depthSelect"]:checked');
  const rawDepth = parseInt(selectedDepthRadio?.value ?? '1', 10);
  const depth = (Number.isFinite(rawDepth) && rawDepth > 0) ? rawDepth : 1;
  const cycleMode = document.getElementById('cycleModeSelect')?.value || 'all';

  // Loading
  document.getElementById('loading').style.display = 'block';
  document.getElementById('emptyState').style.display = 'none';
  document.getElementById('chart').style.display = 'none';
  document.getElementById('chartTitle').style.display = 'none';
  document.getElementById('legend').style.display = 'none';

  setTimeout(() => {
    try {
      const graph = processFoldersGraph(topLevelData, parentFolder, metricKey, depth);
      graph.options = { cycleMode, depth, parentFolder, colorKey };
      if (!graph.nodes.length) {
        showStatus('analysisStatus', 'error', `Aucun dossier trouvé${parentFolder ? ' sous "' + parentFolder + '"' : ''}.`);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
        return;
      }
      currentData = graph;
      // Afficher le SVG avant le dessin pour obtenir les dimensions si nécessaire
      document.getElementById('chart').style.display = 'block';
      createFoldersBubblesGraph(graph, parentFolder, metricKey, colorKey);
      document.getElementById('loading').style.display = 'none';
      document.getElementById('chart').style.display = 'block';
      document.getElementById('chartTitle').style.display = 'block';
      document.getElementById('legend').style.display = 'block';
      document.getElementById('chartControls').style.display = 'block';
      showStatus('analysisStatus', 'success', `Analyse terminée : ${graph.nodes.length} dossiers.`);
    } catch (e) {
      showStatus('analysisStatus', 'error', 'Erreur lors de l\'analyse : ' + e.message);
      document.getElementById('loading').style.display = 'none';
      document.getElementById('emptyState').style.display = 'block';
    }
  }, 350);
}

// Init
document.addEventListener('DOMContentLoaded', function () {
  // Si les données JSON sont déjà fournies par l'outil (ex: window.bubbleData),
  // on les utilise directement et on génère le graphique sans formulaire.
  if (window.bubbleData) {
    topLevelData = window.bubbleData;
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
      emptyState.style.display = 'none';
    }
    generateChart();
  }

  const analyzeBtn = document.getElementById('analyzeBtn');
  if (analyzeBtn) {
    analyzeBtn.addEventListener('click', generateChart);
  }
  const cycleModeSelect = document.getElementById('cycleModeSelect');
  if (cycleModeSelect) {
    cycleModeSelect.addEventListener('change', generateChart);
  }

  // Bouton pour réinitialiser les suppressions de bulles
  document.getElementById('resetFiltersBtn').addEventListener('click', function(){
    if (window.hiddenNodeIds && window.hiddenNodeIds.clear) {
      window.hiddenNodeIds.clear();
    } else {
      hiddenNodeIds = new Set();
      window.hiddenNodeIds = hiddenNodeIds;
    }
    generateChart();
  });

  document.getElementById('resetZoomBtn').addEventListener('click', function(){ if (window.resetZoom) window.resetZoom(); });
  document.getElementById('downloadBtn').addEventListener('click', downloadChartAsPNG);
  document.getElementById('downloadSvgBtn').addEventListener('click', downloadChartAsSVG);

  // Déclencher l'analyse à la touche Entrée dans la section configuration
  const parentInput = document.getElementById('parentFolder');
  const metricSelect = document.getElementById('metricSelect');
  const colorSelect = document.getElementById('colorSelect');
  const depthRadios = document.querySelectorAll('input[name="depthSelect"]');
  const triggerOnEnter = (evt) => {
    if (evt.key === 'Enter') {
      evt.preventDefault();
      generateChart();
    }
  };
  parentInput.addEventListener('keydown', triggerOnEnter);
  metricSelect.addEventListener('keydown', triggerOnEnter);
  // Et recalculer immédiatement quand le select change (UX pratique)
  metricSelect.addEventListener('change', generateChart);
  if (colorSelect) {
    colorSelect.addEventListener('keydown', triggerOnEnter);
    colorSelect.addEventListener('change', generateChart);
  }
  if (depthRadios && depthRadios.length) {
    depthRadios.forEach(r => {
      r.addEventListener('change', generateChart);
      r.addEventListener('keydown', triggerOnEnter);
    });
  }
});

// Expose debug
window.generateChart = generateChart;
window.hiddenNodeIds = hiddenNodeIds;
