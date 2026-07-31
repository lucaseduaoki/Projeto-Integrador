<?php
$tituloPagina = 'Meus Anúncios';

include __DIR__ . '/../shared/header.php';
include __DIR__ . '/../shared/navbar.php';

$vagas = $vagas ?? [];
?>

<main class="flex-1">
<div class="max-w-7xl mx-auto px-4 py-8">


<h1 class="text-3xl font-bold mb-8">
    Meus Anúncios
</h1>


<?php if(empty($vagas)): ?>

<div class="bg-white rounded-xl border p-8 text-center">

<p class="text-gray-500">
Você ainda não publicou nenhum anúncio.
</p>

<a href="/vagas/criar"
class="inline-block mt-4 bg-blue-600 text-white px-5 py-2 rounded-lg">

Criar anúncio

</a>

</div>


<?php else: ?>


<div class="grid md:grid-cols-3 gap-6">


<?php foreach($vagas as $vaga): ?>


<div class="bg-white border rounded-xl p-6 shadow-sm">


<h2 class="font-bold text-lg">
<?= htmlspecialchars($vaga->getTitulo()) ?>
</h2>


<p class="text-gray-600 mt-2">
<?= htmlspecialchars($vaga->getDescricao()) ?>
</p>


<div class="mt-4">

<span class="font-semibold">
R$
<?= number_format($vaga->getRemuneracao(),2,',','.') ?>
</span>

</div>


<div class="flex gap-2 mt-5">


<a
href="/vagas/visualizar?id=<?= $vaga->getIdVaga() ?>"
class="bg-blue-50 text-blue-600 px-4 py-2 rounded">

Visualizar

</a>


<a
href="/vagas/editar?id=<?= $vaga->getIdVaga() ?>"
class="bg-gray-100 px-4 py-2 rounded">

Editar

</a>


<form method="POST"
action="/vagas/excluir">

<input type="hidden"
name="id"
value="<?= $vaga->getIdVaga() ?>">

<button
class="bg-red-50 text-red-600 px-4 py-2 rounded">

Excluir

</button>

</form>


</div>


<a
href="/interesse/interessados?id=<?= $vaga->getIdVaga() ?>"
class="block mt-4 text-center bg-green-600 text-white py-2 rounded">

Ver interessados

</a>


</div>


<?php endforeach; ?>


</div>


<?php endif; ?>


</div>
</main>


<?php include __DIR__ . '/../shared/footer.php'; ?>