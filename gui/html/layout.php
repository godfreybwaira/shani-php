<div class="layout-container">
    <?php if (!empty($menu_)): ?>
        <div class="layout-menu">
            <?php $app->ui()->import($menu_); ?>
        </div>
    <?php endif; ?>
    <div class="layout-content">
        <div class="content-navbar">
            <?php $app->ui()->import($navbar_); ?>
        </div>
        <div class="content-body"<?= !empty($id_) ? ' id="' . $id_ . '"' : null; ?>>
            <?php $app->ui()->import($body_); ?>
        </div>
    </div>
</div>