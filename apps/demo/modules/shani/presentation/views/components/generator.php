<pre>
    <?php
    $gen = new \shani\documentation\Generator($app, ['shani']);
    echo json_encode($gen, JSON_PRETTY_PRINT);
//    print_r($gen->jsonSerialize());
    ?>
</pre>