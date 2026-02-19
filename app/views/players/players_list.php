<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    

    <h1>Lista de jogadores</h1>

    <?php foreach($list as $player): ?>

        <p><?= $player?></p>

    <?php endforeach; ?>
</body>
</html>