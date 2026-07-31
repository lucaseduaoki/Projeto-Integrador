<?php
$tituloPagina = 'Candidatura Confirmada';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$candidatura = $candidatura ?? null;
$anuncio = $anuncio ?? null;
$usuarioLogado = $usuarioLogado ?? null;

$tituloAnuncio = $anuncio ? $anuncio->getTitulo() : 'A vaga selecionada';
$localizacao = $anuncio ? ($anuncio->getLocalizacao() ?? 'Não informada') : 'Não informada';
$remuneracao = $anuncio ? 'R$ ' . number_format((float)$anuncio->getRemuneracao(), 2, ',', '.') : 'Não informada';
$tipoServico = $anuncio ? ($anuncio->getTipoServico() === 'TEMPORARIO' ? 'Temporário' : 'Fixo') : 'Não informado';
$statusCandidatura = $candidatura ? ucfirst(strtolower($candidatura->getStatus())) : 'Confirmada';
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-3xl">
        <div class="bg-white rounded-2xl shadow-sm border border-green-100 overflow-hidden">
            <div class="bg-green-50 px-6 py-5 border-b border-green-100">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-full bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-green-900">Candidatura confirmada</h1>
                        <p class="text-green-800 mt-1">Sua confirmação foi registrada com sucesso.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Vaga</p>
                        <p class="mt-1 text-lg font-bold text-gray-900"><?= htmlspecialchars($tituloAnuncio, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Status</p>
                        <p class="mt-1 text-lg font-bold text-green-600"><?= htmlspecialchars($statusCandidatura, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Localização</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900"><?= htmlspecialchars($localizacao, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Tipo / Valor</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900"><?= htmlspecialchars($tipoServico, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($remuneracao, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-900">
                    Você já pode acompanhar essa candidatura no seu histórico ou voltar para explorar novas vagas.
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <a href="/candidatura/historico" class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                        Ir para meu histórico
                    </a>
                    <?php if ($anuncio): ?>
                        <a href="/anuncios/visualizar?id=<?= $anuncio->getId() ?>" class="flex-1 text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                            Ver vaga
                        </a>
                    <?php else: ?>
                        <a href="/anuncios" class="flex-1 text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                            Explorar vagas
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../shared/footer.php'; ?>
