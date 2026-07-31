<?php
$tituloPagina = isset($modoEdicao) && $modoEdicao ? 'Editar Vaga' : 'Publicar Nova Vaga';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$vaga = $vaga ?? null;
$erros = $erros ?? [];
$modoEdicao = $modoEdicao ?? false;
?>

<main class="flex-1">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">

            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <?= $modoEdicao ? 'Editar Vaga' : 'Publicar Nova Vaga' ?>
            </h1>


            <?php if (isset($erros['geral'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                    <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>


            <form method="POST"
                  action="<?= $modoEdicao ? '/vagas/editar/submit' : '/vagas/criar/submit' ?>"
                  class="space-y-6">


                <?php if ($modoEdicao): ?>
                    <input 
                        type="hidden"
                        name="id_vaga"
                        value="<?= $vaga->getIdVaga() ?>"
                    >
                <?php endif; ?>


                <!-- Categoria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Categoria
                    </label>

<select
    id="id_categoria"
    name="id_categoria"
    required
    class="w-full border border-gray-300 rounded-lg px-3 py-2"
>

    <option value="">
        -- Selecionar --
    </option>

    <option value="1" <?= $vaga && $vaga->getIdCategoria() == 1 ? 'selected' : '' ?>>
        Faxina
    </option>

    <option value="2" <?= $vaga && $vaga->getIdCategoria() == 2 ? 'selected' : '' ?>>
        Garçom
    </option>

    <option value="3" <?= $vaga && $vaga->getIdCategoria() == 3 ? 'selected' : '' ?>>
        Construção Civil
    </option>

    <option value="4" <?= $vaga && $vaga->getIdCategoria() == 4 ? 'selected' : '' ?>>
        Jardinagem
    </option>

    <option value="5" <?= $vaga && $vaga->getIdCategoria() == 5 ? 'selected' : '' ?>>
        Entregas
    </option>

    <option value="6" <?= $vaga && $vaga->getIdCategoria() == 6 ? 'selected' : '' ?>>
        Tecnologia
    </option>

    <option value="7" <?= $vaga && $vaga->getIdCategoria() == 7 ? 'selected' : '' ?>>
        Eventos
    </option>

    <option value="8" <?= $vaga && $vaga->getIdCategoria() == 8 ? 'selected' : '' ?>>
        Atendimento
    </option>

    <option value="9" <?= $vaga && $vaga->getIdCategoria() == 9 ? 'selected' : '' ?>>
        Manutenção
    </option>

    <option value="10" <?= $vaga && $vaga->getIdCategoria() == 10 ? 'selected' : '' ?>>
        Outros
    </option>

</select>

                </div>



                <!-- Título -->
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Título da vaga
                    </label>

                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        required
                        value="<?= $vaga ? htmlspecialchars($vaga->getTitulo(), ENT_QUOTES, 'UTF-8') : '' ?>"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2"
                    >

                </div>



                <!-- Descrição -->
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        required
                        rows="5"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2"
                    ><?= $vaga ? htmlspecialchars($vaga->getDescricao(), ENT_QUOTES, 'UTF-8') : '' ?></textarea>

                </div>



                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">


                    <!-- Localização -->
                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Localização
                        </label>

                        <input
                            type="text"
                            id="localizacao"
                            name="localizacao"
                            required
                            value="<?= $vaga ? htmlspecialchars($vaga->getLocalizacao(), ENT_QUOTES, 'UTF-8') : '' ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >

                    </div>



                    <!-- Remuneração -->
                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Remuneração (R$)
                        </label>

                        <input
                            type="number"
                            id="remuneracao"
                            name="remuneracao"
                            required
                            step="0.01"
                            min="0"
                            value="<?= $vaga ? number_format($vaga->getRemuneracao(),2,'.','') : '' ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >

                    </div>



                    <!-- Data limite -->
                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Prazo para candidatura
                        </label>

                        <input
                            type="date"
                            id="data_limite"
                            name="data_limite"
                            value="<?= $vaga ? htmlspecialchars($vaga->getDataLimite(), ENT_QUOTES, 'UTF-8') : '' ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >

                    </div>



                    <!-- Quantidade -->
                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Quantidade de trabalhadores
                        </label>

                        <input
                            type="number"
                            id="trabalhadores_limite"
                            name="trabalhadores_limite"
                            min="1"
                            value="<?= $vaga ? $vaga->getTrabalhadoresLimite() : 1 ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        >

                    </div>


                </div>



                <div class="flex gap-4 pt-6 border-t border-gray-200">


                    <!-- Botão teste -->
                    <button
                        type="button"
                        onclick="preencherFormularioTeste()"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg"
                    >
                        Preencher teste
                    </button>


                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg"
                    >
                        <?= $modoEdicao ? 'Salvar alterações' : 'Publicar vaga' ?>
                    </button>


                    <a
                        href="/vagas"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-6 rounded-lg"
                    >
                        Cancelar
                    </a>


                </div>


            </form>

        </div>

    </div>
</main>



<script>

function preencherFormularioTeste() {

    document.getElementById('id_categoria').value = '2';

    document.getElementById('titulo').value =
        'Garçom para evento corporativo';

    document.getElementById('descricao').value =
        'Necessário auxiliar no atendimento de convidados durante evento corporativo.';

    document.getElementById('localizacao').value =
        'Foz do Iguaçu - PR';

    document.getElementById('remuneracao').value =
        '250.00';

    document.getElementById('data_limite').value =
        '2026-08-15';

    document.getElementById('trabalhadores_limite').value =
        '2';

}

</script>



<?php include __DIR__ . '/../shared/footer.php'; ?>