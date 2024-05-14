<!doctype html>
<html lang="<?= $app->language(); ?>">
    <head>
        <base href="/"/>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <link rel="stylesheet" href="<?= $app->asset()->css('/comp/table'); ?>"/>
        <script defer src="<?= $app->asset()->js('/3.0/shani.min'); ?>"></script>
        <?= $app->gui()->head(); ?>
    </head>
    <body><?php require $app->gui()->root('/layout'); ?></body>
</html>
