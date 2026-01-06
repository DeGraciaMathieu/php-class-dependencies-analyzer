// Chargement du fichier JSON
function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  if (!file.name.toLowerCase().endsWith('.json')) {
    showStatus('fileStatus', 'error', 'Veuillez sélectionner un fichier JSON valide.');
    return;
  }

  showStatus('fileStatus', 'info', 'Chargement du fichier en cours...');
  const reader = new FileReader();
  reader.onload = function (e) {
    try {
      const data = JSON.parse(e.target.result);
      topLevelData = data;
      const keys = Object.keys(data);
      document.getElementById('fileInfo').innerHTML = `<strong>Fichier chargé :</strong> ${file.name}<br><strong>Entrées :</strong> ${keys.length}`;
      document.getElementById('fileInfo').style.display = 'block';
      showStatus('fileStatus', 'success', 'Fichier chargé avec succès !');
      // Lancer automatiquement l'analyse
      if (typeof generateChart === 'function') {
        generateChart();
      }
    } catch (err) {
      showStatus('fileStatus', 'error', 'Erreur lors de la lecture du fichier JSON : ' + err.message);
    }
  };
  reader.onerror = function (error) {
    showStatus('fileStatus', 'error', 'Erreur lors de la lecture du fichier : ' + error.message);
  };
  reader.readAsText(file);
}

// Ancienne fonction de chargement de données d'exemple supprimée

function showStatus(elementId, type, message) {
  const statusEl = document.getElementById(elementId);
  statusEl.className = `status ${type}`;
  statusEl.textContent = message;
  statusEl.style.display = 'block';
  if (type === 'success' || type === 'info') {
    setTimeout(() => { statusEl.style.display = 'none'; }, 3000);
  }
}
