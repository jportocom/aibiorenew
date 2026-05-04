let chartInstance = null;
let chartFullscreen = null;
let pesos_cache = null;
let obj_cache = null;
let currentVista = 1;

document.addEventListener('DOMContentLoaded', async () => {
  if (!isLoggedIn()) { location.href = '/dev/medepeso/area-pessoal.html'; return; }
  await carregarDashboard();
});

async function carregarDashboard() {
  const metricas = await apiCall('/calc');
  if (metricas && !metricas.erro) {
    document.getElementById('val-imc').textContent  = metricas.imc;
    document.getElementById('cls-imc').textContent  = metricas.classificacao_imc;
    document.getElementById('val-tmb').textContent  = metricas.tmb + ' kcal';
    document.getElementById('val-tdee').textContent = metricas.tdee + ' kcal';
  }

  const plano = await apiCall('/plano/hoje');
  if (plano?.plano) {
    const p = plano.plano;
    document.getElementById('plano-pa').textContent  = p.pequeno_almoco + ' kcal';
    document.getElementById('plano-al').textContent  = p.almoco + ' kcal';
    document.getElementById('plano-ja').textContent  = p.jantar + ' kcal';
    document.getElementById('plano-cam').textContent = p.calorias_caminhada + ' kcal';
  }

  const aval = await apiCall('/plano/avaliacao');
  if (aval && !aval.erro) {
    document.getElementById('aval-estado').textContent  = aval.estado;
    document.getElementById('aval-saldo').textContent   = aval.saldo_calorico + ' kcal';
    document.getElementById('aval-msg').textContent     = aval.mensagem;
    document.getElementById('aval-perda').textContent   = aval.perda_estimada_g + 'g';
  }

  obj_cache   = await apiCall('/objetivos/activo');
  pesos_cache = await apiCall('/peso');

  renderizarObjetivo(obj_cache, pesos_cache);
  await mostrarGrafico(currentVista);
}

// ── Gráfico ──────────────────────────────────────────────────────────────────
async function mostrarGrafico(tipo) {
  currentVista = tipo;
  if (!pesos_cache) pesos_cache = await apiCall('/peso');
  if (!obj_cache)   obj_cache   = await apiCall('/objetivos/activo');
  if (!pesos_cache || pesos_cache.erro || !pesos_cache.length) return;

  if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
  const ctx = document.getElementById('chart-principal').getContext('2d');
  chartInstance = buildChart(ctx, tipo, pesos_cache, obj_cache);
}

function buildChart(ctx, tipo, pesos, obj, fullscreen) {
  const labels  = pesos.map(p => new Date(p.data).toLocaleDateString('pt-PT'));
  const valores = pesos.map(p => parseFloat(p.peso));
  const n       = valores.length;

  // ── Opção 1: Evolução do Peso ─────────────────────────────────────────────
  if (tipo === 1) {
    const ptCores = calcularCoresPontos(valores);
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Peso (kg)',
          data: valores,
          borderColor: '#1a6b35',
          backgroundColor: 'transparent',
          pointBackgroundColor: ptCores,
          pointBorderColor: ptCores,
          tension: 0.3,
          borderWidth: 2,
          fill: false,
          segment: {
            borderColor: c => valores[c.p1DataIndex] > valores[c.p0DataIndex]
              ? '#dc2626' : '#1a6b35'
          }
        }]
      },
      options: chartOptions('Evolução do Peso', fullscreen)
    });
  }

  // ── Opção 2: Ev. Peso e Objectivo ─────────────────────────────────────────
  if (tipo === 2) {
    const lineColor = (n >= 2 && valores[n-1] > valores[n-2]) ? '#dc2626' : '#1a6b35';
    const firstDate = new Date(pesos[0].data);

    const datasets = [{
      label: 'Peso (kg)',
      data: valores,
      borderColor: lineColor,
      backgroundColor: 'transparent',
      pointBackgroundColor: lineColor,
      tension: 0.3,
      borderWidth: 2.5,
      pointRadius: 4,
      fill: false
    }];

    const objData = pesos.map(p => {
      const dias = Math.round((new Date(p.data) - firstDate) / 86400000);
      return parseFloat((valores[0] - dias * 0.10).toFixed(2));
    });
    datasets.push({
      label: 'Objectivo médio (−100g/dia)',
      data: objData,
      borderColor: '#2563eb',
      backgroundColor: 'transparent',
      pointRadius: 0,
      tension: 0.1,
      borderWidth: 2,
      fill: false
    });

    return new Chart(ctx, {
      type: 'line',
      data: { labels, datasets },
      options: chartOptions('Ev. Peso e Objectivo', fullscreen)
    });
  }

  // ── Opção 3: Objectivo ────────────────────────────────────────────────────
  if (tipo === 3) {
    const firstDate  = new Date(pesos[0].data);
    const firstPeso  = valores[0];
    const lastDate   = new Date(pesos[n-1].data);
    const lastPeso   = valores[n-1];

    const endDate = (obj && !obj.erro && obj.data_fim)
      ? new Date(obj.data_fim)
      : new Date(lastDate.getTime() + 30 * 86400000);

    const allDates = [];
    const cur = new Date(firstDate);
    while (cur <= endDate) { allDates.push(new Date(cur)); cur.setDate(cur.getDate() + 1); }

    const fmt         = d => d.toLocaleDateString('pt-PT');
    const projLabels  = allDates.map(fmt);
    const lastLabel   = fmt(lastDate);
    let   useIdx      = projLabels.indexOf(lastLabel);
    if (useIdx < 0) useIdx = projLabels.length - 1;

    const projUpper  = allDates.map(d => parseFloat((firstPeso - Math.round((d - firstDate) / 86400000) * 0.05).toFixed(2)));
    const projMiddle = allDates.map(d => parseFloat((firstPeso - Math.round((d - firstDate) / 86400000) * 0.10).toFixed(2)));
    const projLower  = allDates.map(d => parseFloat((firstPeso - Math.round((d - firstDate) / 86400000) * 0.15).toFixed(2)));

    const upperAtLast = projUpper[useIdx];
    const lowerAtLast = projLower[useIdx];
    const pointColor  = lastPeso > upperAtLast ? '#dc2626' : '#1a6b35';

    // Ponto: array com null em todo o lado exceto useIdx
    const pointData   = projLabels.map((_, i) => i === useIdx ? lastPeso : null);
    const pointRadii  = projLabels.map((_, i) => i === useIdx ? 9 : 0);

    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: projLabels,
        datasets: [
          {
            label: 'Limite superior (−50g/dia)',
            data: projUpper,
            borderColor: 'rgba(220,38,38,0.8)',
            backgroundColor: 'transparent',
            pointRadius: 0, tension: 0.1, borderWidth: 2, fill: false
          },
          {
            label: 'Objectivo médio (−100g/dia)',
            data: projMiddle,
            borderColor: 'rgba(37,99,235,0.9)',
            backgroundColor: 'transparent',
            pointRadius: 0, tension: 0.1, borderWidth: 2, fill: false,
            segment: { borderDash: () => [8, 4] }
          },
          {
            label: 'Limite inferior (−150g/dia)',
            data: projLower,
            borderColor: 'rgba(26,107,53,0.8)',
            backgroundColor: 'transparent',
            pointRadius: 0, tension: 0.1, borderWidth: 2, fill: false
          },
          {
            label: 'Última medição — ' + lastLabel + ' (' + lastPeso.toFixed(1) + ' kg)',
            data: pointData,
            borderColor: pointColor,
            backgroundColor: pointColor,
            pointBackgroundColor: pointColor,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: pointRadii,
            pointHoverRadius: pointRadii.map(r => r > 0 ? r + 2 : 0),
            showLine: false,
            fill: false
          }
        ]
      },
      options: chartOptions('Objectivo', fullscreen)
    });
  }
}

// ── Fullscreen ────────────────────────────────────────────────────────────────
function abrirFullscreen() {
  const modal = document.getElementById('modal-grafico');
  modal.style.display = 'flex';
  // Sincronizar radios do modal com a vista actual
  document.querySelectorAll('input[name="vistaModal"]').forEach(r => {
    r.checked = parseInt(r.value) === currentVista;
  });
  // Duplo requestAnimationFrame: garante que o canvas está visível e dimensionado
  setTimeout(() => _renderFullscreen(currentVista), 100);
}

function fecharFullscreen() {
  document.getElementById('modal-grafico').style.display = 'none';
  if (chartFullscreen) { chartFullscreen.destroy(); chartFullscreen = null; }
}

function mudarVistaModal(tipo) {
  if (chartFullscreen) { chartFullscreen.destroy(); chartFullscreen = null; }
  setTimeout(() => _renderFullscreen(tipo), 50);
}

function _renderFullscreen(tipo) {
  if (chartFullscreen) { chartFullscreen.destroy(); chartFullscreen = null; }
  if (!pesos_cache || !pesos_cache.length) return;
  const canvas = document.getElementById('chart-fullscreen');
  const wrap   = canvas.parentElement;
  const rect   = wrap.getBoundingClientRect();
  canvas.width  = Math.floor(rect.width)  || 800;
  canvas.height = Math.floor(rect.height) || 500;
  const ctx = canvas.getContext('2d');
  chartFullscreen = buildChart(ctx, tipo, pesos_cache, obj_cache, true);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function calcularCoresPontos(valores) {
  return valores.map((v, i) => i === 0 ? '#1a6b35' : (v > valores[i-1] ? '#dc2626' : '#1a6b35'));
}

function chartOptions(titulo, fullscreen) {
  return {
    responsive: !fullscreen,
    maintainAspectRatio: !fullscreen,
    plugins: {
      legend: { display: true, position: 'bottom', labels: { boxWidth: 14, font: { size: 12 } } },
      title:  { display: true, text: titulo, font: { size: 14, weight: '600' } }
    },
    scales: { y: { beginAtZero: false } }
  };
}

// ── Registos ──────────────────────────────────────────────────────────────────
async function registarPeso() {
  const el   = document.getElementById('novo-peso');
  const peso = parseFloat(el.value);
  if (!peso || peso < 20 || peso > 300) { alert('Introduza um peso válido (20–300 kg)'); return; }
  const res = await apiCall('/peso', 'POST', { peso });
  if (res?.id) {
    el.value    = '';
    pesos_cache = null;
    alert('Peso registado com sucesso!');
    await carregarDashboard();
  } else {
    alert('Erro ao registar peso: ' + (res?.erro || 'Resposta inesperada do servidor'));
  }
}

async function registarRefeicao(tipo) {
  const el       = document.getElementById(`cal-${tipo}`);
  const calorias = parseFloat(el.value);
  if (!calorias || calorias < 0) { alert('Introduza um valor de calorias válido'); return; }
  const res = await apiCall('/refeicoes', 'POST', { tipo, calorias });
  if (res?.id) {
    el.value = '';
    alert('Refeição registada!');
    await carregarDashboard();
  } else {
    alert('Erro ao registar refeição: ' + (res?.erro || 'Tente novamente'));
  }
}

async function registarCaminhada() {
  const el  = document.getElementById('min-caminhada');
  const min = parseInt(el.value);
  if (!min || min < 1) { alert('Introduza a duração em minutos'); return; }
  const res = await apiCall('/atividades', 'POST', { tipo: 'caminhada', duracao_min: min });
  if (res?.id) {
    el.value = '';
    alert(`Caminhada registada! ${res.calorias} kcal gastas.`);
    await carregarDashboard();
  } else {
    alert('Erro ao registar caminhada: ' + (res?.erro || 'Tente novamente'));
  }
}

async function criarObjectivo(dias) {
  const res = await apiCall('/objetivos', 'POST', { duracao_dias: dias });
  if (res?.id) {
    obj_cache = res;
    renderizarObjetivo(obj_cache, pesos_cache);
  } else {
    alert('Erro ao criar objectivo: ' + (res?.erro || 'Tente novamente'));
  }
}

async function verCardapios(tipo) {
  const lista = document.getElementById('lista-cardapios');
  lista.innerHTML = '<p style="color:var(--texto-3);font-size:0.9rem;">A carregar...</p>';
  const cards = await apiCall('/cardapios?tipo=' + tipo);
  if (!cards || cards.erro || !cards.length) {
    lista.innerHTML = '<p style="color:var(--texto-3);font-size:0.9rem;">Sem sugestões disponíveis.</p>';
    return;
  }
  lista.innerHTML = cards.map(c => `
    <div class="cardapio-item">
      <strong>${c.nome}</strong>
      <span>${c.calorias} kcal &bull; P: ${c.proteina}g &bull; H: ${c.hidratos}g &bull; G: ${c.gordura}g</span>
      <br><span style="font-size:0.75rem;color:var(--texto-3);margin-top:0.25rem;display:block;">${c.ingredientes || ''}</span>
    </div>
  `).join('');
}

// ── Objectivo — renderização e edição ────────────────────────────────────────
function fmtData(d) {
  if (!d) return '—';
  const s = (typeof d === 'string' ? d : d.toISOString()).split('T')[0];
  const p = s.split('-');
  return p[2] + '/' + p[1] + '/' + p[0];
}

function objStat(label, value, unit) {
  return `<div class="obj-stat">
    <div class="osl">${label}</div>
    <div class="osv">${value}</div>
    <div class="osu">${unit || '&nbsp;'}</div>
  </div>`;
}

function renderizarObjetivo(obj, pesos) {
  const area = document.getElementById('obj-area');
  if (!area) return;

  const btnsCriar = `<button class="btn-obj" onclick="criarObjectivo(30)">+ 30 dias</button>
    <button class="btn-obj" onclick="criarObjectivo(90)">+ 90 dias</button>
    <button class="btn-obj" onclick="criarObjectivo(180)">+ 180 dias</button>`;

  if (!obj || obj.erro) {
    area.innerHTML = `<p style="color:var(--texto-3);font-size:0.9rem;margin-bottom:0.5rem;">Nenhum objectivo activo. Crie um:</p>
      <div class="obj-btns" style="margin-top:0;">${btnsCriar}</div>`;
    return;
  }

  const piStr = (obj.data_inicio || '').split('T')[0];
  const pfStr = (obj.data_fim    || '').split('T')[0];
  const dIni  = new Date(piStr + 'T00:00:00');
  const dFim  = new Date(pfStr + 'T00:00:00');
  const hoje  = new Date(); hoje.setHours(0, 0, 0, 0);

  const diasTotal = Math.round((dFim - dIni)  / 86400000);
  const diasRest  = Math.round((dFim - hoje)  / 86400000);
  const pesoInicio = parseFloat(obj.peso_inicio);
  const alvoMin    = parseFloat(obj.peso_alvo_min);
  const alvoMax    = parseFloat(obj.peso_alvo_max);
  const pesoAlvo   = Math.round((alvoMin + alvoMax) / 2 * 100) / 100;
  const pesoAtual  = (pesos && pesos.length) ? parseFloat(pesos[pesos.length - 1].peso) : pesoInicio;

  const perdaTotal = Math.round((pesoInicio - pesoAlvo) * 100) / 100;
  const maxPerda   = Math.round(diasTotal * 10) / 100;
  const perdaRest  = Math.round((pesoAtual  - pesoAlvo) * 100) / 100;
  const taxaDiaG   = diasRest > 0 ? Math.round(perdaRest / diasRest * 1000) : 0;
  const taxaSemG   = taxaDiaG * 7;

  let html = `<div class="obj-stat-grid">`;
  html += objStat('Peso Inicial',  pesoInicio.toFixed(1), 'kg');
  html += objStat('Data Início',   fmtData(piStr), '');
  html += objStat('Data Fim',      fmtData(pfStr), '');
  html += objStat('Dias Restantes', diasRest > 0 ? diasRest : 0, 'dias');
  html += objStat('Peso Actual',   pesoAtual.toFixed(1), 'kg');
  html += objStat('Peso Alvo',     pesoAlvo.toFixed(1),  'kg');
  if (perdaRest > 0 && diasRest > 0) {
    html += objStat('A Perder',      perdaRest.toFixed(1), 'kg');
    html += objStat('Taxa Diária',   taxaDiaG, 'g/dia');
    html += objStat('Taxa Semanal',  taxaSemG, 'g/sem');
  }
  html += `</div>`;

  if (diasRest <= 0) {
    html += `<div class="obj-aviso-erro">A data de conclusão já passou. Edite ou crie um novo objectivo.</div>`;
  } else if (perdaTotal > 0 && perdaTotal > maxPerda) {
    const pesoAjust   = parseFloat((pesoInicio - maxPerda).toFixed(2));
    const diasNec     = Math.ceil(Math.round(perdaTotal * 100) / 10);
    const dNovaFim    = new Date(dIni); dNovaFim.setDate(dIni.getDate() + diasNec);
    const novaDataFim = dNovaFim.toISOString().split('T')[0];
    html += `<div class="obj-aviso">
      <p class="obj-aviso-titulo">&#9888; Objectivo exigente — acima de 100 g/dia</p>
      <p class="obj-aviso-corpo">Em ${diasTotal} dias a perda ao ritmo de 100 g/dia é <strong>${maxPerda.toFixed(2).replace('.',',')} kg</strong>.
      Para perder <strong>${perdaTotal.toFixed(2).replace('.',',')} kg</strong> são necessários <strong>${diasNec} dias</strong>.</p>
      <div class="obj-btns-ajuste">
        <button class="btn-ajuste btn-ajuste-verde"
          onclick="ajustarObjetivo('${obj.id}','${piStr}','${pfStr}',${pesoInicio},${pesoAjust})">
          Manter ${fmtData(pfStr)} — ajustar peso alvo para ${pesoAjust.toFixed(2).replace('.',',')} kg
        </button>
        <button class="btn-ajuste btn-ajuste-azul"
          onclick="ajustarObjetivo('${obj.id}','${piStr}','${novaDataFim}',${pesoInicio},${pesoAlvo})">
          Manter ${pesoAlvo.toFixed(2).replace('.',',')} kg — ajustar data para ${fmtData(novaDataFim)}
        </button>
      </div>
    </div>`;
  } else if (perdaRest > 0 && diasRest > 0) {
    html += `<p class="obj-nota">Ao ritmo de <strong>${taxaDiaG} g/dia</strong>, atingirá <strong>${pesoAlvo.toFixed(1)} kg</strong> em <strong>${fmtData(pfStr)}</strong>.</p>`;
  }

  html += `<div class="obj-btns">
    <button class="btn-obj" onclick="abrirEdicaoObjetivo()">&#9998; Editar</button>
    ${btnsCriar}
  </div>`;

  area.innerHTML = html;
}

function abrirEdicaoObjetivo() {
  if (!obj_cache || obj_cache.erro) return;
  const piStr  = (obj_cache.data_inicio || '').split('T')[0];
  const pfStr  = (obj_cache.data_fim    || '').split('T')[0];
  const alvoMin = parseFloat(obj_cache.peso_alvo_min);
  const alvoMax = parseFloat(obj_cache.peso_alvo_max);
  const pesoAlvo = Math.round((alvoMin + alvoMax) / 2 * 100) / 100;
  document.getElementById('obj-edit-id').value          = obj_cache.id;
  document.getElementById('obj-edit-inicio').value      = piStr;
  document.getElementById('obj-edit-fim').value         = pfStr;
  document.getElementById('obj-edit-peso-inicio').value = parseFloat(obj_cache.peso_inicio).toFixed(1);
  document.getElementById('obj-edit-peso-alvo').value   = pesoAlvo.toFixed(1);
  document.getElementById('msg-objetivo-modal').innerHTML = '';
  document.getElementById('modal-objetivo').style.display = 'flex';
}

function fecharEdicaoObjetivo() {
  document.getElementById('modal-objetivo').style.display = 'none';
}

async function guardarEdicaoObjetivo() {
  const id          = document.getElementById('obj-edit-id').value;
  const data_inicio = document.getElementById('obj-edit-inicio').value;
  const data_fim    = document.getElementById('obj-edit-fim').value;
  const peso_inicio = parseFloat(document.getElementById('obj-edit-peso-inicio').value);
  const peso_alvo   = parseFloat(document.getElementById('obj-edit-peso-alvo').value);
  const msgEl       = document.getElementById('msg-objetivo-modal');

  if (!data_inicio || !data_fim) { msgEl.innerHTML = '<div class="mmsg-erro">Preencha as datas.</div>'; return; }
  if (!peso_inicio || peso_inicio < 20 || peso_inicio > 300) { msgEl.innerHTML = '<div class="mmsg-erro">Peso inicial inválido (20–300 kg).</div>'; return; }
  if (!peso_alvo   || peso_alvo   < 20 || peso_alvo   > 300) { msgEl.innerHTML = '<div class="mmsg-erro">Peso alvo inválido (20–300 kg).</div>'; return; }
  if (data_fim <= data_inicio) { msgEl.innerHTML = '<div class="mmsg-erro">Data fim deve ser após data início.</div>'; return; }
  if (peso_alvo >= peso_inicio) { msgEl.innerHTML = '<div class="mmsg-erro">Peso alvo deve ser inferior ao peso inicial.</div>'; return; }

  const res = await apiCall('/objetivos/' + id, 'PUT', { data_inicio, data_fim, peso_inicio, peso_alvo });
  if (res && !res.erro) {
    obj_cache = res;
    fecharEdicaoObjetivo();
    renderizarObjetivo(obj_cache, pesos_cache);
  } else {
    msgEl.innerHTML = '<div class="mmsg-erro">Erro: ' + (res?.erro || 'Tente novamente') + '</div>';
  }
}

async function ajustarObjetivo(id, data_inicio, data_fim, peso_inicio, peso_alvo) {
  const res = await apiCall('/objetivos/' + id, 'PUT', { data_inicio, data_fim, peso_inicio, peso_alvo });
  if (res && !res.erro) {
    obj_cache = res;
    renderizarObjetivo(obj_cache, pesos_cache);
  } else {
    alert('Erro ao ajustar: ' + (res?.erro || 'Tente novamente'));
  }
}

function logout() {
  clearToken();
  location.href = '/dev/medepeso/area-pessoal.html';
}
