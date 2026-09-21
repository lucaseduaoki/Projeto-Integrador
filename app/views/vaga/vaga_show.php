<?php
$tituloPagina = 'Detalhes da Vaga';
$vaga = $vaga ?? null;
$contratante = $contratante ?? null;
$usuarioLogado = $usuario ?? null;
$jaDemonstrouInteresse = $jaDemonstrouInteresse ?? false;
if (!$vaga) {
    header('Location: ' . URL_BASE . '/vagas');
    exit;
}

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

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


$disponivel = $vaga->estaDisponivel();

$statusBadge = $disponivel
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
                <li><a href="<?= URL_BASE ?>/vagas" class="text-blue-600 hover:text-blue-700 font-medium">Vagas</a></li>
                <li class="text-gray-500">/</li>
                <li class="text-gray-600 font-medium"><?= mb_strimwidth($titulo, 0, 40, "…") ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Conteúdo Principal (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Status -->
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadge ?>">
                        <?= $disponivel ? 'Aberta' : ($vaga->isUserActive() ? 'Encerrada' : 'Indisponível') ?>
                    </span>
                </div>
                <!-- Título -->
                <h1 class="text-3xl font-bold text-gray-900"><?= $titulo ?></h1>

                <!-- Seção: Sobre a Vaga -->
                <div class="bg-white rounded-md border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Sobre a vaga</h2>
                    <p class="text-gray-700 whitespace-pre-wrap"><?= $descricao ?></p>
                </div>

                <!-- Detalhes -->
                <div class="bg-white rounded-md border border-gray-100 p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                        <div>
                            <p class="text-xs text-gray-600 uppercase font-semibold">Localização</p>
                            <p class="text-gray-900 font-medium"><?= $localizacao ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 uppercase font-semibold">Remuneração</p>
                            <p class="text-gray-900 font-semibold">R$ <?= $remuneracao ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 uppercase font-semibold">Vagas preenchidas</p>
                            <p class="text-gray-900 font-medium"><?= $vaga->getTotalAceitos() ?> de <?= $vaga->getTrabalhadoresLimite() ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 uppercase font-semibold">Prazo</p>
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
                <div class="bg-white rounded-md border border-gray-100 p-6 mb-6 sticky top-24">
                    
                    <!-- Contratante Info -->
                    <div class="text-center mb-6 pb-6 border-b border-gray-200">
                        <div class="w-16 h-16 rounded bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                            <?= htmlspecialchars(strtoupper(substr($contratante->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <h3 class="font-bold text-gray-900"><?= htmlspecialchars($contratante->getNome(), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="text-sm text-gray-600">Contratante</p>
                    </div>

                    <!-- Ação Principal -->
                    <?php if ($isTrabalhador && $disponivel && !$jaDemonstrouInteresse): ?>

                        <form method="POST" action="<?= URL_BASE ?>/interesse/demonstrar" class="mb-4">

                            <input type="hidden" 
                                name="id_vaga" 
                                value="<?= $vaga->getIdVaga() ?>">

                            <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                                Demonstrar Interesse
                            </button>

                        </form>


                    <?php elseif ($jaDemonstrouInteresse): ?>

                        <div class="w-full bg-green-50 border border-green-200 text-green-700 font-semibold py-2 px-4 rounded text-center mb-4">
                            Você já demonstrou interesse
                        </div>


                    <?php elseif ($isTrabalhador && !$disponivel): ?>

                        <div class="w-full bg-gray-100 border border-gray-200 text-gray-600 font-semibold py-2 px-4 rounded text-center mb-4">
                            <?= $vaga->isUserActive() ? 'Vaga encerrada' : 'Vaga indisponível' ?>
                        </div>

                    <?php endif; ?>

                    <!-- Botões Contratante/Admin -->
                    <?php if ($isProprietario): ?>
                        <div class="space-y-2">
                            <a href="<?= URL_BASE ?>/vagas/editar?id=<?= $vaga->getIdVaga() ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded text-center transition-colors duration-200 block">
                                Editar
                            </a>
                            <form method="POST" action="<?= URL_BASE ?>/vagas/excluir" class="block">
                                <input type="hidden" name="id" value="<?= $vaga->getIdVaga() ?>">
                                <button type="submit" onclick="return confirm('Tem certeza? Esta ação não pode ser desfeita.')" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($usuarioLogado && !$isProprietario): ?>
                        <div class="pt-4 border-t border-gray-200 mt-4">
                            <a href="<?= URL_BASE ?>/denuncia/criar?id=<?= $vaga->getIdContratante() ?>" class="w-full bg-white border border-red-200 hover:bg-red-50 text-red-600 font-semibold py-2 px-4 rounded text-center transition-colors duration-200 block">
                                Denunciar contratante
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Link Ver Interessados -->
                    <?php if ($isProprietario): ?>

                        <div class="pt-4 border-t border-gray-200 mt-4">

                            <a href="<?= URL_BASE ?>/interesse/interessados?id=<?= $vaga->getIdVaga() ?>"
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
