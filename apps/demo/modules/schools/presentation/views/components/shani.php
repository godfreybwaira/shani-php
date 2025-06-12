<?php

$app->ui()->import($app->view('/components'));
/*
$modal = new gui\v2\containers\modals\Modal();
$modal->classList->addAll(['width-md-10', 'height-md-11']);
$app->attr()->AddOne('modal', '#' . $modal->getParent()->getId());
echo $modal->open();
$app->ui()->layout($app->view('/box/modal_nav'), $app->view('/box/body'), $app->view('/box/menu'));
echo $modal->close();
*/