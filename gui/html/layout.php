<div class="layout-container">
    <div class="layout-menu">
        <?php $app->ui()->import($menu_); ?>
    </div>
    <div class="layout-content">
        <div class="content-navbar">
            <?php $app->ui()->import($navbar_); ?>
        </div>
        <div class="content-body">
            <?php $app->ui()->import($body_); ?>
        </div>
    </div>
</div>