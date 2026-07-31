<?php
$tituloPagina = 'Detalhes da Vaga';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$vaga = $vaga ?? null;
$contratante = $contratante ?? null;
$usuarioLogado = $usuario ?? null;
$jaDemonstrouInteresse = $jaDemonstrouInteresse ?? false;
error_log("já demonstrei interesse: " . ($jaDemonstrouInteresse ? 'true' : 'false')); // Log the value of $jaDemonstrouInteresse
if (!$vaga) {
    header('Location: /vagas');
    exit;
}

$titulo = htmlspecialchars($vaga->getTitulo(), ENT_QUOTES, 'UTF-8');
$descricao = htmlspecialchars($vaga->getDescricao(), ENT_QUOTES, 'UTF-8');

$localizacao = htmlspecialchars(
    $vaga->getLocalizacao() ?? 'Não informado',
    ENT_QUOTES,
    'UTF-8'
);

$remuneracao = $vaga->getRemuneracao() !== null
    ? number_format($vaga->getRemuneracao(), 2, ',', '.')
    : 'Não informado';

$status = htmlspecialchars(
    $vaga->getStatus(),
    ENT_QUOTES,
    'UTF-8'
);


$statusBadge = $status === 'ATIVA'
    ? 'bg-green-100 text-green-800'
    : 'bg-gray-100 text-gray-800';


$isTrabalhador = 
    $usuarioLogado &&
    $usuarioLogado->getTipoUsuario() === 'TRABALHADOR';


$isContratante =
    $usuarioLogado &&
    $usuarioLogado->getTipoUsuario() === 'CONTRATANTE';


$isProprietario =
    $isContratante &&
    $usuarioLogado->getIdUsuario() === $vaga->getIdContratante();

?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="/vagas" class="text-blue-600 hover:text-blue-700 font-medium">Vagas</a></li>
                <li class="text-gray-500">/</li>
                <li class="text-gray-600 font-medium"><?= substr($titulo, 0, 40) ?>...</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Conteúdo Principal (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Status -->
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                        <?= $status === 'ATIVA' ? '✓ Aberta' : 'Encerrada' ?>
                    </span>
                </div>
                <!-- Título -->
                <h1 class="text-3xl font-bold text-gray-900"><?= $titulo ?></h1>

                <!-- Seção: Sobre a Vaga -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Sobre a vaga</h2>
                    <p class="text-gray-700 whitespace-pre-wrap"><?= $descricao ?></p>
                </div>

                <!-- Detalhes com Ícones -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                        <div class="text-center">
                            <svg class="w-6 h-6 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-xs text-gray-600 uppercase font-semibold">Localização</p>
                            <p class="text-gray-900 font-medium"><?= $localizacao ?></p>
                        </div>
                        <div class="text-center">
                            <svg class="w-6 h-6 text-blue-600 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-gray-600 uppercase font-semibold">Remuneração</p>
                            <p class="text-green-600 font-bold text-lg">R$ <?= $remuneracao ?></p>
                        </div>
                        <div class="text-center">
                            <svg class="w-6 h-6 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-gray-600 uppercase font-semibold">Duração</p>
<p class="text-gray-900 font-medium">
    <?= $vaga->getDataLimite() 
        ? date('d/m/Y', strtotime($vaga->getDataLimite()))
        : 'Sem prazo'
    ?>
</p>                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar Direita (1/3) -->
            <div class="lg:col-span-1">
                
                <!-- Card Contratante -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 sticky top-24">
                    
                    <!-- Contratante Info -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                            <?= htmlspecialchars(strtoupper(substr($contratante->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <h3 class="font-bold text-gray-900"><?= htmlspecialchars($contratante->getNome(), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="text-sm text-gray-600">Contratante</p>
                    </div>

                    <!-- Avaliação -->
                    <div class="text-center mb-6 pb-6 border-b border-gray-200">
                        <div class="flex justify-center gap-1 mb-1">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <p class="text-xs text-gray-600">0 avaliações</p>
                    </div>

                    <!-- Ação Principal -->
                    <?php if ($isTrabalhador && $status === 'ATIVA' && !$jaDemonstrouInteresse): ?>

                        <form method="POST" action="/interesse/demonstrar" class="mb-4">

                            <input type="hidden" 
                                name="id_vaga" 
                                value="<?= $vaga->getIdVaga() ?>">

                            <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Demonstrar Interesse
                            </button>

                        </form>


                    <?php elseif ($jaDemonstrouInteresse): ?>

                        <div class="w-full bg-green-50 border border-green-200 text-green-700 font-semibold py-2 px-4 rounded-lg text-center mb-4">
                            ✓ Você já demonstrou interesse
                        </div>


                    <?php elseif ($isTrabalhador && $status !== 'ATIVA'): ?>

                        <div class="w-full bg-gray-100 border border-gray-200 text-gray-600 font-semibold py-2 px-4 rounded-lg text-center mb-4">
                            Vaga encerrada
                        </div>

                    <?php endif; ?>

                    <!-- Botões Contratante/Admin -->
                    <?php if ($isProprietario): ?>
                        <div class="space-y-2">
                            <a href="/vagas/editar?id=<?= $vaga->getIdVaga() ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors duration-200 block">
                                Editar
                            </a>
                            <form method="POST" action="/vagas/excluir" class="block">
                                <input type="hidden" name="id_vaga" value="<?= $vaga->getIdVaga() ?>">
                                <button type="submit" onclick="return confirm('Tem certeza? Esta ação não pode ser desfeita.')" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($usuarioLogado && !$isProprietario): ?>
                        <div class="pt-4 border-t border-gray-200 mt-4">
                            <a href="/denuncia/criar?id=<?= $vaga->getIdContratante() ?>" class="w-full bg-white border border-red-200 hover:bg-red-50 text-red-600 font-semibold py-2 px-4 rounded-lg text-center transition-colors duration-200 block">
                                Denunciar contratante
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Link Ver Interessados -->
                    <?php if ($isProprietario): ?>

                        <div class="pt-4 border-t border-gray-200 mt-4">

                            <a href="/interesse/interessados?id=<?= $vaga->getIdVaga() ?>"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">

                                Ver interessados →

                            </a>

                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
