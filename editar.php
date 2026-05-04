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
    <title>Editar Medição — Porto Metabolic Health</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .tabela-medicoes {
            width: 100%;
            border-collapse: collapse;
            font-size: .92rem;
        }
        .tabela-medicoes thead th {
            text-align: left;
            padding: var(--s3) var(--s4);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--verde-hover);
            border-bottom: 2px solid var(--verde-border);
            background: var(--verde-muted);
        }
        .tabela-medicoes tbody tr {
            border-bottom: 1px solid var(--borda);
            transition: background var(--t);
            cursor: pointer;
        }
        .tabela-medicoes tbody tr:last-child { border-bottom: none; }
        .tabela-medicoes tbody tr:hover { background: var(--verde-light); }
        .tabela-medicoes tbody tr.selecionada { background: var(--verde-light); outline: 2px solid var(--verde); outline-offset: -2px; }
        .tabela-medicoes tbody td { padding: var(--s3) var(--s4); color: var(--texto); }
        .tabela-medicoes tbody td.peso-val {
            font-weight: 700;
            color: var(--verde);
            font-size: 1rem;
        }
        .tabela-wrap {
            background: var(--branco);
            border: 1px solid var(--borda);
            border-radius: var(--r);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--s6);
        }
        .vazio {
            text-align: center;
            padding: var(--s9) var(--s5);
            color: var(--texto-3);
            font-size: .92rem;
        }
        .painel-edicao {
            background: var(--verde-muted);
            border: 1px solid var(--verde-border);
            border-radius: var(--r);
            padding: var(--s6);
            max-width: 480px;
        }
        .painel-edicao h3 {
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
                <li><a href="/dev/medepeso/editar.php" class="ativo">Editar Medição</a></li>
                <li><a href="/dev/medepeso/apagar.php">Apagar Medição</a></li>
                <li><a href="/dev/medepeso/listar.php">Listar Medições</a></li>
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

<div class="page-hero">
    <span class="eyebrow">Gestão de Peso</span>
    <h1>Editar Medição</h1>
    <p class="lead">Selecione a medição que pretende corrigir e introduza o novo valor.</p>
</div>

<section>
    <div class="wrap">
        <div id="areaTabela">
            <div class="vazio">A carregar…</div>
        </div>
        <div id="painelEdicao" style="display:none">
            <div class="painel-edicao">
                <h3 id="tituloEdicao">Editar medição</h3>
                <div id="msgSucesso" class="callout" style="display:none; margin-bottom:var(--s5);">
                    Medição actualizada com sucesso.
                </div>
                <div id="msgErro" class="callout" style="display:none; margin-bottom:var(--s5); background:#fef2f2; border-color:#fca5a5; color:#991b1b;"></div>
                <form id="formEditar" onsubmit="guardarEdicao(event)">
                    <input type="hidden" id="campoId">
                    <div class="form-group">
                        <label for="campoPeso">Novo peso (kg)</label>
                        <input type="number" id="campoPeso" step="0.01" min="1" max="999.99"
                               placeholder="Ex: 78.50" autocomplete="off" required>
                    </div>
                    <div style="display:flex; gap:var(--s3);">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                        <button type="button" class="btn btn-secondary" onclick="cancelarEdicao()">Cancelar</button>
                    </div>
                </form>
            </div>
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

var medicoes = [];

async function carregarMedicoes() {
    var area = document.getElementById('areaTabela');
    try {
        var resp  = await fetch('/dev/api/medepeso_listar.php');
        var dados = await resp.json();

        if (resp.status === 401) {
            area.innerHTML = '<div class="vazio">Sessão expirada. <a href="/dev/" style="color:var(--verde)">Faça login novamente</a>.</div>';
            return;
        }
        if (!dados.sucesso || dados.medicoes.length === 0) {
            area.innerHTML = '<div class="vazio">Não há medições registadas.</div>';
            return;
        }

        medicoes = dados.medicoes;

        var html = '<p style="margin-bottom:var(--s4);font-size:.9rem;color:var(--texto-2);">Clique numa linha para a editar.</p>'
                 + '<div class="tabela-wrap"><table class="tabela-medicoes"><thead><tr>'
                 + '<th>#</th><th>Data</th><th>Peso (kg)</th>'
                 + '</tr></thead><tbody>';

        dados.medicoes.forEach(function(m) {
            html += '<tr onclick="selecionarMedicao(' + m.id + ',' + m.peso + ',\'' + m.data + '\')">'
                  + '<td style="color:var(--texto-3);font-size:.8rem;">' + m.id + '</td>'
                  + '<td>' + formatarData(m.data) + '</td>'
                  + '<td class="peso-val">' + parseFloat(m.peso).toFixed(2) + ' kg</td>'
                  + '</tr>';
        });

        html += '</tbody></table></div>';
        area.innerHTML = html;
    } catch (err) {
        area.innerHTML = '<div class="vazio">Erro de ligação. Recarregue a página.</div>';
    }
}

function selecionarMedicao(id, peso, data) {
    document.querySelectorAll('.tabela-medicoes tbody tr').forEach(function(tr) {
        tr.classList.remove('selecionada');
    });
    var rows = document.querySelectorAll('.tabela-medicoes tbody tr');
    for (var i = 0; i < rows.length; i++) {
        if (rows[i].innerHTML.indexOf('>' + id + '<') !== -1) {
            rows[i].classList.add('selecionada');
            break;
        }
    }

    document.getElementById('campoId').value  = id;
    document.getElementById('campoPeso').value = parseFloat(peso).toFixed(2);
    document.getElementById('tituloEdicao').textContent = 'Editar medição de ' + formatarData(data);
    document.getElementById('msgSucesso').style.display = 'none';
    document.getElementById('msgErro').style.display    = 'none';
    document.getElementById('painelEdicao').style.display = 'block';
    document.getElementById('campoPeso').focus();
}

function cancelarEdicao() {
    document.getElementById('painelEdicao').style.display = 'none';
    document.querySelectorAll('.tabela-medicoes tbody tr').forEach(function(tr) {
        tr.classList.remove('selecionada');
    });
}

async function guardarEdicao(e) {
    e.preventDefault();
    var id     = parseInt(document.getElementById('campoId').value);
    var peso   = parseFloat(document.getElementById('campoPeso').value);
    var btnEl  = document.getElementById('btnGuardar');
    var msgOk  = document.getElementById('msgSucesso');
    var msgErr = document.getElementById('msgErro');

    msgOk.style.display  = 'none';
    msgErr.style.display = 'none';

    if (isNaN(peso) || peso <= 0 || peso > 999.99) {
        msgErr.textContent   = 'Introduza um peso válido (entre 1 e 999.99 kg).';
        msgErr.style.display = 'block';
        return;
    }

    btnEl.disabled    = true;
    btnEl.textContent = 'A guardar…';

    try {
        var resp  = await fetch('/dev/api/medepeso_editar.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id: id, peso: peso })
        });
        var dados = await resp.json();

        if (resp.status === 401) {
            msgErr.textContent   = 'Sessão expirada. Por favor faça login novamente.';
            msgErr.style.display = 'block';
        } else if (!dados.sucesso) {
            msgErr.textContent   = 'Erro ao actualizar. Tente novamente.';
            msgErr.style.display = 'block';
        } else {
            msgOk.style.display = 'block';
            cancelarEdicao();
            carregarMedicoes();
        }
    } catch (err) {
        msgErr.textContent   = 'Erro de ligação. Tente novamente.';
        msgErr.style.display = 'block';
    }

    btnEl.disabled    = false;
    btnEl.textContent = 'Guardar';
}

carregarMedicoes();
</script>
<script src="/assets/loginbar.js"></script>
</body>
</html>
