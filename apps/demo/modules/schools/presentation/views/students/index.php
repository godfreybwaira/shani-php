<?php $app->ui()->import($app->view('/shani')); ?>
<?php

$box = new gui\v2\containers\GridLayout();
$header = new gui\v2\containers\GridArea();
$left = new gui\v2\containers\GridArea();
$main = new gui\v2\containers\GridArea();
$footer = new gui\v2\containers\GridArea();
//////////////////////
$mobile = \gui\v2\TargetDevice::MOBILE;
$tablet = \gui\v2\TargetDevice::TABLET;
$laptop = \gui\v2\TargetDevice::LAPTOP;
//////////////////////
$box->addArea($mobile, $header);
$box->addArea($mobile, $left);
$box->addArea($mobile, $main);
$box->addArea($mobile, $footer);
//////////////////////
$box->addArea($laptop, $header, $header, $header);
$box->addArea($laptop, $left, $main, $main);
$box->addArea($laptop, $left, $main, $main);
$box->addArea($laptop, $footer, $footer, $footer);
//////////////////////

$header->setText('HEADER');
$left->setText('LEFT');
$main->setText('MAIN');
$footer->setText('FOOTER');

$modal = new \gui\v2\containers\modals\HorizontalModal();
$modal->addSize(\gui\v2\DeviceSize::MOBILE, 5);
$modal->addSize(\gui\v2\DeviceSize::LAPTOP, 1);
$modal->alignBottom();
$modal->appendChild($box);
echo $modal;
