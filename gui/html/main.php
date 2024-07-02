<!doctype html>
<html lang="<?= $app->language(); ?>">
    <head>
        <base href="/"/><meta charset="utf-8"/><meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"/><?= $app->template()->head(); ?>
    </head>
    <body><?php require $app->view(); ?></body>
</html>
