<?php $app->template()->import($app->view('/shani')); ?>

<?php $data = $app->template()->data(); ?>
<h2><?= $data['greeting']; ?>, It works.</h2>