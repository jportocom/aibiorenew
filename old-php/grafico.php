<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
if (empty($_SESSION['email'])) {
    header('Location: /dev/medepeso/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico de Evolução — Porto Metabolic Health</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .grafico-pagina {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .grafico-topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--s4);
            padding: var(--s5) 0 var(--s4);
        }
        .grafico-topo h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--texto);
            margin: 0;
        }
        .opcoes-vista {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--s3);
            background: var(--verde-muted);
            border: 1px solid var(--verde-border);
            border-radius: var(--r);
            padding: var(--s3) var(--s4);
        }
        .opcoes-vista label {
            display: flex;
            align-items: center;
            gap: var(--s2);
            font-size: .875rem;
            color: var(--texto-2);
            cursor: pointer;
            font-weight: 500;
            white-space: nowrap;
        }
        .opcoes-vista input[type="radio"] {
            accent-color: var(--verde);
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }
        @media (max-width: 640px) {
            .grafico-topo { flex-direction: column; align-items: stretch; }
            .opcoes-vista { flex-direction: column; align-items: flex-start; }
            .opcoes-vista label { white-space: normal; }
        }
        .grafico-canvas-wrap {
            background: var(--branco);
            border: 1px solid var(--borda);
            border-radius: var(--r);
            padding: var(--s5);
            box-shadow: var(--shadow-sm);
            flex: 1;
        }
        .grafico-canvas-wrap canvas {
            width: 100% !important;
            height: 65vh !important;
        }
        .legenda-grafico {
            display: flex;
            gap: var(--s5);
            margin-top: var(--s4);
            flex-wrap: wrap;
            justify-content: center;
        }
        .legenda-item {
            display: flex;
            align-items: center;
            gap: var(--s2);
            font-size: .8rem;
            color: var(--texto-2);
        }
        .legenda-cor {
            width: 24px;
            height: 3px;
            border-radius: 2px;
        }
        .grafico-sem-objetivo {
            font-size: .8rem;
            color: var(--texto-3);
            margin-top: var(--s3);
            text-align: center;
        }
        .aviso-objetivo {
            margin-top: var(--s4);
            background: rgba(234,179,8,.07);
            border: 1px solid rgba(234,179,8,.35);
            border-radius: var(--r);
            padding: var(--s4) var(--s5);
        }
        .aviso-obj-titulo {
            font-size: .82rem;
            font-weight: 700;
            color: #92400e;
            margin-bottom: var(--s2);
        }
        .aviso-obj-texto {
            font-size: .8rem;
            color: var(--texto-3);
            line-height: 1.6;
            margin-bottom: var(--s4);
        }
        .aviso-obj-acoes { display:flex; gap:var(--s3); flex-wrap:wrap; }
        .aviso-obj-btn {
            font-size: .78rem;
            font-family: inherit;
            font-weight: 600;
            padding: var(--s2) var(--s4);
            border-radius: var(--r);
            border: 1px solid;
            cursor: pointer;
            background: transparent;
            transition: background .15s;
            line-height: 1.4;
            text-align: left;
        }
        .aviso-obj-btn-peso { color:var(--verde); border-color:var(--verde-border); }
        .aviso-obj-btn-peso:hover { background:var(--verde-muted); }
        .aviso-obj-btn-data { color:#2563eb; border-color:rgba(37,99,235,.3); }
        .aviso-obj-btn-data:hover { background:rgba(37,99,235,.06); }
        .aviso-obj-btn:disabled { opacity:.5; cursor:default; }
    </style>
</head>
<body>

<nav class="nav">
    <a href="/" class="nav-logo">
        <span class="label">Porto</span>
        <span class="brand">Metabolic Health</span>
    </a>
    <button class="nav-hamburger" onclick="toggleNav()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <ul class="nav-menu" id="navMenu">
        <li><a href="/metodo">Método</a></li>
        <li class="nav-dropdown dropdown-aberto">
            <a href="/dev/medepeso/" class="ativo">Gestão de Peso</a>
            <ul class="nav-sub">
                <li><a href="/dev/medepeso/inserir.php">Inserir Medição</a></li>
                <li><a href="/dev/medepeso/editar.php">Editar Medição</a></li>
                <li><a href="/dev/medepeso/apagar.php">Apagar Medição</a></li>
                <li><a href="/dev/medepeso/listar.php" class="ativo">Listar Medições</a></li>
                <li><a href="/dev/medepeso/gestaodaapp.php">Gestão de App</a></li>
            </ul>
        </li>
        <li><a href="/sobre">Sobre</a></li>
        <li><a href="/oferta">O que fazemos</a></li>
        <li><a href="/precos">Preços</a></li>
        <li><a href="/faq">FAQ</a></li>
        <li><a href="/contacto">Contacto</a></li>
        <li><a href="https://app.porto-metabolic.com/start" class="nav-btn">Iniciar avaliação</a></li>
        <li class="nav-item-login">
            <div class="nav-login-form" id="lbForm">
                <input type="email" id="lbEmail" placeholder="Email" autocomplete="email">
                <input type="password" id="lbPassword" placeholder="Password" autocomplete="current-password">
                <button type="button" onclick="lbLogin()">Entrar</button>
            </div>
            <span class="nav-login-erro" id="lbErro"></span>
            <div class="nav-login-sessao" id="lbSessao" style="display:none">
                <span class="saudacao">Olá, <span id="lbNome"></span></span>
                <button type="button" class="btn-logout" onclick="lbLogout()">Sair</button>
            </div>
        </li>
    </ul>
</nav>

<section style="flex:1;">
    <div class="wrap grafico-pagina">
        <div class="grafico-topo">
            <div style="display:flex; align-items:center; gap:var(--s4);">
                <a href="/dev/medepeso/listar.php" style="color:var(--texto-3); font-size:.85rem; text-decoration:none;">&#8592; Voltar</a>
                <h1>Evolução do Peso</h1>
            </div>
            <div class="opcoes-vista">
                <label>
                    <input type="radio" name="vistaGrafico" id="opcaoA" value="A" checked onchange="atualizarVista()">
                    só evolução
                </label>
                <label>
                    <input type="radio" name="vistaGrafico" id="opcaoB" value="B" onchange="atualizarVista()">
                    evolução + objectivo
                </label>
                <label>
                    <input type="radio" name="vistaGrafico" id="opcaoC" value="C" onchange="atualizarVista()">
                    só projecção
                </label>
            </div>
        </div>
        <div id="msgCarregar" style="text-align:center; padding:var(--s9); color:var(--texto-3);">A carregar…</div>
        <div id="areaGrafico" style="display:none; flex:1; display:flex; flex-direction:column;">
            <div class="grafico-canvas-wrap" style="flex:1;">
                <canvas id="grafico"></canvas>
                <div id="legendaGrafico" class="legenda-grafico"></div>
                <div id="msgSemObjetivo" class="grafico-sem-objetivo" style="display:none;"></div>
                <div id="avisoObjetivo" class="aviso-objetivo" style="display:none;">
                    <p class="aviso-obj-titulo">&#9888; Objectivo não atingível ao ritmo de 100 g/dia</p>
                    <p class="aviso-obj-texto" id="avisoObjTexto"></p>
                    <div class="aviso-obj-acoes">
                        <button id="avisoObjBtnPeso" class="aviso-obj-btn aviso-obj-btn-peso"></button>
                        <button id="avisoObjBtnData" class="aviso-obj-btn aviso-obj-btn-data"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
function toggleNav() { document.getElementById('navMenu').classList.toggle('aberto'); }

function formatarData(iso) {
    var p = iso.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}

var graficoChart = null;
var dadosGlobais = { medicoes: [], objetivo: null };

function pesoNaData(medicoes, dataISO) {
    for (var i = 0; i < medicoes.length; i++) {
        if (medicoes[i].data <= dataISO) return parseFloat(medicoes[i].peso);
    }
    return medicoes.length > 0 ? parseFloat(medicoes[medicoes.length - 1].peso) : null;
}

function filtrarParaGrafico(medicoes) {
    var n = medicoes.length;
    if (n <= 30) return medicoes;

    if (n <= 90) {
        var resultado = [medicoes[0]];
        for (var i = 1; i < n; i++) {
            var dAnt = new Date(resultado[resultado.length - 1].data + 'T00:00:00');
            var dAt  = new Date(medicoes[i].data + 'T00:00:00');
            if ((dAt - dAnt) / 86400000 >= 3) resultado.push(medicoes[i]);
        }
        if (resultado[resultado.length - 1].id !== medicoes[n - 1].id) resultado.push(medicoes[n - 1]);
        return resultado;
    }

    var resultado = medicoes.filter(function(m) {
        return new Date(m.data + 'T00:00:00').getDay() === 5;
    });

    if (resultado.length < 2) {
        resultado = [medicoes[0]];
        for (var i = 1; i < n; i++) {
            var dAnt = new Date(resultado[resultado.length - 1].data + 'T00:00:00');
            var dAt  = new Date(medicoes[i].data + 'T00:00:00');
            if ((dAt - dAnt) / 86400000 >= 7) resultado.push(medicoes[i]);
        }
    }

    if (resultado.length === 0 || resultado[resultado.length - 1].id !== medicoes[n - 1].id) {
        resultado.push(medicoes[n - 1]);
    }
    return resultado;
}

function dateToISO(d) {
    return d.getFullYear() + '-' +
           String(d.getMonth() + 1).padStart(2, '0') + '-' +
           String(d.getDate()).padStart(2, '0');
}

var opcoesGrafico = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: function(ctx) {
                    if (ctx.parsed.y === null) return null;
                    return ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + ' kg';
                }
            }
        }
    },
    scales: {
        y: {
            title: { display: true, text: 'Peso (kg)' },
            ticks: { callback: function(v) { return v.toFixed(1) + ' kg'; } }
        },
        x: {
            title: { display: true, text: 'Data' },
            ticks: { maxTicksLimit: 14, maxRotation: 45 }
        }
    }
};

function destruirGrafico() {
    if (graficoChart) { graficoChart.destroy(); graficoChart = null; }
}

function verificarViabilidade(medicoes, objetivo) {
    if (!objetivo || !objetivo.data_ini_objetiv) return null;
    var pesoIni = pesoNaData(medicoes, objetivo.data_ini_objetiv);
    if (pesoIni === null) return null;
    var pesoFim          = parseFloat(objetivo.peso_objetivo);
    var perdaNecessaria  = Math.round((pesoIni - pesoFim) * 100) / 100;
    if (perdaNecessaria <= 0) return null;
    var dIni = new Date(objetivo.data_ini_objetiv + 'T00:00:00');
    var dFim = new Date(objetivo.data_objetivo    + 'T00:00:00');
    var dias     = Math.round((dFim - dIni) / 86400000);
    var maxPerda = Math.round(dias * 10) / 100;
    if (perdaNecessaria <= maxPerda) return null;
    var pesoAjustado    = Math.round((pesoIni - maxPerda) * 100) / 100;
    var diasNecessarios = Math.ceil(Math.round(perdaNecessaria * 100) / 10);
    var dNovaFim = new Date(dIni);
    dNovaFim.setDate(dNovaFim.getDate() + diasNecessarios);
    return { pesoFim, dias, perdaNecessaria, maxPerda, pesoAjustado, diasNecessarios,
             novaDataFim: dateToISO(dNovaFim) };
}

function mostrarAvisoViabilidade(v, objetivo) {
    var el = document.getElementById('avisoObjetivo');
    if (!v) { el.style.display = 'none'; return; }
    document.getElementById('avisoObjTexto').innerHTML =
        'Ao ritmo de 100 g/dia, em <strong>' + v.dias + ' dias</strong> a perda máxima é ' +
        '<strong>' + v.maxPerda.toFixed(2).replace('.', ',') + ' kg</strong>. ' +
        'Para perder <strong>' + v.perdaNecessaria.toFixed(2).replace('.', ',') + ' kg</strong> ' +
        'são necessários <strong>' + v.diasNecessarios + ' dias</strong>.';
    var btnPeso = document.getElementById('avisoObjBtnPeso');
    var btnData = document.getElementById('avisoObjBtnData');
    btnPeso.textContent = 'Manter ' + formatarData(objetivo.data_objetivo) +
        ' — ajustar peso para ' + v.pesoAjustado.toFixed(2).replace('.', ',') + ' kg';
    btnData.textContent = 'Manter ' + v.pesoFim.toFixed(2).replace('.', ',') +
        ' kg — ajustar data para ' + formatarData(v.novaDataFim);
    btnPeso.onclick = function() {
        guardarObjetivoAjustado(objetivo.data_objetivo, v.pesoAjustado, objetivo.data_ini_objetiv);
    };
    btnData.onclick = function() {
        guardarObjetivoAjustado(v.novaDataFim, v.pesoFim, objetivo.data_ini_objetiv);
    };
    el.style.display = 'block';
}

async function guardarObjetivoAjustado(dataObj, pesoObj, dataIni) {
    var btnPeso = document.getElementById('avisoObjBtnPeso');
    var btnData = document.getElementById('avisoObjBtnData');
    btnPeso.disabled = btnData.disabled = true;
    try {
        var resp = await fetch('/dev/api/medepeso_objetivo_guardar.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ peso_objetivo: pesoObj, data_objetivo: dataObj, data_ini_objetiv: dataIni })
        });
        var dados = await resp.json();
        if (dados.sucesso) {
            dadosGlobais.objetivo = { peso_objetivo: pesoObj, data_objetivo: dataObj, data_ini_objetiv: dataIni };
            document.getElementById('avisoObjetivo').style.display = 'none';
            var opcao = document.querySelector('input[name="vistaGrafico"]:checked').value;
            construirGrafico(opcao);
        }
    } catch (e) {}
    btnPeso.disabled = btnData.disabled = false;
}

function construirGrafico(opcao) {
    var medicoes  = dadosGlobais.medicoes;
    var objetivo  = dadosGlobais.objetivo;
    var msgSemObj = document.getElementById('msgSemObjetivo');
    var legenda   = document.getElementById('legendaGrafico');

    msgSemObj.style.display = 'none';
    document.getElementById('avisoObjetivo').style.display = 'none';
    destruirGrafico();

    if (opcao === 'C') {
        if (!objetivo) {
            msgSemObj.innerHTML = 'Opção requer um objectivo definido em ' +
                '<a href="/dev/medepeso/gestaodaapp.php" style="color:var(--verde);">Gestão de App</a>.';
            msgSemObj.style.display = 'block';
            legenda.innerHTML = '';
            return;
        }

        var cronologicasC = medicoes.slice().reverse();
        if (cronologicasC.length === 0) return;

        var pesoFim = parseFloat(objetivo.peso_objetivo);
        var dFim    = new Date(objetivo.data_objetivo + 'T00:00:00');

        var dInicio, pesoInicio;
        if (objetivo.data_ini_objetiv) {
            dInicio    = new Date(objetivo.data_ini_objetiv + 'T00:00:00');
            pesoInicio = pesoNaData(medicoes, objetivo.data_ini_objetiv);
        } else {
            dInicio    = new Date(); dInicio.setHours(0, 0, 0, 0);
            pesoInicio = parseFloat(cronologicasC[cronologicasC.length - 1].peso);
        }

        var totalDias = (dFim - dInicio) / 86400000;

        if (totalDias <= 0) {
            msgSemObj.innerHTML = 'A data objectivo já passou. ' +
                '<a href="/dev/medepeso/gestaodaapp.php" style="color:var(--verde);">Actualize o objectivo</a>.';
            msgSemObj.style.display = 'block';
            legenda.innerHTML = '';
            return;
        }

        /* Construir pontos da projecção como objectos {iso, proj} */
        var pontosProj = [];
        pontosProj.push({ iso: dateToISO(dInicio), proj: pesoInicio });

        var d = new Date(dInicio);
        var diasParaSexta = (5 - d.getDay() + 7) % 7;
        if (diasParaSexta === 0) diasParaSexta = 7;
        d.setDate(d.getDate() + diasParaSexta);

        while (d < dFim) {
            var diasDesdeInicio = (d - dInicio) / 86400000;
            var pesoInterp = pesoInicio + (pesoFim - pesoInicio) * (diasDesdeInicio / totalDias);
            pontosProj.push({ iso: dateToISO(d), proj: pesoInterp });
            d.setDate(d.getDate() + 7);
        }

        pontosProj.push({ iso: objetivo.data_objetivo, proj: pesoFim });

        /* Ponto de hoje: medição actual vs projecção */
        var hoje = new Date(); hoje.setHours(0, 0, 0, 0);
        var hojeISO = dateToISO(hoje);
        var pontoHoje = null;

        if (hoje > dInicio && hoje < dFim) {
            var diasHoje      = (hoje - dInicio) / 86400000;
            var pesoHojeProj  = pesoInicio + (pesoFim - pesoInicio) * (diasHoje / totalDias);
            var pesoHojeMedido = pesoNaData(medicoes, hojeISO);
            if (pesoHojeMedido !== null) {
                var corPonto = pesoHojeMedido > pesoHojeProj ? '#dc2626' : '#16a34a';
                pontoHoje = { iso: hojeISO, peso: pesoHojeMedido, cor: corPonto };
                var jaTemHoje = pontosProj.some(function(p) { return p.iso === hojeISO; });
                if (!jaTemHoje) {
                    pontosProj.push({ iso: hojeISO, proj: pesoHojeProj });
                }
            }
        }

        /* Ordenar cronologicamente e extrair arrays finais */
        pontosProj.sort(function(a, b) { return a.iso.localeCompare(b.iso); });
        var labelsC = pontosProj.map(function(p) { return formatarData(p.iso); });
        var dadosC  = pontosProj.map(function(p) { return p.proj; });

        var datasetsC = [{
            label: 'Projecção',
            data: dadosC,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.08)',
            tension: 0,
            spanGaps: false,
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: false
        }];

        if (pontoHoje) {
            var dadosPontoHoje = pontosProj.map(function(p) {
                return p.iso === pontoHoje.iso ? pontoHoje.peso : null;
            });
            datasetsC.push({
                label: 'Medição actual',
                data: dadosPontoHoje,
                borderColor: pontoHoje.cor,
                backgroundColor: pontoHoje.cor,
                pointRadius: 10,
                pointHoverRadius: 12,
                showLine: false,
                spanGaps: false
            });
        }

        var ctx = document.getElementById('grafico').getContext('2d');
        graficoChart = new Chart(ctx, {
            type: 'line',
            data: { labels: labelsC, datasets: datasetsC },
            options: opcoesGrafico
        });

        var legendaHtml =
            '<div class="legenda-item">' +
            '<div class="legenda-cor" style="background:#2563eb;"></div>' +
            'Azul — projecção objectivo (início + sextas-feiras + data objectivo)</div>';

        if (pontoHoje) {
            legendaHtml +=
                '<div class="legenda-item">' +
                '<div class="legenda-cor" style="background:' + pontoHoje.cor + '; width:12px; height:12px; border-radius:50%;"></div>' +
                (pontoHoje.cor === '#dc2626'
                    ? 'Vermelho — medição actual acima da projecção'
                    : 'Verde — medição actual dentro da projecção') +
                '</div>';
        }

        mostrarAvisoViabilidade(verificarViabilidade(medicoes, objetivo), objetivo);
        legenda.innerHTML = legendaHtml;
        return;
    }

    var cronologicas = medicoes.slice().reverse();
    if (cronologicas.length === 0) return;

    var corLinha = '#16a34a';
    if (cronologicas.length >= 2) {
        var penultima = parseFloat(cronologicas[cronologicas.length - 2].peso);
        var ultima    = parseFloat(cronologicas[cronologicas.length - 1].peso);
        if (ultima > penultima) corLinha = '#dc2626';
    }

    var filtradas  = filtrarParaGrafico(cronologicas);
    var dataHoje   = dateToISO(new Date());
    var pesoUltimo = parseFloat(cronologicas[cronologicas.length - 1].peso);
    var mostrarObjetivo = (opcao === 'B');

    var dataIniProj = (mostrarObjetivo && objetivo && objetivo.data_ini_objetiv)
        ? objetivo.data_ini_objetiv : dataHoje;
    var pesoIniProj = (mostrarObjetivo && objetivo && objetivo.data_ini_objetiv)
        ? pesoNaData(medicoes, objetivo.data_ini_objetiv) : pesoUltimo;

    var labels;
    if (mostrarObjetivo && objetivo) {
        /* Opção B: escala uniforme dia-a-dia para que o eixo X represente tempo real */
        var cursor  = new Date(filtradas[0].data + 'T00:00:00');
        var dFimObj = new Date(objetivo.data_objetivo + 'T00:00:00');
        labels = [];
        while (cursor <= dFimObj) {
            labels.push(dateToISO(cursor));
            cursor.setDate(cursor.getDate() + 1);
        }
    } else {
        var datasSet = {};
        filtradas.forEach(function(m) { datasSet[m.data] = true; });
        datasSet[dataHoje] = true;
        labels = Object.keys(datasSet).sort();
    }

    var dadosAtual = labels.map(function(l) {
        var m = filtradas.find(function(x) { return x.data === l; });
        return m ? parseFloat(m.peso) : null;
    });

    /* Opção B: prolongar a linha verde até à data de início do objetivo.
       Sem isto, a linha termina na última medição real e fica separada da linha azul. */
    if (mostrarObjetivo && objetivo) {
        var idxObjIni = labels.indexOf(dataIniProj);
        if (idxObjIni > 0 && dadosAtual[idxObjIni] === null) {
            for (var k = idxObjIni - 1; k >= 0; k--) {
                if (dadosAtual[k] !== null) { dadosAtual[idxObjIni] = dadosAtual[k]; break; }
            }
        }
    }

    var datasets = [{
        label: 'Peso registado',
        data: dadosAtual,
        borderColor: corLinha,
        backgroundColor: corLinha + '18',
        tension: 0.3,
        spanGaps: true,
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: false,
        order: 1
    }];

    var temObjetivo = mostrarObjetivo && objetivo;
    if (temObjetivo) {
        var dadosObj = labels.map(function(l) {
            if (l === dataIniProj) return pesoIniProj;
            if (l === objetivo.data_objetivo) return parseFloat(objetivo.peso_objetivo);
            return null;
        });
        datasets.push({
            label: 'Objectivo',
            data: dadosObj,
            borderColor: '#2563eb',
            backgroundColor: 'transparent',
            tension: 0,
            spanGaps: true,
            pointRadius: 6,
            pointHoverRadius: 7,
            borderDash: [7, 4],
            fill: false,
            order: 2
        });
    }

    var ctx = document.getElementById('grafico').getContext('2d');
    graficoChart = new Chart(ctx, {
        type: 'line',
        data: { labels: labels.map(formatarData), datasets: datasets },
        options: opcoesGrafico
    });

    var corNome = corLinha === '#16a34a' ? 'Verde — tendência de perda' : 'Vermelho — tendência de ganho';
    legenda.innerHTML =
        '<div class="legenda-item">' +
        '<div class="legenda-cor" style="background:' + corLinha + ';"></div>' +
        corNome + '</div>';
    if (temObjetivo) {
        legenda.innerHTML +=
            '<div class="legenda-item">' +
            '<div class="legenda-cor" style="background:#2563eb; border-top:2px dashed #2563eb; background:none; border-bottom:none;"></div>' +
            'Azul — projecção objectivo</div>';
        mostrarAvisoViabilidade(verificarViabilidade(medicoes, objetivo), objetivo);
    }

    if (mostrarObjetivo && !objetivo) {
        msgSemObj.innerHTML = 'Esta opção requer um objectivo definido em ' +
            '<a href="/dev/medepeso/gestaodaapp.php" style="color:var(--verde);">Gestão de App</a>.';
        msgSemObj.style.display = 'block';
    }
}

function atualizarVista() {
    var opcao = document.querySelector('input[name="vistaGrafico"]:checked').value;
    construirGrafico(opcao);
}

async function carregarDados() {
    try {
        var [rMedicoes, rObjetivo] = await Promise.all([
            fetch('/dev/api/medepeso_listar.php'),
            fetch('/dev/api/medepeso_objetivo_obter.php')
        ]);
        var dMedicoes = await rMedicoes.json();
        var dObjetivo = await rObjetivo.json();

        document.getElementById('msgCarregar').style.display = 'none';

        if (rMedicoes.status === 401) {
            document.getElementById('msgCarregar').textContent = 'Sessão expirada.';
            document.getElementById('msgCarregar').style.display = 'block';
            return;
        }
        if (!dMedicoes.sucesso || dMedicoes.medicoes.length === 0) {
            document.getElementById('msgCarregar').textContent = 'Sem medições para mostrar.';
            document.getElementById('msgCarregar').style.display = 'block';
            return;
        }

        dadosGlobais.medicoes = dMedicoes.medicoes;
        dadosGlobais.objetivo = (dObjetivo.sucesso && dObjetivo.objetivo) ? dObjetivo.objetivo : null;

        document.getElementById('areaGrafico').style.display = 'flex';
        construirGrafico('A');

    } catch (err) {
        document.getElementById('msgCarregar').textContent = 'Erro de ligação. Recarregue a página.';
    }
}

carregarDados();
</script>
<script src="/assets/loginbar.js"></script>
</body>
</html>
