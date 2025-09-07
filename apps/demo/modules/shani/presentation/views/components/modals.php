<div class="modal-background">
    <?php if ($app->request->query->getOne('type') === 'c'): ?>
        <div class="modal modal-type-c width-sm-10 height-sm-10 pos-c">
            <button shani-fn="close" class="button button-times pos-tr" style="margin: var(--spacing)"
                    shani-target=".modal-background">&times;</button>
            <div class="height-sm-max load-spin" style="--size:2.5rem"></div>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'vl'): ?>
        <div class="modal modal-type-v width-md-1q width-sm-3q pos-l" action="/shani/0/components/0/generator"
             shani-fn="r" shani-on="load" id="modal1" shani-target="#card1">
            <button shani-fn="close" class="button button-times pos-tr"
                    shani-target=".modal-background" style="margin: var(--spacing)">&times;</button>
            <div class="card" style="--size:2.5rem" watch-on="end,start"
                 id="card1" shani-watch="#modal1" shani-css="start:add load-spin,end:remove load-spin">
            </div>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'vr'): ?>
        <div class="modal modal-type-v width-md-1q width-sm-3q pos-r">
            <button shani-fn="close" class="button button-times" shani-target=".modal-background">&times;</button>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'ht'): ?>
        <div class="modal modal-type-h height-sm-4 pos-t">
            <button shani-fn="close" class="button button-times" shani-target=".modal-background">&times;</button>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'hb'): ?>
        <div class="modal modal-type-h height-sm-4 pos-b">
            <button shani-fn="close" class="button button-times" shani-target=".modal-background">&times;</button>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'cl'): ?>
        <div class="modal modal-type-c width-sm-3q height-sm-10 pos-c">
            <?php $app->ui()->import($app->view('/shani')); ?>
        </div>
    <?php else: ?>
        Not available!
    <?php endif; ?>
</div>