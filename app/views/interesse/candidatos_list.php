<?php
$tituloPagina = 'Candidatos da Vaga';

$vaga = $vaga ?? null;
$interessados = $interessados ?? [];

$tituloVaga = $vaga && method_exists($vaga, 'getTitulo')
    ? $vaga->getTitulo()
    : 'Vaga selecionada';

$voltarUrl = $vaga
    ? '/vagas/visualizar?id=' . $vaga->getIdVaga()
    : '/vagas';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';
?>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="mb-8">

            <a href="<?= htmlspecialchars($voltarUrl) ?>"
               class="text-blue-600 hover:text-blue-700 font-medium text-sm mb-4 inline-block">
                ← Voltar para vaga
            </a>

            <div class="flex justify-between items-center">

                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Interessados na vaga:
                    </h1>

                    <p class="text-xl text-gray-700 mt-1">
                        <?= htmlspecialchars($tituloVaga) ?>
                    </p>
                </div>


                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-medium">
                    <?= count($interessados) ?>
                    interessado<?= count($interessados) != 1 ? 's' : '' ?>
                </span>

            </div>

        </div>


        <?php if(empty($interessados)): ?>

            <div class="text-center py-16 bg-white rounded-xl border">

                <p class="text-gray-500 text-lg">
                    Nenhum trabalhador demonstrou interesse ainda.
                </p>

            </div>


        <?php else: ?>


            <div class="bg-white rounded-xl shadow border overflow-hidden">

                <table class="w-full">

                    <thead class="bg-gray-50 border-b">

                        <tr>

                            <th class="px-6 py-3 text-left">
                                Trabalhador
                            </th>

                            <th class="px-6 py-3 text-left">
                                Data
                            </th>

                            <th class="px-6 py-3 text-left">
                                Status
                            </th>

                            <th class="px-6 py-3 text-left">
                                Ações
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y">


                    <?php foreach($interessados as $interesse): ?>


                        <?php

                        $status = $interesse->getStatus();

                        $statusClass = match($status){

                            'ACEITO'
                                => 'bg-green-100 text-green-800',

                            'RECUSADO'
                                => 'bg-red-100 text-red-800',

                            default
                                => 'bg-yellow-100 text-yellow-800'
                        };

                        ?>


                        <tr>


                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">


                                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">

                                        <?= $interesse->getIdTrabalhador() ?>

                                    </div>


                                    <div>

                                        <p class="font-semibold text-gray-900">

                                            Trabalhador #<?= $interesse->getIdTrabalhador() ?>

                                        </p>


                                        <p class="text-sm text-gray-500">

                                            ID interesse:
                                            <?= $interesse->getIdInteresse() ?>

                                        </p>

                                    </div>


                                </div>


                            </td>



                            <td class="px-6 py-4 text-gray-600">

                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($interesse->getDataInteresse())
                                ) ?>


                            </td>



                            <td class="px-6 py-4">


                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">

                                    <?= ucfirst(strtolower($status)) ?>

                                </span>


                            </td>



                            <td class="px-6 py-4">


                                <?php if($status === 'PENDENTE'): ?>


                                    <form method="POST" action="/interesse/aceitar">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $interesse->getIdInteresse() ?>"
                                        >


                                        <button
                                            class="text-green-600 hover:text-green-800 font-medium">

                                            Aceitar

                                        </button>


                                    </form>


                                <?php endif; ?>


                            </td>



                        </tr>


                    <?php endforeach; ?>


                    </tbody>


                </table>


            </div>


        <?php endif; ?>


    </div>
</main>


<?php include __DIR__ . '/../shared/footer.php'; ?>