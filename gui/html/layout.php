<div class="layout-container">
    <?php if (!empty($menu_)): ?>
        <div class="layout-menu">
            <?php $web->import($menu_); ?>
        </div>
    <?php endif; ?>
    <div class="layout-content">
        <div class="content-navbar">
            <?php $web->import($navbar_); ?>
        </div>
        <div class="content-body"<?= !empty($id_) ? ' id="' . $id_ . '"' : null; ?>>
            <?php $web->import($body_); ?>
        </div>
    </div>
</div>