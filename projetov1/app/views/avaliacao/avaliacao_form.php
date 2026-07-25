<?php
$tituloPagina = 'Avaliar Serviço';
include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$avaliado = $avaliado ?? null;
$anuncio = $anuncio ?? null;
$erros = $erros ?? [];

if (!$avaliado || !$anuncio) {
    header('Location: /anuncios');
    exit;
}
?>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Avaliar Serviço</h1>
                <p class="text-gray-600">Sua opinião ajuda a manter a comunidade confiável</p>
            </div>

            <!-- Avatar e Nome do Avaliado -->
            <div class="text-center mb-8 pb-8 border-b border-gray-200">
                <div class="w-20 h-20 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold mx-auto mb-3">
                    <?= htmlspecialchars(strtoupper(substr($avaliado->getNome(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h2 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($avaliado->getNome(), ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-sm text-gray-600 mt-1">Vaga: <?= htmlspecialchars($anuncio->getTitulo(), ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <!-- Erro Geral -->
            <?php if (isset($erros['geral'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/avaliacao/criar/submit" class="space-y-6">
                
                <input type="hidden" name="id_avaliado" value="<?= $avaliado->getId() ?>">
                <input type="hidden" name="id_anuncio" value="<?= $anuncio->getId() ?>">

                <!-- Nota (Estrelas) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Sua nota</label>
                    <div class="flex justify-center gap-2 mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button 
                                type="button"
                                onclick="selecionarNota(<?= $i ?>)"
                                class="star-btn group p-1 transition-transform hover:scale-110"
                                data-nota="<?= $i ?>"
                            >
                                <svg class="w-10 h-10 text-gray-300 group-hover:text-yellow-400 transition-colors cursor-pointer" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="nota" name="nota" value="">
                    <p class="text-xs text-gray-600 text-center">Clique em uma estrela para avaliar</p>
                    <?php if (isset($erros['nota'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['nota'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Comentário -->
                <div>
                    <label for="comentario" class="block text-sm font-medium text-gray-700 mb-1">Comentário (obrigatório)</label>
                    <textarea 
                        id="comentario" 
                        name="comentario" 
                        required
                        rows="5"
                        maxlength="500"
                        onkeyup="atualizarContador()"
                        placeholder="Conte sua experiência. O que achaste do trabalho realizado?"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($erros['comentario']) ? 'border-red-500 focus:ring-red-500' : '' ?>"
                    ></textarea>
                    <div class="flex justify-between mt-2">
                        <p class="text-xs text-gray-600">Mínimo 10 caracteres</p>
                        <p class="text-xs text-gray-600"><span id="contador">0</span> / 500</p>
                    </div>
                    <?php if (isset($erros['comentario'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['comentario'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Botões -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button 
                        type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                        Enviar avaliação
                    </button>
                    <a 
                        href="/candidatura/historico"
                        class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg text-center transition-colors duration-200"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function selecionarNota(nota) {
    document.getElementById('nota').value = nota;
    
    // Atualizar visual das estrelas
    document.querySelectorAll('.star-btn').forEach((btn, index) => {
        const svg = btn.querySelector('svg');
        if (index < nota) {
            svg.classList.remove('text-gray-300');
            svg.classList.add('text-yellow-400');
        } else {
            svg.classList.remove('text-yellow-400');
            svg.classList.add('text-gray-300');
        }
    });
}

function atualizarContador() {
    const comentario = document.getElementById('comentario').value;
    document.getElementById('contador').textContent = comentario.length;
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
