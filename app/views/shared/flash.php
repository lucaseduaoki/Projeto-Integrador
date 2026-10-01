<?php
$flashSucesso = $_SESSION['flash_sucesso'] ?? null;
$flashErro = $_SESSION['flash_erro'] ?? null;
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
?>

<?php if ($flashSucesso): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded text-sm">
            <?= htmlspecialchars($flashSucesso, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($flashErro): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            <?= htmlspecialchars($flashErro, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
<?php endif; ?>
