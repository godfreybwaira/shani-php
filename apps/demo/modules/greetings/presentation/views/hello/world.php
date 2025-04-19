<?php

use gui\v2\Component;
use gui\v2\decoration\Margin;
use gui\v2\decoration\Padding;
use gui\v2\decoration\RoundCorner;

$data = $app->ui()->data()->toArray();

$padding = new Padding();
$margin = new Margin();
$radius = new RoundCorner();
$radius->all(1);
$padding->all(1);
$margin->all(1);

$h2 = new Component('h2');
$h2->setText('Student Details');
$h2->addDecoration($radius);
$ul = new Component('ul');
$ul->addDecoration($padding);
foreach ($data as $key => $value):
    $li = new Component('li');
    if (is_array($value)):
        $title = new Component('span');
        $title->setText($key . ':');
        $title->addDecoration($padding);
        $li->appendChild($title);
        foreach ($value as $v):
            $ul2 = new Component('ul');
            $li2 = new Component('li');
            $li3 = new Component('li');
            $li4 = new Component('li');
            $hr = new Component('hr');
            $li2->setText('name: ' . $v['name']);
            $li3->setText('grade: ' . $v['grade']);
            $li4->setText('marks: ' . $v['marks']);
            $ul2->appendChild($li2, $li3, $li4);
            $ul2->addDecoration($padding, $margin);
            $li->appendChild($ul2, $hr);
        endforeach;
    else:
        $li->setText($key . ': ' . $value);
    endif;
    $ul->appendChild($li);
endforeach;
echo $h2 . $ul;
