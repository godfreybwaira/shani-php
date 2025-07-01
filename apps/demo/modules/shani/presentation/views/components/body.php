<div class="tab tab-pos-b">
    <div class="tab-menu menubar">
        <a href="#" class="menu-item">
            <i class="mdi mdi-professional-hexagon"></i>
            <span class="font-sm">Professional</span>
        </a>
        <a href="#" class="active menu-item">
            <i class="mdi mdi-account-group"></i>
            <span class="font-sm">All Users</span>
        </a>
        <a href="#" class="menu-item">
            <i class="mdi mdi-cog"></i>
            <span class="font-sm">Settings</span>
        </a>
        <a href="#" class="menu-item">
            <i class="mdi mdi-account"></i>
            <span class="font-sm">Profile</span>
        </a>
    </div>
    <div class="tab-body container" id="content">
        <?php $app->ui()->import($app->view('/all')); ?>
    </div>
</div>