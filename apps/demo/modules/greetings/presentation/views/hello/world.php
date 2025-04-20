<?php $app->ui()->import($app->view('/shani')); ?>

<?php $data = $app->ui()->data()->toArray(); ?>
<h2>Student details</h2>
<ul>
    <?php foreach ($data as $key => $value): ?>
        <?php if (is_array($value)): ?>
            <li>
                <?= $key . ':'; ?>
                <?php foreach ($value as $v): ?>
                    <ul>
                        <li><?= 'name: ' . $v['name']; ?></li>
                        <li><?= 'grade: ' . $v['grade']; ?></li>
                        <li><?= 'marks: ' . $v['marks']; ?></li>
                    </ul>
                    <hr/>
                <?php endforeach; ?>
            </li>
        <?php else: ?>
            <li><?= $key . ': ' . $value; ?></li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
<?php
$button = new \gui\v2\controls\FlatButton('Hello There');
echo $button;
