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
    <title>Gestão de App — Porto Metabolic Health</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .objetivo-painel {
            background: var(--branco);
            border: 1px solid var(--borda);
            border-radius: var(--r);
            padding: var(--s6);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--s6);
        }
        .objetivo-painel h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--texto);
            margin-bottom: var(--s5);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: var(--s4);
            margin-bottom: var(--s6);
        }
        .stat-card {
            background: var(--verde-muted);
            border: 1px solid var(--verde-border);
            border-radius: var(--r);
            padding: var(--s4) var(--s5);
            text-align: center;
        }
        .stat-card .stat-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--texto-3);
            margin-bottom: var(--s2);
        }
        .stat-card .stat-valor {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--verde);
            line-height: 1.1;
        }
        .stat-card .stat-unidade {
            font-size: .8rem;
            font-weight: 400;
            color: var(--texto-2);
            margin-top: var(--s1);
        }
        .sem-objetivo {
            text-align: center;
            padding: var(--s7) var(--s5);
            color: var(--texto-3);
            font-size: .95rem;
        }
        .sem-objetivo span {
            display: block;
            font-size: 2.5rem;
            margin-bottom: var(--s4);
        }
        .painel-form {
            background: var(--fundo);
            border: 1px solid var(--borda);
            border-radius: var(--r);
            padding: var(--s6);
        }
        .painel-form h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--texto);
            margin-bottom: var(--s5);
        }
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
                <li><a href="/dev/medepeso/listar.php">Listar Medições</a></li>
                <li><a href="/dev/medepeso/gestaodaapp.php" class="ativo">Gestão de App</a></li>
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

<div class="page-hero">
    <span class="eyebrow">Gestão de Peso</span>
    <h1>Gestão de App</h1>
    <p class="lead">Defina o seu objectivo de peso e acompanhe a projecção necessária para o atingir.</p>
</div>

<section>
    <div class="wrap" style="max-width:860px;">

        <div id="areaObjetivo">
            <div class="objetivo-painel">
                <div class="sem-objetivo">
                    <span>&#127919;</span>
                    A carregar…
                </div>
            </div>
        </div>

        <div class="painel-form">
            <h3 id="tituloForm">Definir Objectivo</h3>
            <div id="msgSucesso" class="callout" style="display:none; margin-bottom:var(--s5);">
                Objectivo guardado com sucesso.
            </div>
            <div id="msgErro" class="callout" style="display:none; margin-bottom:var(--s5); background:#fef2f2; border-color:#fca5a5; color:#991b1b;"></div>
            <form id="formObjetivo" onsubmit="guardarObjetivo(event)" style="max-width:480px;">
                <div class="form-group">
                    <label for="campoPeso">Peso objectivo (kg)</label>
                    <input type="number" id="campoPeso" name="peso_objetivo"
                           step="0.01" min="1" max="999.99"
                           placeholder="Ex: 80.00"
                           autocomplete="off"
                           required>
                </div>
                <div class="form-group">
                    <label for="campoDataIni">Data início objectivo</label>
                    <input type="date" id="campoDataIni" name="data_ini_objetiv"
                           autocomplete="off"
                           required>
                </div>
                <div class="form-group">
                    <label for="campoData">Data fim objectivo</label>
                    <input type="date" id="campoData" name="data_objetivo"
                           autocomplete="off"
                           required>
                </div>
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    Guardar Objectivo
                </button>
            </form>
        </div>

    </div>
</section>

<footer class="footer">
    <div class="wrap">
        <div class="footer-grid">
            <div class="footer-brand">
                <span class="label">Porto</span>
                <span class="name">Metabolic Health</span>
                <p>Triagem estruturada, decisão automática e protocolo progressivo para problemas metabólicos e comportamentais.</p>
            </div>
            <div class="footer-col">
                <h5>Navegação</h5>
                <ul>
                    <li><a href="/metodo">Método</a></li>
                    <li><a href="/sobre">Sobre nós</a></li>
                    <li><a href="/oferta">O que fazemos</a></li>
                    <li><a href="/precos">Preços</a></li>
                    <li><a href="/faq">FAQ</a></li>
                    <li><a href="/contacto">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Legal</h5>
                <ul>
                    <li><a href="/legal">Política de Privacidade</a></li>
                    <li><a href="/legal">Termos de Utilização</a></li>
                    <li><a href="/legal">Proteção de Dados</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; 2026 Porto Metabolic Health. Todos os direitos reservados.</span>
            <span>porto-metabolic.com</span>
        </div>
    </div>
</footer>

<script>
function toggleNav() { document.getElementById('navMenu').classList.toggle('aberto'); }

function formatarData(iso) {
    var p = iso.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}

function dateToISO(d) {
    return d.getFullYear() + '-' +
           String(d.getMonth() + 1).padStart(2, '0') + '-' +
           String(d.getDate()).padStart(2, '0');
}

function pesoNaData(medicoes, dataISO) {
    for (var i = 0; i < medicoes.length; i++) {
        if (medicoes[i].data <= dataISO) return parseFloat(medicoes[i].peso);
    }
    return medicoes.length > 0 ? parseFloat(medicoes[medicoes.length - 1].peso) : null;
}

var pesoAtual      = null;
var medicoesTodas  = [];
var _gestaoAviso   = null;   /* params para os botões de ajuste */

async function ajustarGestaoObjetivo(tipo) {
    if (!_gestaoAviso) return;
    var p         = _gestaoAviso;
    var dataFinal = (tipo === 1) ? p.dataFim    : p.novaDataFim;
    var pesoFinal = (tipo === 1) ? p.pesoAjust  : p.pesoFim;
    try {
        var resp = await fetch('/dev/api/medepeso_objetivo_guardar.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ peso_objetivo: pesoFinal, data_objetivo: dataFinal, data_ini_objetiv: p.dataIni })
        });
        var dados = await resp.json();
        if (dados.sucesso) {
            document.getElementById('campoPeso').value = pesoFinal.toFixed(2);
            document.getElementById('campoData').value = dataFinal;
            mostrarObjetivo({ peso_objetivo: pesoFinal, data_objetivo: dataFinal, data_ini_objetiv: p.dataIni });
        }
    } catch (e) {}
}

async function carregarDados() {
    try {
        var [rMedicoes, rObjetivo] = await Promise.all([
            fetch('/dev/api/medepeso_listar.php'),
            fetch('/dev/api/medepeso_objetivo_obter.php')
        ]);
        var dMedicoes = await rMedicoes.json();
        var dObjetivo = await rObjetivo.json();

        if (rMedicoes.status === 401 || rObjetivo.status === 401) {
            document.getElementById('areaObjetivo').innerHTML =
                '<div class="objetivo-painel"><div class="sem-objetivo">Sessão expirada. ' +
                '<a href="/dev/" style="color:var(--verde)">Faça login novamente</a>.</div></div>';
            return;
        }

        if (dMedicoes.sucesso && dMedicoes.medicoes.length > 0) {
            medicoesTodas = dMedicoes.medicoes;
            pesoAtual     = parseFloat(dMedicoes.medicoes[0].peso);
        }

        mostrarObjetivo(dObjetivo.objetivo);

    } catch (err) {
        document.getElementById('areaObjetivo').innerHTML =
            '<div class="objetivo-painel"><div class="sem-objetivo">Erro de ligação. Recarregue a página.</div></div>';
    }
}

function mostrarObjetivo(objetivo) {
    var area = document.getElementById('areaObjetivo');
    var tituloForm = document.getElementById('tituloForm');

    if (!objetivo) {
        area.innerHTML =
            '<div class="objetivo-painel">' +
            '<div class="sem-objetivo">' +
            '<span>&#127919;</span>' +
            'Ainda não definiu um objectivo de peso. Use o formulário abaixo para começar.' +
            '</div></div>';
        tituloForm.textContent = 'Definir Objectivo';
        return;
    }

    tituloForm.textContent = 'Editar Objectivo';

    var pesoObj   = parseFloat(objetivo.peso_objetivo);
    var dataObj   = objetivo.data_objetivo;
    var dataIni   = objetivo.data_ini_objetiv || null;
    var hoje      = new Date();
    hoje.setHours(0, 0, 0, 0);
    var dataAlvo  = new Date(dataObj + 'T00:00:00');
    var diasTotal = Math.round((dataAlvo - hoje) / 86400000);

    var html = '<div class="objetivo-painel"><h3>Objectivo Actual</h3>';

    if (diasTotal <= 0) {
        html += '<div class="callout" style="margin-bottom:var(--s5); background:#fef2f2; border-color:#fca5a5; color:#991b1b;">' +
                'A data objectivo já passou. Por favor actualize o seu objectivo.' +
                '</div>';
    }

    html += '<div class="stats-grid">';

    html += '<div class="stat-card">' +
            '<div class="stat-label">Peso Objectivo</div>' +
            '<div class="stat-valor">' + pesoObj.toFixed(2) + '</div>' +
            '<div class="stat-unidade">kg</div>' +
            '</div>';

    if (dataIni) {
        html += '<div class="stat-card">' +
                '<div class="stat-label">Data Início</div>' +
                '<div class="stat-valor" style="font-size:1.1rem;">' + formatarData(dataIni) + '</div>' +
                '<div class="stat-unidade">&nbsp;</div>' +
                '</div>';
    }

    html += '<div class="stat-card">' +
            '<div class="stat-label">Data Fim</div>' +
            '<div class="stat-valor" style="font-size:1.1rem;">' + formatarData(dataObj) + '</div>' +
            '<div class="stat-unidade">&nbsp;</div>' +
            '</div>';

    if (diasTotal > 0) {
        html += '<div class="stat-card">' +
                '<div class="stat-label">Dias Restantes</div>' +
                '<div class="stat-valor">' + diasTotal + '</div>' +
                '<div class="stat-unidade">dias</div>' +
                '</div>';
    }

    if (pesoAtual !== null && diasTotal > 0) {
        var diff      = pesoAtual - pesoObj;
        var taxaDia   = diff / diasTotal;
        var taxaSem   = taxaDia * 7;
        var taxaDiaG  = Math.round(taxaDia * 1000);
        var taxaSemG  = Math.round(taxaSem * 1000);

        html += '<div class="stat-card">' +
                '<div class="stat-label">Peso Actual</div>' +
                '<div class="stat-valor">' + pesoAtual.toFixed(2) + '</div>' +
                '<div class="stat-unidade">kg</div>' +
                '</div>';

        if (diff > 0) {
            html += '<div class="stat-card">' +
                    '<div class="stat-label">A Perder</div>' +
                    '<div class="stat-valor">' + diff.toFixed(2) + '</div>' +
                    '<div class="stat-unidade">kg no total</div>' +
                    '</div>';

            html += '<div class="stat-card">' +
                    '<div class="stat-label">Taxa Diária</div>' +
                    '<div class="stat-valor">' + taxaDiaG + '</div>' +
                    '<div class="stat-unidade">g / dia</div>' +
                    '</div>';

            html += '<div class="stat-card">' +
                    '<div class="stat-label">Taxa Semanal</div>' +
                    '<div class="stat-valor">' + taxaSemG + '</div>' +
                    '<div class="stat-unidade">g / semana</div>' +
                    '</div>';
        } else if (diff < 0) {
            var ganho = Math.abs(diff);
            html += '<div class="stat-card">' +
                    '<div class="stat-label">A Ganhar</div>' +
                    '<div class="stat-valor" style="color:#dc2626;">' + ganho.toFixed(2) + '</div>' +
                    '<div class="stat-unidade">kg no total</div>' +
                    '</div>';
        }

        html += '</div>';

        if (diff > 0) {
            /* Verificar viabilidade com base no período total do objectivo (ini → fim) */
            var avisoHtml = '';
            if (dataIni && medicoesTodas.length > 0) {
                var pesoIniObj      = pesoNaData(medicoesTodas, dataIni);
                var dIniObj         = new Date(dataIni + 'T00:00:00');
                var diasObj         = Math.round((dataAlvo - dIniObj) / 86400000);
                var perdaTotal      = Math.round((pesoIniObj - pesoObj) * 100) / 100;
                var maxPerda        = Math.round(diasObj * 10) / 100;

                if (perdaTotal > 0 && perdaTotal > maxPerda) {
                    var pesoAjust       = Math.round((pesoIniObj - maxPerda) * 100) / 100;
                    var diasNec         = Math.ceil(Math.round(perdaTotal * 100) / 10);
                    var dNovaFim        = new Date(dIniObj);
                    dNovaFim.setDate(dNovaFim.getDate() + diasNec);
                    var novaDataFim     = dateToISO(dNovaFim);

                    _gestaoAviso = { dataFim: dataObj, pesoAjust: pesoAjust,
                                     novaDataFim: novaDataFim, pesoFim: pesoObj, dataIni: dataIni };

                    avisoHtml =
                        '<div style="margin-top:var(--s4); background:rgba(234,179,8,.07); border:1px solid rgba(234,179,8,.35); border-radius:var(--r); padding:var(--s4) var(--s5);">' +
                        '<p style="font-size:.82rem; font-weight:700; color:#92400e; margin-bottom:var(--s2);">&#9888; Objectivo não atingível ao ritmo de 100 g/dia</p>' +
                        '<p style="font-size:.8rem; color:var(--texto-2); line-height:1.6; margin-bottom:var(--s4);">' +
                        'Ao ritmo de 100 g/dia, em <strong>' + diasObj + ' dias</strong> a perda máxima é ' +
                        '<strong>' + maxPerda.toFixed(2).replace('.', ',') + ' kg</strong>. ' +
                        'Para perder <strong>' + perdaTotal.toFixed(2).replace('.', ',') + ' kg</strong> ' +
                        'são necessários <strong>' + diasNec + ' dias</strong>.' +
                        '</p>' +
                        '<div style="display:flex; gap:var(--s3); flex-wrap:wrap;">' +
                        '<button type="button" onclick="ajustarGestaoObjetivo(1)" style="font-size:.78rem; font-family:inherit; font-weight:600; padding:var(--s2) var(--s4); border-radius:var(--r); border:1px solid var(--verde-border); cursor:pointer; background:transparent; color:var(--verde); transition:background .15s; text-align:left;">' +
                        'Manter ' + formatarData(dataObj) + ' — ajustar peso para ' + pesoAjust.toFixed(2).replace('.', ',') + ' kg' +
                        '</button>' +
                        '<button type="button" onclick="ajustarGestaoObjetivo(2)" style="font-size:.78rem; font-family:inherit; font-weight:600; padding:var(--s2) var(--s4); border-radius:var(--r); border:1px solid rgba(37,99,235,.3); cursor:pointer; background:transparent; color:#2563eb; transition:background .15s; text-align:left;">' +
                        'Manter ' + pesoObj.toFixed(2).replace('.', ',') + ' kg — ajustar data para ' + formatarData(novaDataFim) +
                        '</button>' +
                        '</div></div>';
                } else {
                    _gestaoAviso = null;
                    avisoHtml =
                        '<div class="callout" style="margin-top:var(--s4);">' +
                        'Ao ritmo de <strong>' + taxaDiaG + ' g/dia</strong>, ' +
                        'atingirá <strong>' + pesoObj.toFixed(2) + ' kg</strong> ' +
                        'em <strong>' + formatarData(dataObj) + '</strong>.' +
                        '</div>';
                }
            } else {
                _gestaoAviso = null;
                avisoHtml =
                    '<div class="callout" style="margin-top:var(--s4);">' +
                    'Ao ritmo de <strong>' + taxaDiaG + ' g/dia</strong>, ' +
                    'atingirá <strong>' + pesoObj.toFixed(2) + ' kg</strong> ' +
                    'em <strong>' + formatarData(dataObj) + '</strong>.' +
                    '</div>';
            }
            html += avisoHtml;
        }
    } else {
        html += '</div>';
        if (pesoAtual === null) {
            html += '<p style="color:var(--texto-3); font-size:.9rem; margin-top:var(--s4);">Insira pelo menos uma medição de peso para ver as projecções.</p>';
        }
    }

    html += '</div>';
    area.innerHTML = html;

    document.getElementById('campoPeso').value    = pesoObj.toFixed(2);
    document.getElementById('campoDataIni').value = dataIni || '';
    document.getElementById('campoData').value    = dataObj;
}

async function guardarObjetivo(e) {
    e.preventDefault();
    var pesoEl    = document.getElementById('campoPeso');
    var dataIniEl = document.getElementById('campoDataIni');
    var dataEl    = document.getElementById('campoData');
    var btnEl     = document.getElementById('btnGuardar');
    var msgOk     = document.getElementById('msgSucesso');
    var msgErr    = document.getElementById('msgErro');

    msgOk.style.display  = 'none';
    msgErr.style.display = 'none';

    var peso    = parseFloat(pesoEl.value);
    var dataIni = dataIniEl.value;
    var data    = dataEl.value;

    if (isNaN(peso) || peso <= 0 || peso > 999.99) {
        msgErr.textContent   = 'Introduza um peso válido (entre 1 e 999.99 kg).';
        msgErr.style.display = 'block';
        pesoEl.focus();
        return;
    }

    if (!dataIni) {
        msgErr.textContent   = 'Seleccione a data início do objectivo.';
        msgErr.style.display = 'block';
        dataIniEl.focus();
        return;
    }

    if (!data) {
        msgErr.textContent   = 'Seleccione a data fim do objectivo.';
        msgErr.style.display = 'block';
        dataEl.focus();
        return;
    }

    var dataAlvo = new Date(data + 'T00:00:00');
    var hoje     = new Date();
    hoje.setHours(0, 0, 0, 0);
    if (dataAlvo <= hoje) {
        msgErr.textContent   = 'A data fim objectivo tem de ser uma data futura.';
        msgErr.style.display = 'block';
        dataEl.focus();
        return;
    }

    var dataIniObj = new Date(dataIni + 'T00:00:00');
    if (dataIniObj > dataAlvo) {
        msgErr.textContent   = 'A data início não pode ser depois da data fim.';
        msgErr.style.display = 'block';
        dataIniEl.focus();
        return;
    }

    btnEl.disabled    = true;
    btnEl.textContent = 'A guardar…';

    try {
        var resp  = await fetch('/dev/api/medepeso_objetivo_guardar.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ peso_objetivo: peso, data_objetivo: data, data_ini_objetiv: dataIni })
        });
        var dados = await resp.json();

        if (resp.status === 401) {
            msgErr.textContent   = 'Sessão expirada. Por favor faça login novamente.';
            msgErr.style.display = 'block';
        } else if (resp.status === 422 && dados.erro === 'data_passado') {
            msgErr.textContent   = 'A data fim objectivo tem de ser uma data futura.';
            msgErr.style.display = 'block';
        } else if (resp.status === 422 && dados.erro === 'data_ini_apos_fim') {
            msgErr.textContent   = 'A data início não pode ser depois da data fim.';
            msgErr.style.display = 'block';
        } else if (!dados.sucesso) {
            msgErr.textContent   = 'Erro ao guardar. Tente novamente.';
            msgErr.style.display = 'block';
        } else {
            msgOk.style.display = 'block';
            mostrarObjetivo({ peso_objetivo: peso, data_objetivo: data, data_ini_objetiv: dataIni });
        }
    } catch (err) {
        msgErr.textContent   = 'Erro de ligação. Tente novamente.';
        msgErr.style.display = 'block';
    }

    btnEl.disabled    = false;
    btnEl.textContent = 'Guardar Objectivo';
}

(function () {
    var amanha = new Date();
    amanha.setDate(amanha.getDate() + 1);
    document.getElementById('campoData').min = amanha.toISOString().split('T')[0];
}());

carregarDados();
</script>
<script src="/assets/loginbar.js"></script>
</body>
</html>
