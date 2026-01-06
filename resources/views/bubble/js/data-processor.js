// Variables globales
let topLevelData = null;
let currentData = null;
// Ensemble des ids de nœuds supprimés (masqués)
let hiddenNodeIds = new Set();

// Transforme le JSON (structure des dossiers) en nœuds/liaisons de premier niveau, avec filtre par dossier parent
function processFoldersGraph(jsonData, parentFilter = '', metricKey = 'total', depth = 1) {
  // Format attendu (ex. analysis_php-smelly-code-detector...json):
  // {
  //   "path": { classes, interfaces, abstracts, total, relations: ["pathA", "pathB"], metrics: { efferent_coupling, afferent_coupling, instability, loc_total, ccn_total } }
  // }
  // Mais le fichier fourni contient des sections par dossier avec métriques et relations.

  const entries = Object.entries(jsonData || {});
  // Déterminer si c'est un fichier de structure (contient des objets avec metrics/relations)
  const looksLikeStructure = entries.every(([k, v]) => typeof v === 'object');
  if (!looksLikeStructure) return { nodes: [], links: [] };

  // Normalisation du séparateur
  const norm = (p) => (p || '').replace(/\\/g, '/').replace(/\/+$/,'');
  const parent = norm(parentFilter);
  const groupingDepth = Math.max(1, parseInt(depth || 1, 10));

  // 1) Construire les nœuds de premier niveau sous le parent (ou racine s'il est vide)
  const nodeByKey = new Map();
  const childCounters = new Map();

  // Accumuler métriques par dossier top-level relatif au parent
  entries.forEach(([path, info]) => {
    const key = norm(path);
    const inScope = parent ? key.startsWith(parent + '/') || key === parent : true;
    if (!inScope) return;

    // Trouver le dossier de premier niveau relatif au parent
    let rel = parent ? key.slice(parent.length).replace(/^\//,'') : key;
    // Ignorer le dossier parent lui-même (relatif vide)
    if (parent && rel.length === 0) return;
    const segs = rel.split('/').filter(Boolean);
    const picked = segs.slice(0, groupingDepth).join('/');
    const groupKey = parent ? (parent + '/' + (picked || segs[0] || '')) : (picked || segs[0] || rel);

    if (!nodeByKey.has(groupKey)) {
      nodeByKey.set(groupKey, {
        key: groupKey,
        display: parent ? (picked || segs[0] || '') : (picked || segs[0] || ''),
        classes: 0,
        interfaces: 0,
        abstracts: 0,
        efferent_coupling_sum: 0,
        afferent_coupling_sum: 0,
        instability_sum: 0,
        instability_count: 0,
        loc_sum: 0,
        ccn_sum: 0,
      });
    }

    const node = nodeByKey.get(groupKey);
    const classes = Number(info.classes || 0);
    const interfaces = Number(info.interfaces || 0);
    const abstracts = Number(info.abstracts || 0);
    const total = Number(info.total || (classes + interfaces + abstracts));
    const eff = Number(info.metrics?.efferent_coupling || 0);
    const aff = Number(info.metrics?.afferent_coupling || 0);
    const inst = Number(info.metrics?.instability || 0);
    const loc = Number(info.metrics?.loc_total || 0);
    const ccn = Number(info.metrics?.ccn_total || 0);

    node.classes += classes;
    node.interfaces += interfaces;
    node.abstracts += abstracts;
    node.total = (node.total || 0) + total;
    node.efferent_coupling_sum += eff;
    node.afferent_coupling_sum += aff;
    node.instability_sum += inst;
    node.instability_count += 1;
    node.loc_sum += loc;
    node.ccn_sum += ccn;

    // Compter présence
    childCounters.set(groupKey, (childCounters.get(groupKey) || 0) + 1);
  });

  // Finaliser métrique utilisée pour la taille
  let nodes = Array.from(nodeByKey.values()).map(n => {
    const instability_avg = n.instability_count > 0 ? (n.instability_sum / n.instability_count) : 0;
    const metrics = {
      classes: n.classes,
      interfaces: n.interfaces,
      abstracts: n.abstracts,
      total: n.total || (n.classes + n.interfaces + n.abstracts),
      efferent_coupling: n.efferent_coupling_sum,
      afferent_coupling: n.afferent_coupling_sum,
      instability_avg,
      loc_total: n.loc_sum,
      ccn_total: n.ccn_sum,
    };
    const value = metricKey === 'constant' ? 1 : (Number(metrics[metricKey]) || 0);
    return {
      id: n.key,
      name: n.key,
      displayName: n.display,
      value,
      metrics
    };
  }).filter(n => n.value > 0);

  // Appliquer le filtre de suppression (masquage)
  if (hiddenNodeIds && hiddenNodeIds.size > 0) {
    nodes = nodes.filter(n => !hiddenNodeIds.has(n.id));
  }

  // 2) Construire les liens entre ces nœuds top-level, basés sur relations
  const indexByTop = new Map(nodes.map((n, i) => [n.name, i]));
  const nodeByName = new Map(nodes.map(n => [n.name, n]));
  const linksSig = new Set();
  const links = [];

  entries.forEach(([path, info]) => {
    const fromKey = norm(path);
    const inScope = parent ? fromKey.startsWith(parent + '/') || fromKey === parent : true;
    if (!inScope) return;
    const rel = parent ? fromKey.slice(parent.length).replace(/^\//,'') : fromKey;
    if (parent && rel.length === 0) return; // ignorer le parent lui-même
    const fromSegs = rel.split('/').filter(Boolean);
    const fromPicked = fromSegs.slice(0, groupingDepth).join('/');
    const topFrom = parent ? (parent + '/' + (fromPicked || fromSegs[0] || rel)) : (fromPicked || fromSegs[0] || rel);

    const relations = Array.isArray(info.relations) ? info.relations : [];
    relations.forEach(r => {
      const toKey = norm(r);
      // Restreindre aux liens internes au même parent si parent est renseigné
      if (parent) {
        const inScopeTo = toKey.startsWith(parent + '/') || toKey === parent;
        if (!inScopeTo) return;
      }
      const relTo = parent ? toKey.slice(parent.length).replace(/^\//,'') : toKey;
      if (parent && relTo.length === 0) return; // ignorer lien vers parent lui-même
      const toSegs = relTo.split('/').filter(Boolean);
      const toPicked = toSegs.slice(0, groupingDepth).join('/');
      const topTo = parent ? (parent + '/' + (toPicked || toSegs[0] || relTo)) : (toPicked || toSegs[0] || relTo);

      if (topFrom === topTo) return; // ignorer self
      const srcNodeName = topFrom;
      const tgtNodeName = topTo;
      if (!indexByTop.has(srcNodeName) || !indexByTop.has(tgtNodeName)) return;

      const sig = srcNodeName + '->' + tgtNodeName;
      if (!linksSig.has(sig)) {
        linksSig.add(sig);
        links.push({ source: srcNodeName, target: tgtNodeName });
      }
    });
  });

  // 3) Marquer les liens bidirectionnels
  const linkSet = new Set(links.map(l => `${l.source}->${l.target}`));
  links.forEach(l => {
    const reverse = `${l.target}->${l.source}`;
    if (linkSet.has(reverse)) {
      l.bidir = true;
    }
  });

  // 3b) Marquer les dépendances faibles (SDP): si la cible est plus instable que la source
  links.forEach(l => {
    const src = nodeByName.get(l.source);
    const tgt = nodeByName.get(l.target);
    const srcInst = Number(src?.metrics?.instability_avg ?? 0);
    const tgtInst = Number(tgt?.metrics?.instability_avg ?? 0);
    if (!l.bidir && tgtInst > srcInst) {
      l.weak = true;
    }
  });

  // 4) Détecter les cycles sur plusieurs composants (SCC de taille >= 3)
  // Construire l'adjacence
  const nodeNames = nodes.map(n => n.name);
  const adjacency = new Map(nodeNames.map(n => [n, []]));
  links.forEach(l => {
    if (adjacency.has(l.source)) {
      adjacency.get(l.source).push(l.target);
    }
  });

  // Tarjan SCC
  const indexMap = new Map();
  const lowlinkMap = new Map();
  const onStack = new Map();
  const stack = [];
  let index = 0;
  const sccs = [];

  function strongConnect(v) {
    indexMap.set(v, index);
    lowlinkMap.set(v, index);
    index += 1;
    stack.push(v);
    onStack.set(v, true);

    const neighbors = adjacency.get(v) || [];
    for (const w of neighbors) {
      if (!indexMap.has(w)) {
        strongConnect(w);
        lowlinkMap.set(v, Math.min(lowlinkMap.get(v), lowlinkMap.get(w)));
      } else if (onStack.get(w)) {
        lowlinkMap.set(v, Math.min(lowlinkMap.get(v), indexMap.get(w)));
      }
    }

    if (lowlinkMap.get(v) === indexMap.get(v)) {
      const component = [];
      while (true) {
        const w = stack.pop();
        onStack.set(w, false);
        component.push(w);
        if (w === v) break;
      }
      sccs.push(component);
    }
  }

  nodeNames.forEach(n => { if (!indexMap.has(n)) strongConnect(n); });
  const bigSccs = sccs.filter(c => c.length >= 3);
  if (bigSccs.length > 0) {
    // Indexer les SCC
    const sccSets = bigSccs.map(arr => new Set(arr));
    const nodeToSccId = new Map();
    sccSets.forEach((set, idx) => {
      set.forEach(n => nodeToSccId.set(n, idx));
    });

    // Annoter nœuds
    nodes.forEach(n => {
      const id = nodeToSccId.get(n.name);
      if (id !== undefined) {
        n.cycleId = id;
        n.cycleSize = sccSets[id].size;
      }
    });

    // Annoter liens
    links.forEach(l => {
      const idSrc = nodeToSccId.get(l.source);
      const idTgt = nodeToSccId.get(l.target);
      if (idSrc !== undefined && idSrc === idTgt) {
        l.cycle = true;
        l.cycleId = idSrc;
        l.cycleMembers = Array.from(sccSets[idSrc]);
        l.cycleSize = sccSets[idSrc].size;
      }
    });
  }

  return { nodes, links };
}

function getMetricLabelForFolders(metricKey) {
  const map = {
    total: 'Total éléments',
    constant: 'Taille standard',
    classes: 'Classes',
    interfaces: 'Interfaces',
    abstracts: 'Abstraites',
    efferent_coupling: 'Efferent Coupling (Σ)',
    afferent_coupling: 'Afferent Coupling (Σ)',
    instability_avg: 'Instability (moyenne)',
    loc_total: 'Lignes de code (Σ)',
    ccn_total: 'Complexité cyclomatique (Σ)'
  };
  return map[metricKey] || metricKey;
}
