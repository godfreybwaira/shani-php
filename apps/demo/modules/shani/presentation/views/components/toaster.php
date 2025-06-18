<?php if ($app->request->query->getOne('type') === 't'): ?>
    <div class="toaster color-danger pos-tc width-md-5 width-sm-10 padding-xy">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sit, at, esse magnam quaerat recusandae cum necessitatibus nemo praesentium assumenda suscipit velit harum omnis exercitationem unde fuga modi rem! Natus, esse.
    </div>
<?php else: ?>
    <div class="toaster color-success pos-bc width-md-5 width-sm-10 padding-xy">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sit, at, esse magnam quaerat recusandae cum necessitatibus nemo praesentium assumenda suscipit velit harum omnis exercitationem unde fuga modi rem! Natus, esse.
    </div>
<?php endif; ?>
