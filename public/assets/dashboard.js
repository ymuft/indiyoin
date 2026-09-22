(() => {
  const fileInput = document.getElementById('ppv-file');
  const selected = document.getElementById('selected-file');
  if (fileInput && selected) {
    fileInput.addEventListener('change', () => {
      const file = fileInput.files && fileInput.files[0];
      selected.textContent = file ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB` : 'Nenhum arquivo selecionado';
    });
  }

  document.querySelectorAll('.coverage-ring').forEach((el) => {
    const value = Math.max(0, Math.min(100, Number(el.dataset.value || 0)));
    el.style.setProperty('--deg', `${value * 3.6}deg`);
  });

  const ns = 'http://www.w3.org/2000/svg';
  const make = (name, attrs = {}, text = '') => {
    const node = document.createElementNS(ns, name);
    Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, String(value)));
    if (text) node.textContent = text;
    return node;
  };

  document.querySelectorAll('.capacity-chart').forEach((root) => {
    let series = [];
    try { series = JSON.parse(root.dataset.series || '[]'); } catch (_) { return; }
    if (!Array.isArray(series) || series.length === 0) return;

    const width = 940, height = 320;
    const pad = {left: 54, right: 18, top: 24, bottom: 42};
    const chartW = width - pad.left - pad.right, chartH = height - pad.top - pad.bottom;
    const maxValue = Math.max(24, 21.5833, ...series.map(p => Number(p.hours || 0))) * 1.08;
    const x = (i) => pad.left + (series.length === 1 ? chartW / 2 : i * chartW / (series.length - 1));
    const y = (v) => pad.top + chartH - (Number(v) / maxValue) * chartH;
    const svg = make('svg', {viewBox:`0 0 ${width} ${height}`, role:'img', 'aria-label':'Horas necessárias por dia por período'});

    [0, 8, 16, 24].filter(v => v <= maxValue).forEach(v => {
      svg.appendChild(make('line',{x1:pad.left,y1:y(v),x2:width-pad.right,y2:y(v),class:'chart-grid-line'}));
      svg.appendChild(make('text',{x:pad.left-10,y:y(v)+4,'text-anchor':'end',class:'chart-axis-label'},`${v}h`));
    });

    [[8.75,'1T'],[16.4167,'2T'],[21.5833,'3T']].forEach(([v,label]) => {
      svg.appendChild(make('line',{x1:pad.left,y1:y(v),x2:width-pad.right,y2:y(v),class:'chart-threshold'}));
      svg.appendChild(make('text',{x:width-pad.right,y:y(v)-6,'text-anchor':'end',class:'chart-threshold-label'},`${label} · ${Number(v).toFixed(2)}h`));
    });

    const points = series.map((p,i) => [x(i), y(p.hours)]);
    const linePath = points.map((p,i) => `${i === 0 ? 'M' : 'L'} ${p[0]} ${p[1]}`).join(' ');
    const areaPath = `${linePath} L ${points[points.length-1][0]} ${pad.top+chartH} L ${points[0][0]} ${pad.top+chartH} Z`;
    svg.appendChild(make('path',{d:areaPath,class:'chart-area'}));
    svg.appendChild(make('path',{d:linePath,class:'chart-path'}));

    series.forEach((p,i) => {
      const px=x(i), py=y(p.hours), over=Number(p.hours)>21.5833;
      svg.appendChild(make('circle',{cx:px,cy:py,r:5,class:`chart-point${over?' over':''}`}));
      svg.appendChild(make('text',{x:px,y:py-11,'text-anchor':'middle',class:'chart-value'},`${Number(p.hours).toFixed(1)}h`));
      svg.appendChild(make('text',{x:px,y:height-15,'text-anchor':'middle',class:'chart-axis-label'},String(p.period)));
    });
    root.replaceChildren(svg);
  });
})();
