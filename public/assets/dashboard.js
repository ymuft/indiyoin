(() => {
  const nf0 = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 });
  const nf1 = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
  const nf2 = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const fileInput = document.getElementById('ppv-file');
  const selected = document.getElementById('selected-file');
  const dropzone = document.getElementById('ppv-dropzone');
  const analyzeButton = document.getElementById('analyze-button');

  const showFile = (file) => {
    if (!selected) return;
    selected.textContent = file
      ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB`
      : 'Nenhum arquivo selecionado';
  };

  if (fileInput && selected) {
    fileInput.addEventListener('change', () => showFile(fileInput.files && fileInput.files[0]));
  }

  if (dropzone && fileInput) {
    ['dragenter', 'dragover'].forEach((eventName) => {
      dropzone.addEventListener(eventName, (event) => {
        event.preventDefault();
        dropzone.classList.add('dragging');
      });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
      dropzone.addEventListener(eventName, (event) => {
        event.preventDefault();
        dropzone.classList.remove('dragging');
      });
    });

    dropzone.addEventListener('drop', (event) => {
      const files = event.dataTransfer && event.dataTransfer.files;
      if (!files || files.length === 0) return;
      const dt = new DataTransfer();
      dt.items.add(files[0]);
      fileInput.files = dt.files;
      showFile(files[0]);
    });
  }

  const uploadForm = document.getElementById('ppv-upload-form');
  if (uploadForm && analyzeButton) {
    uploadForm.addEventListener('submit', () => {
      analyzeButton.disabled = true;
      analyzeButton.textContent = 'Analisando PPV…';
    });
  }

  document.querySelectorAll('.coverage-ring').forEach((el) => {
    const value = Math.max(0, Math.min(100, Number(el.dataset.value || 0)));
    el.style.setProperty('--deg', `${value * 3.6}deg`);
  });

  const inspector = document.getElementById('period-inspector');
  let setInspectorPeriod = null;

  if (inspector) {
    let periods = [];
    let models = [];
    try {
      periods = JSON.parse(inspector.dataset.periods || '[]');
      models = JSON.parse(inspector.dataset.models || '[]');
    } catch (_) {
      periods = [];
      models = [];
    }

    const select = document.getElementById('period-select');
    const breakdown = document.getElementById('model-breakdown');
    const warning = document.getElementById('period-warning');
    const kpi = {
      demandTotal: document.getElementById('period-demand-total'),
      demandMatched: document.getElementById('period-demand-matched'),
      hoursMonth: document.getElementById('period-hours-month'),
      hoursDay: document.getElementById('period-hours-day'),
      coverage: document.getElementById('period-coverage'),
    };

    const buildModelRow = (model, maxHours) => {
      const row = document.createElement('div');
      row.className = 'model-row';

      const identity = document.createElement('div');
      identity.className = 'model-identity';
      const name = document.createElement('strong');
      name.textContent = model.model;
      const meta = document.createElement('span');
      meta.textContent = `${nf0.format(model.demand)} peças · CT ${nf2.format(model.ct)}s · OEE ${nf1.format(Number(model.oee) * 100)}%`;
      identity.append(name, meta);

      const bar = document.createElement('div');
      bar.className = 'model-bar';
      const fill = document.createElement('i');
      fill.style.width = `${maxHours > 0 ? Math.max(2, 100 * Number(model.hours_day) / maxHours) : 0}%`;
      bar.appendChild(fill);

      const value = document.createElement('div');
      value.className = 'model-value';
      const hours = document.createElement('strong');
      hours.textContent = `${nf2.format(model.hours_day)} h/d`;
      const month = document.createElement('span');
      month.textContent = `${nf2.format(model.hours_month)} h/mês`;
      value.append(hours, month);

      row.append(identity, bar, value);
      return row;
    };

    setInspectorPeriod = (periodName, emit = false) => {
      const period = periods.find((item) => item.period === periodName) || periods[0];
      if (!period) return;

      if (select) select.value = period.period;
      if (kpi.demandTotal) kpi.demandTotal.textContent = nf0.format(period.demand_total);
      if (kpi.demandMatched) kpi.demandMatched.textContent = nf0.format(period.demand_matched);
      if (kpi.hoursMonth) kpi.hoursMonth.textContent = `${nf2.format(period.hours_month)} h`;
      if (kpi.hoursDay) kpi.hoursDay.textContent = `${nf2.format(period.hours_day)} h/d`;
      if (kpi.coverage) kpi.coverage.textContent = `${nf1.format(period.coverage_pct)}%`;

      if (warning) {
        const incomplete = Number(period.coverage_pct) < 99.95;
        warning.hidden = !incomplete;
        warning.textContent = incomplete
          ? `${nf0.format(period.demand_pending)} peças deste período ainda não entram no cálculo por falta de parâmetro técnico validado.`
          : '';
      }

      if (breakdown) {
        breakdown.replaceChildren();
        const periodModels = models
          .filter((model) => model.period === period.period)
          .sort((a, b) => Number(b.hours_day) - Number(a.hours_day));

        if (periodModels.length === 0) {
          const empty = document.createElement('div');
          empty.className = 'empty-breakdown';
          empty.textContent = 'Nenhum modelo deste período possui CT/OEE resolvido.';
          breakdown.appendChild(empty);
        } else {
          const maxHours = Math.max(...periodModels.map((model) => Number(model.hours_day) || 0), 0);
          periodModels.forEach((model) => breakdown.appendChild(buildModelRow(model, maxHours)));
        }
      }

      inspector.dataset.activePeriod = period.period;
      if (emit) {
        document.dispatchEvent(new CustomEvent('indiyoin:period-changed', { detail: { period: period.period } }));
      }
    };

    if (select) {
      select.addEventListener('change', () => setInspectorPeriod(select.value, true));
    }

    setInspectorPeriod(inspector.dataset.defaultPeriod || (periods[0] && periods[0].period) || '');
  }

  const ns = 'http://www.w3.org/2000/svg';
  const make = (name, attrs = {}, text = '') => {
    const node = document.createElementNS(ns, name);
    Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, String(value)));
    if (text) node.textContent = text;
    return node;
  };

  document.querySelectorAll('.capacity-chart').forEach((root) => {
    let series = [];
    try {
      series = JSON.parse(root.dataset.series || '[]');
    } catch (_) {
      return;
    }
    if (!Array.isArray(series) || series.length === 0) return;

    const width = 980;
    const height = 340;
    const pad = { left: 58, right: 20, top: 28, bottom: 48 };
    const chartW = width - pad.left - pad.right;
    const chartH = height - pad.top - pad.bottom;
    const maxValue = Math.max(24, 21.5833, ...series.map((p) => Number(p.hours || 0))) * 1.08;
    const x = (i) => pad.left + (series.length === 1 ? chartW / 2 : i * chartW / (series.length - 1));
    const y = (v) => pad.top + chartH - (Number(v) / maxValue) * chartH;
    const svg = make('svg', {
      viewBox: `0 0 ${width} ${height}`,
      role: 'img',
      'aria-label': 'Horas necessárias por dia por período',
    });

    [0, 8, 16, 24, 32].filter((v) => v <= maxValue).forEach((v) => {
      svg.appendChild(make('line', { x1: pad.left, y1: y(v), x2: width - pad.right, y2: y(v), class: 'chart-grid-line' }));
      svg.appendChild(make('text', { x: pad.left - 10, y: y(v) + 4, 'text-anchor': 'end', class: 'chart-axis-label' }, `${v}h`));
    });

    [[8.75, '1T'], [16.4167, '2T'], [21.5833, '3T']].forEach(([v, label]) => {
      svg.appendChild(make('line', { x1: pad.left, y1: y(v), x2: width - pad.right, y2: y(v), class: 'chart-threshold' }));
      svg.appendChild(make('text', { x: width - pad.right, y: y(v) - 6, 'text-anchor': 'end', class: 'chart-threshold-label' }, `${label} · ${Number(v).toFixed(2)}h`));
    });

    const points = series.map((p, i) => [x(i), y(p.hours)]);
    const linePath = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p[0]} ${p[1]}`).join(' ');
    const areaPath = `${linePath} L ${points[points.length - 1][0]} ${pad.top + chartH} L ${points[0][0]} ${pad.top + chartH} Z`;
    svg.appendChild(make('path', { d: areaPath, class: 'chart-area' }));
    svg.appendChild(make('path', { d: linePath, class: 'chart-path' }));

    const selectable = [];
    const activate = (period) => {
      selectable.forEach(({ group, data }) => group.classList.toggle('active', data.period === period));
      root.dataset.activePeriod = period;
    };

    series.forEach((p, i) => {
      const px = x(i);
      const py = y(p.hours);
      const over = Number(p.hours) > 21.5833;
      const incomplete = Number(p.coverage) < 99.95;
      const group = make('g', {
        class: 'chart-point-group',
        tabindex: '0',
        role: 'button',
        'aria-label': `${p.period}: ${Number(p.hours).toFixed(2)} horas por dia, cobertura ${Number(p.coverage).toFixed(1)}%`,
      });
      const hit = make('circle', { cx: px, cy: py, r: 14, class: 'chart-hit' });
      const point = make('circle', { cx: px, cy: py, r: 5, class: `chart-point${over ? ' over' : ''}${incomplete ? ' partial' : ''}` });
      const value = make('text', { x: px, y: py - 13, 'text-anchor': 'middle', class: 'chart-value' }, `${Number(p.hours).toFixed(1)}h`);
      const label = make('text', { x: px, y: height - 16, 'text-anchor': 'middle', class: 'chart-axis-label chart-period-label' }, String(p.period));
      group.append(hit, point, value, label);

      const choose = () => {
        activate(p.period);
        if (setInspectorPeriod) setInspectorPeriod(p.period);
        document.getElementById('period-inspector')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      };

      group.addEventListener('click', choose);
      group.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          choose();
        }
      });

      selectable.push({ group, data: p });
      svg.appendChild(group);
    });

    root.replaceChildren(svg);
    activate(root.dataset.defaultPeriod || series[0].period);

    document.addEventListener('indiyoin:period-changed', (event) => {
      if (event.detail && event.detail.period) activate(event.detail.period);
    });
  });

  const issueSearch = document.getElementById('issue-search');
  if (issueSearch) {
    issueSearch.addEventListener('input', () => {
      const query = issueSearch.value.trim().toLocaleLowerCase('pt-BR');
      document.querySelectorAll('#issue-grid .issue-item').forEach((item) => {
        item.hidden = query !== '' && !String(item.dataset.search || '').includes(query);
      });
    });
  }
})();
