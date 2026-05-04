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
    <title>Inserir Medição — Porto Metabolic Health</title>
    <link rel="stylesheet" href="/assets/style.css">
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
                <li><a href="/dev/medepeso/inserir.php" class="ativo">Inserir Medição</a></li>
                <li><a href="/dev/medepeso/editar.php">Editar Medição</a></li>
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
    <span class="eyebrow">Medições de Peso</span>
    <h1>Inserir Medição</h1>
    <p class="lead">Registe o seu peso de hoje. A data é preenchida automaticamente pelo sistema.</p>
</div>

<section>
    <div class="wrap">
        <div style="max-width:480px; margin:0 auto;">
            <div id="msgSucesso" class="callout" style="display:none; margin-bottom:var(--s5);">
                Medição guardada com sucesso.
            </div>
            <div id="msgErro" class="callout" style="display:none; margin-bottom:var(--s5); background:#fef2f2; border-color:#fca5a5; color:#991b1b;">
            </div>
            <form id="formInserir" onsubmit="guardarMedicao(event)">
                <div class="form-group">
                    <label for="campoPeso">Peso (kg)</label>
                    <input type="number" id="campoPeso" name="peso"
                           step="0.01" min="1" max="999.99"
                           placeholder="Ex: 78.50"
                           autocomplete="off"
                           required>
                </div>
                <button type="submit" class="btn btn-primary btn-full" id="btnGuardar">
                    Guardar
                </button>
            </form>
            <p style="margin-top:var(--s4); text-align:center;">
                <a href="/dev/medepeso/listar.php" style="color:var(--verde); font-size:.875rem;">Ver todas as medições &rarr;</a>
            </p>
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


async function guardarMedicao(e) {
    e.preventDefault();
    var pesoEl  = document.getElementById('campoPeso');
    var btnEl   = document.getElementById('btnGuardar');
    var msgOk   = document.getElementById('msgSucesso');
    var msgErr  = document.getElementById('msgErro');
    var peso    = parseFloat(pesoEl.value);

    msgOk.style.display  = 'none';
    msgErr.style.display = 'none';

    if (isNaN(peso) || peso <= 0 || peso > 999.99) {
        msgErr.textContent   = 'Introduza um peso válido (entre 1 e 999.99 kg).';
        msgErr.style.display = 'block';
        pesoEl.focus();
        return;
    }

    btnEl.disabled    = true;
    btnEl.textContent = 'A guardar…';

    try {
        var resp  = await fetch('/dev/api/medepeso_inserir.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ peso: peso })
        });
        var dados = await resp.json();

        if (resp.status === 401) {
            msgErr.textContent   = 'Sessão expirada. Por favor faça login novamente.';
            msgErr.style.display = 'block';
        } else if (!dados.sucesso) {
            msgErr.textContent   = 'Erro ao guardar. Tente novamente.';
            msgErr.style.display = 'block';
        } else {
            msgOk.style.display = 'block';
            pesoEl.value = '';
            pesoEl.focus();
        }
    } catch (err) {
        msgErr.textContent   = 'Erro de ligação. Tente novamente.';
        msgErr.style.display = 'block';
    }

    btnEl.disabled    = false;
    btnEl.textContent = 'Guardar';
}
</script>
<script src="/assets/loginbar.js"></script>
</body>
</html>
