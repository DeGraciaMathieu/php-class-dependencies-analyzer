// Inline des styles essentiels dans une copie du SVG (pour export fidèle)
function getInlineStyledSVGString() {
  const original = document.getElementById('chart');
  const clone = original.cloneNode(true);
  // S'assurer que les namespaces sont présents sur la racine
  if (!clone.getAttribute('xmlns')) {
    clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
  }
  if (!clone.getAttribute('xmlns:xlink')) {
    clone.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
  }

  // Forcer les styles qui dépendaient du CSS externe
  const qsa = (sel) => Array.from(clone.querySelectorAll(sel));

  // Libellés des bulles: conserver et inliner les styles
  qsa('text.bubble-text').forEach(el => {
    el.setAttribute('fill', '#ffffff');
    el.setAttribute('text-anchor', 'middle');
    el.setAttribute('font-weight', '600');
    el.setAttribute('font-family', '-apple-system, BlinkMacSystemFont, sans-serif');
  });

  // Groupes dimmés
  qsa('g.bubble.dimmed').forEach(g => {
    g.setAttribute('opacity', '0.25');
  });

  // Surbrillance/adjacence des cercles
  qsa('g.bubble.highlight > circle').forEach(c => {
    c.setAttribute('stroke', '#111827');
    c.setAttribute('stroke-width', '3');
    c.setAttribute('opacity', '1');
  });
  qsa('g.bubble.adjacent > circle').forEach(c => {
    c.setAttribute('stroke', '#2563eb');
    c.setAttribute('stroke-width', '2');
    c.setAttribute('opacity', '0.95');
  });

  // Liens dimmés/surbrillance
  qsa('line.dep-link').forEach(l => {
    if (l.classList.contains('highlight')) {
      l.setAttribute('stroke-width', '3');
      l.setAttribute('opacity', '1');
    } else if (l.classList.contains('dimmed')) {
      l.setAttribute('opacity', '0.15');
    }
  });

  return new XMLSerializer().serializeToString(clone);
}

function getCurrentSvgSize() {
  const svg = document.getElementById('chart');
  const widthAttr = parseFloat(svg.getAttribute('width'));
  const heightAttr = parseFloat(svg.getAttribute('height'));
  const width = Number.isFinite(widthAttr) && widthAttr > 0 ? widthAttr : (svg.clientWidth || 1200);
  const height = Number.isFinite(heightAttr) && heightAttr > 0 ? heightAttr : (svg.clientHeight || 700);
  return { width, height };
}

// Export PNG
function downloadChartAsPNG() {
  const svgData = getInlineStyledSVGString();
  const { width, height } = getCurrentSvgSize();
  const exportScale = 0.75; // Réduction de la taille d'export
  const targetWidth = Math.max(1, Math.round(width * exportScale));
  const targetHeight = Math.max(1, Math.round(height * exportScale));
  const scale = (window.devicePixelRatio || 1);
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  canvas.style.width = targetWidth + 'px';
  canvas.style.height = targetHeight + 'px';
  canvas.width = Math.max(1, Math.round(targetWidth * scale));
  canvas.height = Math.max(1, Math.round(targetHeight * scale));
  const img = new Image();
  const url = URL.createObjectURL(new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' }));
  img.onload = function () {
    ctx.setTransform(scale, 0, 0, scale, 0, 0);
    ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, targetWidth, targetHeight);
    ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
    // Pas de titre ni de labels à l'export
    canvas.toBlob(function (blob) {
      const link = document.createElement('a');
      link.download = `folders-bubbles-${Date.now()}.png`;
      link.href = URL.createObjectURL(blob);
      link.click();
      URL.revokeObjectURL(url); URL.revokeObjectURL(link.href);
    });
  };
  img.src = url;
}

// Export SVG
function downloadChartAsSVG() {
  const svgData = getInlineStyledSVGString();
  const exportScale = 0.75; // Réduction de la taille d'export
  const complete = createCompleteSVG(svgData, exportScale);
  const blob = new Blob([complete], { type: 'image/svg+xml;charset=utf-8' });
  const link = document.createElement('a');
  link.download = `folders-bubbles-${Date.now()}.svg`;
  link.href = URL.createObjectURL(blob);
  link.click();
  URL.revokeObjectURL(link.href);
}

function addTitleAndLegendToCanvas(ctx, canvas) {
  const title = document.getElementById('chartTitle').textContent;
  ctx.fillStyle = '#374151';
  ctx.font = 'bold 18px -apple-system, BlinkMacSystemFont, sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText(title, canvas.width / 2, 30);
  if (currentData && currentData.nodes && currentData.nodes.length > 0) {
    ctx.font = '12px -apple-system, BlinkMacSystemFont, sans-serif';
    ctx.textAlign = 'left';
    let legendY = canvas.height - 60;
    let legendX = 20;
    const parentFolder = currentData.options?.parentFolder || '';
    currentData.nodes.slice(0, 6).forEach((d, i) => {
      const color = (typeof getColorForNode === 'function') ? getColorForNode(d, parentFolder) : (d3.schemeCategory10[i % 10]);
      ctx.fillStyle = color; ctx.fillRect(legendX, legendY, 12, 12);
      ctx.fillStyle = '#64748b'; ctx.fillText(`${d.displayName || d.name}: ${d.value.toFixed(1)}`, legendX + 20, legendY + 10);
      legendX += 160; if (legendX > canvas.width - 160) { legendX = 20; legendY += 20; }
    });
  }
}

function createCompleteSVG(chartSvgData, exportScale = 1) {
  const parentFolder = currentData?.options?.parentFolder || '';
  const baseSvg = document.getElementById('chart');
  const baseWidth = Number(baseSvg.getAttribute('width')) || baseSvg.clientWidth || 1200;
  const baseHeight = Number(baseSvg.getAttribute('height')) || baseSvg.clientHeight || 700;
  // Pas de titre ni de légende dans l'export SVG
  const outWidth = Math.max(1, Math.round(baseWidth * exportScale));
  const outHeight = Math.max(1, Math.round(baseHeight * exportScale));
  return `<?xml version="1.0" encoding="UTF-8"?>
  <svg width="${outWidth}" height="${outHeight}" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <rect width="100%" height="100%" fill="white"/>
    <g transform="translate(0, 0) scale(${exportScale})">${chartSvgData.replace('<svg', '<g').replace('</svg>', '</g>')}</g>
  </svg>`;
}
