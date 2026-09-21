<?php
$erros = $erros ?? [];
$tituloPagina = 'Registrar Não Comparecimento';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-md border border-red-200 p-8">

            <h1 class="text-2xl font-bold text-red-900 mb-1">Registrar não comparecimento</h1>
            <p class="text-gray-600 text-sm mb-6">O registro segue para a moderação como uma denúncia contra o trabalhador.</p>

            <div class="mb-6 pb-6 border-b border-gray-200 text-sm space-y-1">
                <p><span class="text-gray-500">Trabalhador:</span> <strong><?= htmlspecialchars($trabalhador->getNome(), ENT_QUOTES, 'UTF-8') ?></strong></p>
                <p><span class="text-gray-500">Vaga:</span> <strong><?= htmlspecialchars($vaga->getTitulo(), ENT_QUOTES, 'UTF-8') ?></strong></p>
                <?php if ($vaga->getDataServico()): ?>
                    <p><span class="text-gray-500">Data do serviço:</span> <?= date('d/m/Y', strtotime($vaga->getDataServico())) ?></p>
                <?php endif; ?>
            </div>

            <?php if (isset($erros['geral'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm mb-6">
                    <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= URL_BASE ?>/denuncia/nao-comparecimento/submit" class="space-y-6">
                <input type="hidden" name="id_interesse" value="<?= $interesse->getIdInteresse() ?>">

                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição <span class="text-gray-500 font-normal">(opcional)</span></label>
                    <textarea
                        id="descricao"
                        name="descricao"
                        rows="4"
                        maxlength="1000"
                        placeholder="Conte o que aconteceu: horário combinado, tentativas de contato..."
                        class="w-full border border-gray-300 rounded px-3 py-2"
                    ><?= htmlspecialchars($_POST['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($erros['descricao'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['descricao'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                        Registrar
                    </button>
                    <a href="<?= URL_BASE ?>/interesse/interessados?id=<?= $vaga->getIdVaga() ?>" class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
