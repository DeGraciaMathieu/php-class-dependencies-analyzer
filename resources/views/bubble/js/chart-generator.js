// Couleurs cohérentes: les enfants héritent la couleur de leur dossier racine
function normalizePath(p) {
  return (p || '').replace(/\\/g, '/').replace(/\/+$/, '');
}

function hashStringToIndex(str, mod) {
  const s = String(str || '');
  let h = 5381;
  for (let i = 0; i < s.length; i++) {
    h = ((h << 5) + h) + s.charCodeAt(i);
  }
  return Math.abs(h) % Math.max(1, mod || 1);
}

function getRootGroupKey(name, parentFolder) {
  const full = normalizePath(name);
  const parent = normalizePath(parentFolder);
  let rest = full;
  if (parent) {
    if (full === parent) return parent; // parent lui-même
    if (full.startsWith(parent + '/')) {
      rest = full.slice(parent.length).replace(/^\//, '');
    }
  }
  const firstSeg = (rest.split('/')[0] || rest);
  return parent ? (parent + '/' + firstSeg) : firstSeg;
}

function getColorForNode(d, parentFolder) {
  const palette = d3.schemeCategory10;
  const key = getRootGroupKey(d?.name || d?.id || '', parentFolder);
  const idx = hashStringToIndex(key, palette.length);
  return palette[idx];
}

// Crée le graphe en bulles avec liens (dossiers top-level)
function createFoldersBubblesGraph(graph, parentFolder, metricKey, colorKey) {
  const { nodes, links } = graph;
  const cycleMode = graph.options?.cycleMode || 'all';
  const depth = graph.options?.depth || 1;
  const isDirectMode = cycleMode === 'direct';
  const isMultiMode = cycleMode === 'multi';
  const resolvedColorKey = colorKey || graph.options?.colorKey || 'group';

  const svg = d3.select('#chart');
  svg.selectAll('*').remove();

  // Utiliser la largeur réelle du conteneur si possible (fallback: fenêtre)
  const containerEl = document.querySelector('.chart-container');
  const measuredWidth = (containerEl && containerEl.clientWidth ? containerEl.clientWidth : window.innerWidth) - 40;
  const containerWidth = Math.max(1400, Math.min(2400, measuredWidth));
  const containerHeight = 800;
  svg.attr('width', containerWidth).attr('height', containerHeight);

  const margin = { top: 20, right: 20, bottom: 20, left: 20 };
  const width = containerWidth - margin.left - margin.right;
  const height = containerHeight - margin.top - margin.bottom;

  const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

  // Tooltip différé
  let tooltipTimer = null;
  let lastMouseEvent = null;
  const tooltipDelayMs = 400;

  // Calque de fond pour capter les clics (reset surbrillance)
  const bgRect = g.append('rect')
    .attr('class', 'bg-capture')
    .attr('x', 0)
    .attr('y', 0)
    .attr('width', width)
    .attr('height', height)
    .attr('fill', 'transparent')
    .style('pointer-events', 'all')
    .style('cursor', 'default');

  // Définir les marqueurs de flèche
  const defs = svg.append('defs');
  defs.append('marker')
    .attr('id', 'arrowhead')
    .attr('viewBox', '0 -5 10 10')
    .attr('refX', 10)
    .attr('refY', 0)
    .attr('markerWidth', 5)
    .attr('markerHeight', 5)
    .attr('orient', 'auto')
    .append('path')
    .attr('d', 'M0,-5L10,0L0,5')
    .attr('fill', '#9aa0a6')
    .attr('opacity', 0.8);
  // Marqueur rouge pour les liens bidirectionnels
  defs.append('marker')
    .attr('id', 'arrowhead-red')
    .attr('viewBox', '0 -5 10 10')
    .attr('refX', 10)
    .attr('refY', 0)
    .attr('markerWidth', 5)
    .attr('markerHeight', 5)
    .attr('orient', 'auto')
    .append('path')
    .attr('d', 'M0,-5L10,0L0,5')
    .attr('fill', '#ef4444')
    .attr('opacity', 0.9);

  // Marqueur orange pour dépendances faibles (SDP)
  defs.append('marker')
    .attr('id', 'arrowhead-weak')
    .attr('viewBox', '0 -5 10 10')
    .attr('refX', 10)
    .attr('refY', 0)
    .attr('markerWidth', 5)
    .attr('markerHeight', 5)
    .attr('orient', 'auto')
    .append('path')
    .attr('d', 'M0,-5L10,0L0,5')
    .attr('fill', '#f59e0b')
    .attr('opacity', 0.9);

  // Marqueur violet pour liens appartenant à un cycle multi-composants
  defs.append('marker')
    .attr('id', 'arrowhead-cycle')
    .attr('viewBox', '0 -5 10 10')
    .attr('refX', 10)
    .attr('refY', 0)
    .attr('markerWidth', 5)
    .attr('markerHeight', 5)
    .attr('orient', 'auto')
    .append('path')
    .attr('d', 'M0,-5L10,0L0,5')
    .attr('fill', '#ef4444')
    .attr('opacity', 0.95);

  // Zoom (désactiver le double-clic pour l'utiliser pour "entrer" dans une bulle)
  const zoom = d3.zoom()
    .filter(event => event.type !== 'dblclick')
    .scaleExtent([0.2, 5])
    // Limiter l'étendue de translation pour éviter de perdre le graphe en dehors du viewport
    .extent([[0, 0], [containerWidth, containerHeight]])
    .translateExtent([[-width, -height], [2 * width, 2 * height]])
    .on('zoom', function (event) {
      g.attr('transform', `translate(${margin.left + event.transform.x},${margin.top + event.transform.y}) scale(${event.transform.k})`);
    });
  svg.call(zoom);
  // Toujours repartir d'une transformation identité lors d'une régénération pour éviter les sauts/disparitions après filtrage
  svg.call(zoom.transform, d3.zoomIdentity);
  window.resetZoom = function () { svg.transition().duration(600).call(zoom.transform, d3.zoomIdentity); };

  const maxValue = d3.max(nodes, d => d.value) || 1;
  const isConstantSize = metricKey === 'constant';
  const baseMaxRadius = Math.min(width, height) / 5; // bulles plus grandes
  const constantRadius = 42; // rayon fixe plus grand
  const radiusScale = isConstantSize
    ? (() => {
        const r = constantRadius;
        const f = (v) => r;
        f.range = () => [r, r];
        return f;
      })()
    : d3.scaleSqrt().domain([0, maxValue]).range([24, baseMaxRadius]);
  // Couleur dérivée via getColorForNode ou via instabilité si colorKey = 'instability_avg'
  const minInstability = d3.min(nodes, d => Number(d.metrics?.instability_avg ?? 0)) ?? 0;
  const maxInstability = d3.max(nodes, d => Number(d.metrics?.instability_avg ?? 0)) ?? 1;
  const instabilityColorScale = d3.scaleLinear()
    .domain([minInstability, (minInstability + maxInstability) / 2, maxInstability])
    .range(['#16a34a', '#f59e0b', '#ef4444']) // vert -> orange -> rouge
    .clamp(true);

  // Force layout
  const simNodes = nodes.map(d => ({ ...d }));
  const nodeById = new Map(simNodes.map(n => [n.id, n]));
  const simLinks = links
    .map(l => ({ ...l, source: nodeById.get(l.source), target: nodeById.get(l.target) }))
    .filter(l => l.source && l.target);

  const simulation = d3.forceSimulation(simNodes)
    .force('charge', d3.forceManyBody().strength(-45))
    .force('center', d3.forceCenter(width / 2, height / 2))
    .force('collision', d3.forceCollide().radius(d => radiusScale(d.value) + 18).iterations(2))
    .force('link', d3.forceLink(simLinks)
      .id(d => d.id)
      .distance(d => radiusScale(d.source.value) + radiusScale(d.target.value) + 120)
      .strength(0.12))
    .on('tick', ticked);

  const linksLayer = g.append('g').attr('class', 'links-layer');
  // Filtrer les liens selon le mode choisi
  const filteredLinks = simLinks.filter(l => {
    if (cycleMode === 'multi') return !!l.cycle;
    if (cycleMode === 'direct') return !!l.bidir;
    if (cycleMode === 'weak') return !!l.weak;
    return true; // 'all'
  });
  const linkSelection = linksLayer.selectAll('line.dep-link')
    .data(filteredLinks)
    .enter()
    .append('line')
    .attr('class', 'dep-link')
    .attr('stroke', d => {
      if (isMultiMode && d.cycle) return '#ef4444';
      if (isDirectMode && d.bidir) return '#ef4444';
      return d.weak ? '#f59e0b' : '#9aa0a6';
    })
    .attr('stroke-width', d => {
      if (isMultiMode && d.cycle) return 2.5;
      if (isDirectMode && d.bidir) return 2.5;
      return d.bidir ? 2 : 1.5;
    })
    .attr('opacity', 0.6)
    .attr('stroke-dasharray', d => d.weak && !d.bidir ? '6,4' : 'none')
    .attr('marker-end', d => {
      if (isMultiMode && d.cycle) return 'url(#arrowhead-red)';
      if (isDirectMode && d.bidir) return 'url(#arrowhead-red)';
      return d.weak ? 'url(#arrowhead-weak)' : 'url(#arrowhead)';
    })
    .style('pointer-events', d => (isMultiMode && d.cycle) || (isDirectMode && d.bidir) ? 'stroke' : 'none')
    .style('cursor', d => (isMultiMode && d.cycle) || (isDirectMode && d.bidir) ? 'pointer' : 'default');

  const bubbles = g.selectAll('.bubble')
    .data(simNodes)
    .enter()
    .append('g')
    .attr('class', 'bubble');

  // Gestion de surbrillance sélection/dépendances
  let selectedNodeId = null;
  function clearHighlight() {
    selectedNodeId = null;
    bubbles.classed('highlight', false).classed('adjacent', false).classed('dimmed', false);
    linkSelection.classed('highlight', false).classed('dimmed', false);
  }
  function highlightNodeAndDeps(nodeDatum) {
    if (!nodeDatum) { clearHighlight(); return; }
    selectedNodeId = nodeDatum.id;
    const neighborIds = new Set([selectedNodeId]);
    linkSelection.each(l => {
      const isConnected = l.source?.id === selectedNodeId || l.target?.id === selectedNodeId;
      if (isConnected) {
        neighborIds.add(l.source?.id);
        neighborIds.add(l.target?.id);
      }
    });
    bubbles
      .classed('highlight', d => d.id === selectedNodeId)
      .classed('adjacent', d => d.id !== selectedNodeId && neighborIds.has(d.id))
      .classed('dimmed', d => !neighborIds.has(d.id));
    linkSelection
      .classed('highlight', l => l.source?.id === selectedNodeId || l.target?.id === selectedNodeId)
      .classed('dimmed', l => !(l.source?.id === selectedNodeId || l.target?.id === selectedNodeId));
  }
  // Clic de fond pour désélectionner
  svg.on('click', () => clearHighlight());
  bgRect.on('click', (event) => { event.stopPropagation(); clearHighlight(); });

  // Clic sur lien de cycle: afficher tout le cycle
  if (isMultiMode || isDirectMode) {
    function highlightCycleFromLink(linkDatum) {
      // Mode direct: surligner la paire bidirectionnelle uniquement
      if (isDirectMode) {
        if (!linkDatum.bidir) return;
        const src = linkDatum.source?.id || linkDatum.source;
        const tgt = linkDatum.target?.id || linkDatum.target;
        const allowed = new Set([src, tgt]);
        bubbles
          .classed('highlight', d => allowed.has(d.id))
          .classed('adjacent', false)
          .classed('dimmed', d => !allowed.has(d.id));
        linkSelection
          .classed('highlight', e => (e.bidir && ((e.source?.id === src && e.target?.id === tgt) || (e.source?.id === tgt && e.target?.id === src))))
          .classed('dimmed', e => !(e.bidir && ((e.source?.id === src && e.target?.id === tgt) || (e.source?.id === tgt && e.target?.id === src))));
        return;
      }

      // Mode multi: calcul précédent basé sur SCC/members
      const memberList = Array.isArray(linkDatum.cycleMembers) ? linkDatum.cycleMembers : [];
      const allowed = new Set(memberList);
      if (!linkDatum.cycle || allowed.size === 0) {
        const cid = linkDatum.cycleId;
        bubbles
          .classed('highlight', d => d.cycleId === cid)
          .classed('adjacent', false)
          .classed('dimmed', d => d.cycleId !== cid);
        linkSelection
          .classed('highlight', e => e.cycle && e.cycleId === cid)
          .classed('dimmed', e => !(e.cycle && e.cycleId === cid));
        return;
      }

      // Construire adjacence dans la SCC
      const outgoing = new Map();
      const incoming = new Map();
      const addEdge = (u, v) => {
        if (!allowed.has(u) || !allowed.has(v)) return;
        if (!outgoing.has(u)) outgoing.set(u, []);
        if (!incoming.has(v)) incoming.set(v, []);
        outgoing.get(u).push(v);
        incoming.get(v).push(u);
      };
      linkSelection.each(e => addEdge(e.source?.id, e.target?.id));

      // BFS depuis target vers source pour obtenir un plus court cycle passant par le lien cliqué
      const start = linkDatum.target?.id;
      const goal = linkDatum.source?.id;
      const queue = [];
      const visited = new Set();
      const prev = new Map();
      if (start && goal) {
        queue.push(start);
        visited.add(start);
        while (queue.length > 0) {
          const u = queue.shift();
          if (u === goal) break;
          const nbrs = outgoing.get(u) || [];
          for (const v of nbrs) {
            if (!visited.has(v)) {
              visited.add(v);
              prev.set(v, u);
              queue.push(v);
            }
          }
        }
      }

      const nodesInPath = new Set();
      const edgesInPath = new Set();
      function sig(u, v) { return `${u}->${v}`; }
      let found = false;
      if (prev.has(goal) || start === goal) {
        // Reconstituer le chemin goal <- ... <- start
        found = true;
        let cur = goal;
        nodesInPath.add(cur);
        while (cur !== start) {
          const p = prev.get(cur);
          if (!p) { found = false; break; }
          nodesInPath.add(p);
          edgesInPath.add(sig(p, cur));
          cur = p;
        }
        // Ajouter le lien cliqué pour fermer le cycle
        edgesInPath.add(sig(linkDatum.source?.id, linkDatum.target?.id));
        nodesInPath.add(linkDatum.source?.id);
        nodesInPath.add(linkDatum.target?.id);
      }

      if (!found) {
        // Fallback: surligner la SCC entière
        const cid = linkDatum.cycleId;
        bubbles
          .classed('highlight', d => d.cycleId === cid)
          .classed('adjacent', false)
          .classed('dimmed', d => d.cycleId !== cid);
        linkSelection
          .classed('highlight', e => e.cycle && e.cycleId === cid)
          .classed('dimmed', e => !(e.cycle && e.cycleId === cid));
        return;
      }

      // Appliquer la surbrillance sur le cycle minimal trouvé
      bubbles
        .classed('highlight', d => nodesInPath.has(d.id))
        .classed('adjacent', false)
        .classed('dimmed', d => !nodesInPath.has(d.id));
      linkSelection
        .classed('highlight', e => edgesInPath.has(sig(e.source?.id, e.target?.id)))
        .classed('dimmed', e => !edgesInPath.has(sig(e.source?.id, e.target?.id)));
    }

    linksLayer.selectAll('line.dep-link')
      .on('click', (event, l) => {
        const eligible = (isMultiMode && l.cycle) || (isDirectMode && l.bidir);
        if (!eligible) return;
        event.stopPropagation();
        highlightCycleFromLink(l);
      })
      .style('pointer-events', d => (isMultiMode && d.cycle) || (isDirectMode && d.bidir) ? 'stroke' : 'none')
      .style('cursor', d => (isMultiMode && d.cycle) || (isDirectMode && d.bidir) ? 'pointer' : 'default');
  }

  // Drag & drop sur les bulles
  const drag = d3.drag()
    .on('start', function (event, d) {
      if (!event.active) simulation.alphaTarget(0.3).restart();
      d.fx = d.x;
      d.fy = d.y;
    })
    .on('drag', function (event, d) {
      d.fx = event.x;
      d.fy = event.y;
    })
    .on('end', function (event, d) {
      if (!event.active) simulation.alphaTarget(0);
      // Conserver la position manuellement fixée
      d.fx = event.x;
      d.fy = event.y;
    });
  bubbles.call(drag);

  bubbles.append('circle')
    .attr('r', d => radiusScale(d.value))
    .attr('fill', (d) => resolvedColorKey === 'instability_avg' ? instabilityColorScale(Number(d.metrics?.instability_avg ?? 0)) : getColorForNode(d, parentFolder))
    .attr('opacity', 0.85)
    .attr('stroke', 'white')
    .attr('stroke-width', 2)
    .style('cursor', 'pointer')
    .on('mouseover', (event, d) => {
      lastMouseEvent = event;
      if (tooltipTimer) clearTimeout(tooltipTimer);
      tooltipTimer = setTimeout(() => showTooltip(lastMouseEvent, d), tooltipDelayMs);
    })
    .on('mousemove', (event) => {
      lastMouseEvent = event;
      const tooltip = document.getElementById('tooltip');
      if (tooltip && Number(tooltip.style.opacity) > 0) {
        tooltip.style.left = (event.pageX + 10) + 'px';
        tooltip.style.top = (event.pageY - 10) + 'px';
      }
    })
    .on('mouseout', () => {
      if (tooltipTimer) clearTimeout(tooltipTimer);
      hideTooltip();
    })
    .on('contextmenu', (event, d) => {
      // Suppression via clic droit
      event.preventDefault();
      event.stopPropagation();
      try {
        if (!window.hiddenNodeIds) {
          window.hiddenNodeIds = hiddenNodeIds instanceof Set ? hiddenNodeIds : new Set();
        }
        window.hiddenNodeIds.add(d.id);
        generateChart();
      } catch (e) {
        console.error('Erreur suppression bulle:', e);
      }
    })
    .on('click', (event, d) => {
      event.stopPropagation();
      highlightNodeAndDeps(d);
    })
    .on('dblclick', (event, d) => {
      event.preventDefault();
      event.stopPropagation();
      // Entrer dans la bulle: définir le parent sur le chemin de groupe complet
      const parentInput = document.getElementById('parentFolder');
      const norm = (p) => (p || '').replace(/\\/g, '/').replace(/\/+$/,'');
      const name = norm(d.name);
      parentInput.value = name;
      generateChart();
    });

  const textSelection = bubbles.append('text')
    .attr('class', 'bubble-text')
    .attr('dy', '0.3em')
    .style('cursor', 'pointer')
    .text(d => {
      const name = d.displayName || d.name.split('/').pop();
      return name.length > 14 ? name.substring(0, 14) + '…' : name;
    })
    .style('font-size', d => Math.min(radiusScale(d.value) / 3, 13) + 'px')
    .on('mouseover', (event, d) => {
      lastMouseEvent = event;
      if (tooltipTimer) clearTimeout(tooltipTimer);
      tooltipTimer = setTimeout(() => showTooltip(lastMouseEvent, d), tooltipDelayMs);
    })
    .on('mousemove', (event) => {
      lastMouseEvent = event;
      const tooltip = document.getElementById('tooltip');
      if (tooltip && Number(tooltip.style.opacity) > 0) {
        tooltip.style.left = (event.pageX + 10) + 'px';
        tooltip.style.top = (event.pageY - 10) + 'px';
      }
    })
    .on('mouseout', () => {
      if (tooltipTimer) clearTimeout(tooltipTimer);
      hideTooltip();
    })
    .on('contextmenu', (event, d) => {
      // Suppression via clic droit sur le label
      event.preventDefault();
      event.stopPropagation();
      try {
        if (!window.hiddenNodeIds) {
          window.hiddenNodeIds = hiddenNodeIds instanceof Set ? hiddenNodeIds : new Set();
        }
        window.hiddenNodeIds.add(d.id);
        generateChart();
      } catch (e) {
        console.error('Erreur suppression bulle (texte):', e);
      }
    })
    .on('click', (event, d) => {
      event.stopPropagation();
      highlightNodeAndDeps(d);
    })
    .on('dblclick', (event, d) => {
      event.preventDefault();
      event.stopPropagation();
      const parentInput = document.getElementById('parentFolder');
      const norm = (p) => (p || '').replace(/\\/g, '/').replace(/\/+$/,'');
      const name = norm(d.name);
      parentInput.value = name;
      generateChart();
    });

  // Règle d'affichage des labels: en taille standard => 100% des noms; sinon <= 20 bulles => 100% des noms; > 20 => 80% (masquer 20% les plus petites)
  const showAllLabels = isConstantSize || (simNodes.length <= 20);
  if (showAllLabels) {
    textSelection.style('display', null);
  } else {
    const valuesAsc = simNodes.map(n => n.value).sort((a, b) => a - b);
    const hideLabelThreshold = d3.quantile(valuesAsc, 0.20) || 0;
    textSelection.style('display', d => (d.value <= hideLabelThreshold ? 'none' : null));
  }

  function ticked() {
    bubbles.attr('transform', d => `translate(${d.x || 0},${d.y || 0})`);
    linkSelection
      .attr('x1', d => getEndpoints(d).x1)
      .attr('y1', d => getEndpoints(d).y1)
      .attr('x2', d => getEndpoints(d).x2)
      .attr('y2', d => getEndpoints(d).y2)
      .attr('stroke', d => {
        if (isMultiMode && d.cycle) return '#ef4444';
        if (isDirectMode && d.bidir) return '#ef4444';
        return d.weak ? '#f59e0b' : '#9aa0a6';
      })
      .attr('stroke-width', d => {
        if (isMultiMode && d.cycle) return 2.5;
        if (isDirectMode && d.bidir) return 2.5;
        return d.bidir ? 2 : 1.5;
      })
      .attr('stroke-dasharray', d => d.weak && !d.bidir ? '6,4' : 'none')
      .attr('marker-end', d => {
        if (isMultiMode && d.cycle) return 'url(#arrowhead-red)';
        if (isDirectMode && d.bidir) return 'url(#arrowhead-red)';
        return d.weak ? 'url(#arrowhead-weak)' : 'url(#arrowhead)';
      })
      .style('display', d => {
        if (cycleMode === 'multi') return d.cycle ? null : 'none';
        if (cycleMode === 'direct') return d.bidir ? null : 'none';
        if (cycleMode === 'weak') return d.weak ? null : 'none';
        return null;
      });
  }

  function getEndpoints(l) {
    const sx = l.source.x || 0;
    const sy = l.source.y || 0;
    const tx = l.target.x || 0;
    const ty = l.target.y || 0;
    const dx = tx - sx;
    const dy = ty - sy;
    const dist = Math.hypot(dx, dy) || 1;
    const rs = Math.max(5, radiusScale(l.source.value || 0));
    const rt = Math.max(5, radiusScale(l.target.value || 0)) + 2;
    const ux = dx / dist;
    const uy = dy / dist;
    return { x1: sx + ux * rs, y1: sy + uy * rs, x2: tx - ux * rt, y2: ty - uy * rt };
  }

  // Titre & légende
  const depthLabel = depth > 1 ? ` (prof. ${depth})` : '';
  document.getElementById('chartTitle').textContent = `Dossiers${parentFolder ? ` sous \"${parentFolder}\"` : ''} – groupe par ${depth} niveau(x) – ${getMetricLabelForFolders(metricKey)}${depthLabel}`;
  document.getElementById('chartTitle').style.display = 'block';
  renderLegend(simNodes, metricKey, cycleMode, parentFolder, resolvedColorKey === 'instability_avg' ? instabilityColorScale : null);

  // Afficher
  document.getElementById('loading').style.display = 'none';
  document.getElementById('chart').style.display = 'block';
  document.getElementById('legend').style.display = 'block';
  document.getElementById('chartControls').style.display = 'flex';
}

function renderLegend(nodes, metricKey, cycleMode, parentFolder, instabilityColorScale) {
  const legend = document.getElementById('legend');
  legend.innerHTML = '';
  nodes.forEach((d, i) => {
    const item = document.createElement('div');
    item.className = 'legend-item';
    const color = document.createElement('div');
    color.className = 'legend-color';
    color.style.backgroundColor = instabilityColorScale ? instabilityColorScale(Number(d.metrics?.instability_avg ?? 0)) : getColorForNode(d, parentFolder);
    const text = document.createElement('span');
    text.textContent = `${d.displayName || d.name}: ${d.value.toFixed(1)}`;
    item.appendChild(color);
    item.appendChild(text);
    legend.appendChild(item);
  });

  // Ajouter une échelle continue si colorée par instabilité
  if (instabilityColorScale) {
    const scaleContainer = document.createElement('div');
    scaleContainer.style.display = 'flex';
    scaleContainer.style.alignItems = 'center';
    scaleContainer.style.gap = '8px';
    scaleContainer.style.marginTop = '8px';

    const labelMin = document.createElement('span');
    labelMin.textContent = 'Stable (0)';
    const gradient = document.createElement('div');
    gradient.style.width = '160px';
    gradient.style.height = '10px';
    gradient.style.background = 'linear-gradient(to right, #16a34a, #f59e0b, #ef4444)';
    gradient.style.borderRadius = '4px';
    const labelMax = document.createElement('span');
    labelMax.textContent = 'Instable (1)';

    scaleContainer.appendChild(labelMin);
    scaleContainer.appendChild(gradient);
    scaleContainer.appendChild(labelMax);
    legend.appendChild(scaleContainer);
  }

  // Ajouter une sous-légende pour les types de liens
  const linkLegend = document.createElement('div');
  linkLegend.style.marginTop = '8px';

  const mkEntry = (label, style) => {
    const row = document.createElement('div');
    row.style.display = 'flex';
    row.style.alignItems = 'center';
    row.style.gap = '6px';
    const line = document.createElement('div');
    line.style.width = '28px';
    line.style.height = '0';
    line.style.borderTop = style;
    const span = document.createElement('span');
    span.textContent = label;
    row.appendChild(line);
    row.appendChild(span);
    return row;
  };

  linkLegend.appendChild(mkEntry('Lien normal', '2px solid #9aa0a6'));
  linkLegend.appendChild(mkEntry('Lien bidirectionnel', '2px solid #ef4444'));
  linkLegend.appendChild(mkEntry('Dépendance faible (cible plus instable)', '2px dashed #f59e0b'));
  if (cycleMode === 'multi') {
    linkLegend.appendChild(mkEntry('Lien dans cycle multi-composants', '2px solid #ef4444'));
  } else if (cycleMode === 'direct') {
    linkLegend.appendChild(mkEntry('Lien en cycle direct (A↔B)', '2px solid #ef4444'));
  } else if (cycleMode === 'all') {
    linkLegend.appendChild(mkEntry('Cycles (directs et multi)', '2px solid #ef4444'));
  }
  legend.appendChild(linkLegend);
}

function showTooltip(event, d) {
  const tooltip = document.getElementById('tooltip');
  tooltip.innerHTML = `
    <strong>${d.displayName || d.name}</strong><br>
    ${getMetricLabelForFolders('total')}: ${d.metrics.total || 0}<br>
    Classes: ${d.metrics.classes} | Interfaces: ${d.metrics.interfaces} | Abstracts: ${d.metrics.abstracts}<br>
    Eff: ${d.metrics.efferent_coupling} | Aff: ${d.metrics.afferent_coupling}<br>
    Instabilité moyenne: ${(d.metrics.instability_avg || 0).toFixed(3)}
  `;
  tooltip.style.opacity = 1;
  tooltip.style.left = (event.pageX + 10) + 'px';
  tooltip.style.top = (event.pageY - 10) + 'px';
}

document.getElementById('tooltip').style.opacity = 0;
function hideTooltip() {
  const tooltip = document.getElementById('tooltip');
  if (tooltip) {
    tooltip.style.opacity = 0;
  }
}
