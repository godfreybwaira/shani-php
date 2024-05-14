<div class="layout-container">
    <?php
    $template = $app->gui();
    $top = $template->topType();
    $bottom = $template->bottomType();
    ?>
    <div class="layout-content">
        <?php if (!empty($top)): ?>
            <div class="content-navbar <?= $top; ?>">
                <?php $template->top(); ?>
            </div>
        <?php endif; ?>
        <div class="content-body">
            <ul class="breadcrumb font-sm padding-y-md">
                <li><a href="/"><i class="mdi mdi-home"></i></a></li>
                <?= $template->breadcrumb(); ?>
            </ul>
            <?php $template->view(); ?>
        </div>
        <?php if (!empty($bottom)): ?>
            <div class="content-navbar <?= $bottom; ?>">
                <?php $template->bottom(); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($template->showMenu()): ?>
        <div class="layout-menubar">
            <div class="menubar">
                <?php $template->menu(); ?>
            </div>
            <div class="menubar-content"></div>
        </div>
    <?php endif; ?>
</div>