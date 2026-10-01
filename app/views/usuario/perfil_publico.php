<?php
$tituloPagina = 'Perfil de ' . $perfil->getNome();
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$habilidades = $habilidades ?? [];
$nome = htmlspecialchars($perfil->getNome(), ENT_QUOTES, 'UTF-8');
$descricao = htmlspecialchars($perfil->getDescricao() ?? '', ENT_QUOTES, 'UTF-8');
?>

<main class="flex-1">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="bg-white rounded-md border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <?php if ($perfil->getFotoUrl()): ?>
                    <img src="<?= htmlspecialchars($perfil->getFotoUrl(), ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= $nome ?>" class="w-16 h-16 rounded-full object-cover">
                <?php else: ?>
                    <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold">
                        <?= htmlspecialchars(strtoupper(mb_substr($perfil->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?= $nome ?></h1>
                    <p class="text-sm text-gray-600">
                        <?= htmlspecialchars($perfil->getRotuloPapeis(), ENT_QUOTES, 'UTF-8') ?>
                        &middot; <?= $perfil->isPessoaJuridica() ? 'Empresa' : 'Pessoa física' ?>
                        &middot; <?= htmlspecialchars($perfil->getLocalizacao() ?? 'Localização não informada', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <?php if ($perfil->isPessoaJuridica() && $perfil->getNomeResponsavel()): ?>
                        <p class="text-sm text-gray-600">Responsável pela execução: <?= htmlspecialchars($perfil->getNomeResponsavel(), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-md border border-gray-100 p-6 mt-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Sobre</h2>
            <p class="text-gray-600 whitespace-pre-wrap"><?= $descricao ?: 'Nenhuma descrição adicionada.' ?></p>
        </div>

        <?php if ($perfil->isTrabalhador() && !empty($habilidades)): ?>
            <div class="bg-white rounded-md border border-gray-100 p-6 mt-6">
                <h2 class="text-lg font-bold text-gray-900 mb-3">Habilidades</h2>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($habilidades as $habilidade): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?= htmlspecialchars($habilidade->getNome(), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <p class="text-xs text-gray-500 mt-6">
            Contato disponível apenas nos fluxos de candidatura já em andamento.
        </p>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
