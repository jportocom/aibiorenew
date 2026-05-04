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
    <title>Apagar Medição — Porto Metabolic Health</title>
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
        }
        .tabela-medicoes tbody tr:last-child { border-bottom: none; }
        .tabela-medicoes tbody tr:hover { background: var(--fundo); }
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
        }
        .vazio {
            text-align: center;
            padding: var(--s9) var(--s5);
            color: var(--texto-3);
            font-size: .92rem;
        }
        .btn-apagar {
            height: 30px;
            padding: 0 14px;
            font-size: .8rem;
            font-weight: 600;
            background: transparent;
            color: #dc2626;
            border: 1px solid #fca5a5;
            border-radius: var(--r-sm);
            cursor: pointer;
            transition: background var(--t), border-color var(--t);
            font-family: var(--font);
            white-space: nowrap;
        }
        .btn-apagar:hover {
            background: #fef2f2;
            border-color: #dc2626;
        }
        .btn-apagar:disabled { opacity: .5; cursor: not-allowed; }
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
                <li><a href="/dev/medepeso/apagar.php" class="ativo">Apagar Medição</a></li>
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
    <h1>Apagar Medição</h1>
    <p class="lead">Selecione a medição que pretende eliminar. Esta acção não pode ser desfeita.</p>
</div>

<section>
    <div class="wrap">
        <div id="msgGlobal" class="callout" style="display:none; margin-bottom:var(--s5);"></div>
        <div id="areaTabela">
            <div class="vazio">A carregar…</div>
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

async function carregarMedicoes() {
    var area = document.getElementById('areaTabela');
    try {
        var resp  = await fetch('/dev/api/medepeso_listar.php');
        var dados = await resp.json();

        if (resp.status === 401) {
            area.innerHTML = '<div class="vazio">Sessão expirada. <a href="/dev/" style="color:var(--verde)">Faça login novamente</a>.</div>';
            return;
        }
        if (!dados.sucesso) {
            area.innerHTML = '<div class="vazio">Erro ao carregar medições.</div>';
            return;
        }
        if (dados.medicoes.length === 0) {
            area.innerHTML = '<div class="vazio">Não há medições registadas.</div>';
            return;
        }

        var html = '<div class="tabela-wrap"><table class="tabela-medicoes"><thead><tr>'
                 + '<th>#</th><th>Data</th><th>Peso (kg)</th><th></th>'
                 + '</tr></thead><tbody>';

        dados.medicoes.forEach(function(m) {
            html += '<tr>'
                  + '<td style="color:var(--texto-3);font-size:.8rem;">' + m.id + '</td>'
                  + '<td>' + formatarData(m.data) + '</td>'
                  + '<td class="peso-val">' + parseFloat(m.peso).toFixed(2) + ' kg</td>'
                  + '<td><button class="btn-apagar" onclick="confirmarApagar(' + m.id + ',\'' + formatarData(m.data) + '\',' + m.peso + ',this)">Apagar</button></td>'
                  + '</tr>';
        });

        html += '</tbody></table></div>';
        area.innerHTML = html;
    } catch (err) {
        area.innerHTML = '<div class="vazio">Erro de ligação. Recarregue a página.</div>';
    }
}

async function confirmarApagar(id, data, peso, btn) {
    if (!confirm('Apagar a medição de ' + data + ' (' + parseFloat(peso).toFixed(2) + ' kg)?\nEsta acção não pode ser desfeita.')) return;

    var msgGlobal = document.getElementById('msgGlobal');
    msgGlobal.style.display = 'none';
    btn.disabled    = true;
    btn.textContent = 'A apagar…';

    try {
        var resp  = await fetch('/dev/api/medepeso_apagar.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id: id })
        });
        var dados = await resp.json();

        if (resp.status === 401) {
            msgGlobal.textContent   = 'Sessão expirada. Por favor faça login novamente.';
            msgGlobal.style.cssText = 'display:block; background:#fef2f2; border-color:#fca5a5; color:#991b1b;';
            btn.disabled    = false;
            btn.textContent = 'Apagar';
        } else if (!dados.sucesso) {
            msgGlobal.textContent   = 'Erro ao apagar. Tente novamente.';
            msgGlobal.style.cssText = 'display:block; background:#fef2f2; border-color:#fca5a5; color:#991b1b;';
            btn.disabled    = false;
            btn.textContent = 'Apagar';
        } else {
            msgGlobal.textContent   = 'Medição de ' + data + ' apagada com sucesso.';
            msgGlobal.style.cssText = 'display:block;';
            carregarMedicoes();
        }
    } catch (err) {
        msgGlobal.textContent   = 'Erro de ligação. Tente novamente.';
        msgGlobal.style.cssText = 'display:block; background:#fef2f2; border-color:#fca5a5; color:#991b1b;';
        btn.disabled    = false;
        btn.textContent = 'Apagar';
    }
}

carregarMedicoes();
</script>
<script src="/assets/loginbar.js"></script>
</body>
</html>
