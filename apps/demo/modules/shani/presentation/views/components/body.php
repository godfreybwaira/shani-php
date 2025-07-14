<div class="tab tab-pos-b">
    <div class="tab-menu menubar">
        <a href="/shani/0/components/0/all" shani-fn="r"
           shani-on="click" shani-target="#content" class="active menu-item">
            <i class="mdi mdi-tab"></i>
            <span class="font-sm menu-label">Components</span>
        </a>
        <a href="/shani/0/components/0/containers"
           shani-fn="r" shani-target="#content" class="menu-item">
            <i class="mdi mdi-rectangle"></i>
            <span class="font-sm menu-label">Containers</span>
        </a>
        <a href="/shani/0/components/0/inputs"
           shani-fn="r" shani-target="#content" class="menu-item">
            <i class="mdi mdi-inbox-full"></i>
            <span class="font-sm menu-label">Inputs</span>
        </a>
    </div>
    <div class="tab-body container" id="content">
        <?php $app->ui()->import($app->view('/all')); ?>
    </div>
</div>