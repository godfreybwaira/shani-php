<?php if ($app->request->query->getOne('type') === 'i'): ?>
    <div class="progress-bar loader">
        <div class="progress"></div>
    </div>
<?php else: ?>
    <div class="progress-bar loader">
        <div class="progress" style="--color:lime"></div>
    </div>
<?php endif; ?>
