<?php
// Verificar se usuário está logado
$usuarioLogado = $_SESSION['usuario_logado'] ?? null;
$isContratante = $usuarioLogado && method_exists($usuarioLogado, 'isContratante') && $usuarioLogado->isContratante();
$isTrabalhador = $usuarioLogado && method_exists($usuarioLogado, 'isTrabalhador') && $usuarioLogado->isTrabalhador();
$isAdmin = $usuarioLogado && method_exists($usuarioLogado, 'isAdmin') && $usuarioLogado->isAdmin();
$nomeUsuario = $usuarioLogado ? htmlspecialchars($usuarioLogado->getNome(), ENT_QUOTES, 'UTF-8') : '';
$inicialNome = $usuarioLogado ? strtoupper(substr($usuarioLogado->getNome(), 0, 1)) : '';
?>

<nav class="fixed top-0 left-0 right-0 bg-white border-b border-gray-100 shadow-sm z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="<?= URL_BASE ?>/anuncios" class="flex items-center gap-2 text-xl font-bold text-blue-600">
                    <!-- Briefcase Icon -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H9V1H5v2zm0 2h10v10H5V5z"/>
                    </svg>
                    FreelaJá
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-8">
                <a href="<?= URL_BASE ?>/anuncios" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Vagas</a>
                <a href="<?= URL_BASE ?>/anuncios/buscar" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Buscar</a>
                
                <?php if ($usuarioLogado && $isContratante): ?>
                    <a href="<?= URL_BASE ?>/anuncios" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Meus Anúncios</a>
                <?php endif; ?>
                
                <?php if ($usuarioLogado && $isTrabalhador): ?>
                    <a href="<?= URL_BASE ?>/candidatura/historico" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Minhas Candidaturas</a>
                <?php endif; ?>
                
                <?php if ($usuarioLogado && $isAdmin): ?>
                    <a href="<?= URL_BASE ?>/admin/denuncias" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Admin</a>
                <?php endif; ?>
            </div>

            <!-- Right Side -->
            <div class="hidden md:flex items-center gap-4">
                <?php if (!$usuarioLogado): ?>
                    <a href="<?= URL_BASE ?>/login" class="text-gray-700 hover:text-blue-600 font-medium">Entrar</a>
                    <a href="<?= URL_BASE ?>/cadastro" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                        Cadastrar
                    </a>
                <?php else: ?>
                    <!-- User Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">
                            <?= htmlspecialchars($inicialNome, ENT_QUOTES, 'UTF-8') ?>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="<?= URL_BASE ?>/perfil" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 border-b border-gray-100 first:rounded-t-lg">
                                Meu Perfil
                            </a>
                            <a href="<?= URL_BASE ?>/logout" class="block px-4 py-2 text-red-600 hover:bg-red-50 last:rounded-b-lg">
                                Sair
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden pb-4 border-t border-gray-100">
            <a href="<?= URL_BASE ?>/anuncios" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Vagas</a>
            <a href="<?= URL_BASE ?>/anuncios/buscar" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Buscar</a>
            
            <?php if ($usuarioLogado && $isContratante): ?>
                <a href="<?= URL_BASE ?>/anuncios" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Meus Anúncios</a>
            <?php endif; ?>
            
            <?php if ($usuarioLogado && $isTrabalhador): ?>
                <a href="<?= URL_BASE ?>/candidatura/historico" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Minhas Candidaturas</a>
            <?php endif; ?>
            
            <?php if ($usuarioLogado && $isAdmin): ?>
                <a href="<?= URL_BASE ?>/admin/denuncias" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Admin</a>
            <?php endif; ?>
            
            <div class="border-t border-gray-100 mt-4 pt-4 px-4">
                <?php if (!$usuarioLogado): ?>
                    <a href="<?= URL_BASE ?>/login" class="block py-2 text-gray-700 hover:text-blue-600">Entrar</a>
                    <a href="<?= URL_BASE ?>/cadastro" class="block mt-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors duration-200">
                        Cadastrar
                    </a>
                <?php else: ?>
                    <p class="font-semibold mb-2"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="<?= URL_BASE ?>/perfil" class="block py-2 text-gray-700 hover:text-blue-600">Meu Perfil</a>
                    <a href="<?= URL_BASE ?>/logout" class="block py-2 text-red-600 hover:text-red-700">Sair</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Espaço para compensar navbar fixa -->
<div class="h-16"></div>

<script>
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    });
</script>
