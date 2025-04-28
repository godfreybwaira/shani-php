<?php $app->ui()->import($app->view('/shani')); ?>
<div shanify="*" shani-header="x-request-mode:async" shani-on="click" shani-fn="r" style="background: red;padding: 3rem">
    <a href="/greetings/0/hello/0/test" shani-fn="r" shani-watcher="#box,#box2" shani-log="true">Testing</a>
    <div id="box" watch-on="200" shani-insert="after" shani-plugin="init:fs">BOX 1</div>
    <div id="box2" action="/greetings/0/hello/0/other" watch-on="end" shani-xss="true" shani-insert="before">BOX 2</div>
</div>