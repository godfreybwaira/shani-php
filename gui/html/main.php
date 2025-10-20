<!doctype html>
<html lang="<?= $web->language(); ?>">
    <head>
        <base href="/"/><meta charset="utf-8"/><meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <?= $web->head(); ?>
    </head>
    <body><?php require $web->view(); ?></body>
</html>
