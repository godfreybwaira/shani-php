<?php

\gui\v1\Theme::setStyles([
    'avatar' => ['avatar'],
    'avatar-size-md' => ['size-md'],
    'avatar-on' => ['state-on'],
    'avatar-off' => ['state-off'],
    'avatar-stack' => ['stack'],
]);
$node = new gui\v1\components\Avatar();
$node->setGutters(gui\v1\components\Avatar::SIZE_MD)
        ->setStack()->setState(gui\v1\components\Avatar::STATE_OFF)
        ->appendChildren(new gui\v1\Component('img'));

echo $node;
