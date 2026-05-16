<pre><?php
    $gen = $web->data();
    echo json_encode($gen, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    ?>
</pre>