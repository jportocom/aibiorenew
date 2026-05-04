<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
$loggedIn = !empty($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medições de Peso — Porto Metabolic Health</title>
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
                <li><a href="/dev/medepeso/inserir.php">Inserir Medição</a></li>
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
    <span class="eyebrow">Gestão de Peso</span>
    <h1>Medições de Peso</h1>
    <p class="lead">Registe, consulte, edite ou apague as suas medições de peso.</p>
</div>

<section>
    <div class="wrap">
        <?php if ($loggedIn): ?>
        <div class="grid-cards" style="max-width:860px; margin:0 auto;">
            <a href="/dev/medepeso/inserir.php" class="card" style="text-decoration:none;">
                <div class="card-icon">&#9998;</div>
                <h3>Inserir Medição</h3>
                <p>Registar o peso de hoje com a data actual do sistema.</p>
            </a>
            <a href="/dev/medepeso/editar.php" class="card" style="text-decoration:none;">
                <div class="card-icon">&#9999;</div>
                <h3>Editar Medição</h3>
                <p>Corrigir o valor de uma medição já registada.</p>
            </a>
            <a href="/dev/medepeso/apagar.php" class="card" style="text-decoration:none;">
                <div class="card-icon">&#128465;</div>
                <h3>Apagar Medição</h3>
                <p>Eliminar definitivamente uma medição do histórico.</p>
            </a>
            <a href="/dev/medepeso/listar.php" class="card" style="text-decoration:none;">
                <div class="card-icon">&#128202;</div>
                <h3>Listar Medições</h3>
                <p>Consultar o histórico completo de medições por ordem cronológica.</p>
            </a>
            <a href="/dev/medepeso/gestaodaapp.php" class="card" style="text-decoration:none;">
                <div class="card-icon">&#127919;</div>
                <h3>Gestão de App</h3>
                <p>Definir o peso objectivo e a data alvo, e acompanhar a projecção de perda.</p>
            </a>
        </div>
        <?php else: ?>
        <div style="max-width:520px; margin:var(--s9) auto; text-align:center;">
            <div style="font-size:2.5rem; margin-bottom:var(--s5);">&#128274;</div>
            <h2 style="font-size:1.4rem; margin-bottom:var(--s4); color:var(--texto);">Acesso restrito</h2>
            <p style="color:var(--texto-2); line-height:1.7;">Para aceder à Gestão de Peso precisa de efectuar o login.</p>
            <p style="margin-top:var(--s4); font-size:.875rem; color:var(--texto-3);">Use o formulário de login no canto superior direito da barra de navegação.</p>
        </div>
        <?php endif; ?>
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

</script>
<script src="/assets/loginbar.js"></script>
</body>
</html>
