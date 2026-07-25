<?php
$tituloPagina = 'Meu Perfil';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$usuario = $usuario ?? null;
$habilidades = $habilidades ?? [];
$avaliacoes = $avaliacoes ?? [];
$mediaAvaliacao = $mediaAvaliacao ?? 0;

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
                        
                        <!-- Avaliação -->
                        <?php if ($usuario->isTrabalhador()): ?>
                            <div class="mt-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <svg class="w-4 h-4 <?= $i < round($mediaAvaliacao) ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-sm text-gray-600 mt-1"><?= count($avaliacoes) ?> avaliações</p>
                            </div>
                        <?php endif; ?>
                        
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

                <!-- Avaliações Recentes -->
                <?php if ($usuario->isTrabalhador() && !empty($avaliacoes)): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Avaliações recentes</h3>
                        <div class="space-y-4">
                            <?php foreach (array_slice($avaliacoes, 0, 5) as $avaliacao): ?>
                                <div class="flex gap-4 pb-4 border-b border-gray-100 last:border-b-0">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <h4 class="font-semibold text-gray-900">
                                                <?= htmlspecialchars($avaliacao->getNomeAvaliador() ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>
                                            </h4>
                                            <div class="flex gap-1">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <svg class="w-3 h-3 <?= $i < $avaliacao->getNota() ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($avaliacao->getComentario() ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($avaliacao->getData(), ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
