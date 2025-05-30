<?php

$app->ui()->import($app->view('/components'));

$modal = new gui\v2\containers\modals\Modal();
$modal->setAutoclose()->classList->addAll(['width-md-10', 'height-md-11']);
echo $modal->open();
$app->ui()->layout($app->view('/box/menu'), $app->view('/box/navbar'), $app->view('/box/body'));
echo $modal->close();
