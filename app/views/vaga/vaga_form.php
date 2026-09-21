<?php
$modoEdicao = $modoEdicao ?? (($acao ?? '') === 'editar');
$tituloPagina = $modoEdicao ? 'Editar Vaga' : 'Publicar Nova Vaga';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$vaga = $vaga ?? null;
$erros = $erros ?? [];
?>

<main class="flex-1">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="bg-white rounded-md border border-gray-100 p-8">

            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <?= $modoEdicao ? 'Editar Vaga' : 'Publicar Nova Vaga' ?>
            </h1>


            <?php if (isset($erros['geral'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm mb-6">
                    <?= htmlspecialchars($erros['geral'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>


            <form method="POST"
                  action="<?= URL_BASE ?><?= $modoEdicao ? '/vagas/editar/submit' : '/vagas/criar/submit' ?>"
                  class="space-y-6">


                <?php if ($modoEdicao): ?>
                    <input 
                        type="hidden"
                        name="id"
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
    class="w-full border border-gray-300 rounded px-3 py-2"
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
                        class="w-full border border-gray-300 rounded px-3 py-2"
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
                        class="w-full border border-gray-300 rounded px-3 py-2"
                    ><?= $vaga ? htmlspecialchars($vaga->getDescricao(), ENT_QUOTES, 'UTF-8') : '' ?></textarea>

                </div>



                <!-- Tipo de serviço -->
                <fieldset>

                    <legend class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de serviço
                    </legend>

                    <?php $tipoAtual = $vaga ? $vaga->getTipoServico() : ($_POST['tipo_servico'] ?? ''); ?>

                    <div class="flex gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="tipo_servico" value="FIXO" required <?= $tipoAtual === 'FIXO' ? 'checked' : '' ?>>
                            Fixo
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="tipo_servico" value="TEMPORARIO" <?= $tipoAtual === 'TEMPORARIO' ? 'checked' : '' ?>>
                            Temporário
                        </label>
                    </div>

                    <?php if (isset($erros['tipo_servico'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['tipo_servico'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                </fieldset>


                <!-- Duração (só para serviço temporário) -->
                <div id="bloco_duracao" class="<?= $tipoAtual === 'TEMPORARIO' ? '' : 'hidden' ?>">

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Duração
                    </label>

                    <input
                        type="text"
                        id="duracao"
                        name="duracao"
                        maxlength="50"
                        placeholder="Ex.: 3 dias, 2 semanas"
                        value="<?= $vaga ? htmlspecialchars($vaga->getDuracao() ?? '', ENT_QUOTES, 'UTF-8') : htmlspecialchars($_POST['duracao'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                    >

                    <?php if (isset($erros['duracao'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['duracao'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

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
                            class="w-full border border-gray-300 rounded px-3 py-2"
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
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >

                    </div>



                    <!-- Data do serviço -->
                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Data do serviço
                        </label>

                        <input
                            type="date"
                            id="data_servico"
                            name="data_servico"
                            required
                            value="<?= $vaga ? htmlspecialchars($vaga->getDataServico() ?? '', ENT_QUOTES, 'UTF-8') : htmlspecialchars($_POST['data_servico'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >

                        <?php if (isset($erros['data_servico'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['data_servico'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>

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
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >

                    </div>



                    <!-- Horário -->
                    <div>

                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Horário
                        </label>

                        <input
                            type="time"
                            id="horario"
                            name="horario"
                            required
                            value="<?= $vaga ? htmlspecialchars($vaga->getHorario() ?? '', ENT_QUOTES, 'UTF-8') : '' ?>"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >

                        <?php if (isset($erros['horario'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['horario'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>

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
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >

                    </div>


                </div>



                <!-- Observações -->
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Observações adicionais <span class="text-gray-500 font-normal">(opcional, até 500 caracteres)</span>
                    </label>

                    <textarea
                        id="observacoes"
                        name="observacoes"
                        rows="3"
                        maxlength="500"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                    ><?= $vaga ? htmlspecialchars($vaga->getObservacoes() ?? '', ENT_QUOTES, 'UTF-8') : htmlspecialchars($_POST['observacoes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

                    <?php if (isset($erros['observacoes'])): ?>
                        <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erros['observacoes'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                </div>



                <div class="flex gap-4 pt-6 border-t border-gray-200">


                    <!-- Botão teste -->
                    <button
                        type="button"
                        onclick="preencherFormularioTeste()"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded"
                    >
                        Preencher teste
                    </button>


                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded"
                    >
                        <?= $modoEdicao ? 'Salvar alterações' : 'Publicar vaga' ?>
                    </button>


                    <a
                        href="<?= URL_BASE ?>/vagas"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-6 rounded"
                    >
                        Cancelar
                    </a>


                </div>


            </form>

        </div>

    </div>
</main>



<script>

// Duração só aparece (e só é exigida no HTML) quando o serviço é temporário.
// A regra de verdade é validada no servidor.
document.querySelectorAll('input[name=tipo_servico]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        var temporario = document.querySelector('input[name=tipo_servico]:checked').value === 'TEMPORARIO';
        document.getElementById('bloco_duracao').classList.toggle('hidden', !temporario);
        document.getElementById('duracao').required = temporario;
    });
});

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

    document.getElementById('data_servico').value = '2027-01-10';

    document.getElementById('data_limite').value =
        '2026-08-15';

    document.getElementById('horario').value = '08:00';

    document.querySelector('input[name=tipo_servico][value=FIXO]').checked = true;

    document.getElementById('trabalhadores_limite').value =
        '2';

}

</script>



<?php include __DIR__ . '/../shared/footer.php'; ?>