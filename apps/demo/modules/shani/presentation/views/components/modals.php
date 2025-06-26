<div class="modal-background">
    <?php if ($app->request->query->getOne('type') === 'c'): ?>
        <div class="modal modal-type-c width-sm-10 height-sm-10 pos-c">
            <button shani-fn="close" class="button button-times pos-tr" shani-target=".modal-background"></button>
            <div class="container height-sm-max">
                <div class="spinner" style="--size:2.5rem"></div>
            </div>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'vl'): ?>
        <div class="modal modal-type-v width-md-1q width-sm-10 pos-l">
            <button shani-fn="close" class="button button-times" shani-target=".modal-background"></button>
            <div class="card">
                <div class="card-body container">
                    <div class="spinner" style="--size:2.5rem"></div>
                </div>
            </div>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'vr'): ?>
        <div class="modal modal-type-v width-md-1q width-sm-10 pos-r">
            <button shani-fn="close" class="button button-times" shani-target=".modal-background"></button>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'ht'): ?>
        <div class="modal modal-type-h height-sm-4 pos-t">
            <button shani-fn="close" class="button button-times" shani-target=".modal-background"></button>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'hb'): ?>
        <div class="modal modal-type-h height-sm-4 pos-b">
            <button shani-fn="close" class="button button-times" shani-target=".modal-background"></button>
        </div>
    <?php elseif ($app->request->query->getOne('type') === 'cl'): ?>
        <div class="modal modal-type-c width-sm-10 height-sm-10 pos-c">
            <?php $app->ui()->layout($app->view('/box/modal_nav'), $app->view('/body'), $app->view('/box/menu')); ?>
        </div>
    <?php else: ?>
        Not available!
    <?php endif; ?>
</div>