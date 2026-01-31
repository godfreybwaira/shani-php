<div class="modal-background">
    <?php $type = $web->attr->getOne('type'); ?>
    <?php if ($type === 'c'): ?>
        <div class="modal modal-type-c width-sm-10 height-sm-10 pos-c">
            <button shani-on="click->ui.close>>.modal-background"
                    class="button button-times pos-tr" style="margin: var(--spacing)">&times;</button>
            <div class="height-sm-max loader-spin" style="--loader-size:2.5rem"></div>
        </div>
    <?php elseif ($type === 'vl'): ?>
        <div class="modal modal-type-v width-md-1q width-sm-3q pos-l">
            <button shani-on="click->ui.close>>.modal-background" id="closebtn1" class="button button-times pos-tr"
                    style="margin: var(--spacing)">&times;</button>
            <div class="card" action="/shani/0/components/0/generator"
                 shani-on="load delay:2s&steps:3s&limit:4&ondone:--close-me->http.pull;
                 httpend->ui.loader.rmv name:loader-spin;
                 httpstart->ui.loader name:loader-spin&size:2.5rem;
                 --close-me->util.trigger click>>#closebtn1"
                 shani-http="url:@action">
            </div>
        </div>
    <?php elseif ($type === 'vr'): ?>
        <div class="modal modal-type-v width-md-1q width-sm-3q pos-r">
            <button shani-on="click->ui.close>>.modal-background" class="button button-times">&times;</button>
        </div>
    <?php elseif ($type === 'ht'): ?>
        <div class="modal modal-type-h height-sm-4 pos-t">
            <button shani-on="click->ui.close>>.modal-background" class="button button-times">&times;</button>
        </div>
    <?php elseif ($type === 'hb'): ?>
        <div class="modal modal-type-h height-sm-4 pos-b">
            <button shani-on="click->ui.close>>.modal-background" class="button button-times">&times;</button>
        </div>
    <?php elseif ($type === 'cl'): ?>
        <div class="modal modal-type-c width-sm-3q height-sm-10 pos-c">
            <?php $web->import($web->view('/shani')); ?>
        </div>
    <?php else: ?>
        Not available!
    <?php endif; ?>
</div>