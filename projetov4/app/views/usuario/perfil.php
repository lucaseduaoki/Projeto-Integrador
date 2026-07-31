<?php
$tituloPagina = 'Meu Perfil';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$usuario = $usuario ?? null;
$habilidades = $habilidades ?? [];
$avaliacoes = $avaliacoes ?? [];
if (!$usuario) {
    header('Location: /login');
    exit;
}

$nomeUsuario = htmlspecialchars($usuario->getNome(), ENT_QUOTES, 'UTF-8');
$tipoUsuario = htmlspecialchars($usuario->getTipoUsuario(), ENT_QUOTES, 'UTF-8');
$descricao = htmlspecialchars($usuario->getDescricao() ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($usuario->getEmail(), ENT_QUOTES, 'UTF-8');
$telefone = htmlspecialchars($usuario->getTelefone() ?? 'Não informado', ENT_QUOTES, 'UTF-8');
$inicialNome = strtoupper(substr($usuario->getNome(), 0, 1));
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Coluna Esquerda - Card de Perfil -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    
                    <!-- Avatar -->
                    <div class="flex flex-col items-center">
                        <div class="w-24 h-24 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold mb-4">
                            <?= htmlspecialchars($inicialNome, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        
                        <!-- Nome e Tipo -->
                        <h2 class="text-xl font-bold text-gray-900"><?= $nomeUsuario ?></h2>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?= $tipoUsuario === 'TRABALHADOR' ? 'Trabalhador' : ($tipoUsuario === 'CONTRATANTE' ? 'Contratante' : 'Admin') ?>
                        </span>
                        
                        <!-- Informações de Contato -->
                        <div class="w-full mt-6 pt-6 border-t border-gray-200 space-y-3">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span><?= $email ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span><?= $telefone ?></span>
                            </div>
                        </div>
                        
                        <!-- Botão Editar -->
                        <a href="/perfil/editar" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors duration-200">
                            Editar Perfil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita - Conteúdo -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Sobre Mim -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Sobre mim</h3>
                    <p class="text-gray-600 whitespace-pre-wrap">
                        <?= $descricao ?: 'Nenhuma descrição adicionada ainda.' ?>
                    </p>
                </div>

                <!-- Habilidades (apenas trabalhadores) -->
                <?php if ($usuario->isTrabalhador() && !empty($habilidades)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Habilidades</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($habilidades as $habilidade): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($habilidade->getNome(), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
