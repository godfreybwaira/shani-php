<div class="tab tab-pos-b">
    <div class="tab-menu menubar">
        <a href="/shani/0/components/0/generator" shani-on="click:r;start:cssaddt loader-spin;end:cssrmvt loader-spin"
           shani-target="#content" shani-formatter="formatter" class="active menu-item">
            <i class="mdi mdi-cog"></i>
            <span class="font-sm menu-label">Generator</span>
        </a>
        <a href="/shani/0/components/0/all" shani-on="click:r"
           shani-target="#content" class="menu-item">
            <i class="mdi mdi-tab"></i>
            <span class="font-sm menu-label">Components</span>
        </a>
        <a href="/shani/0/components/0/containers"
           shani-on="click:r" shani-target="#content" class="menu-item">
            <i class="mdi mdi-rectangle"></i>
            <span class="font-sm menu-label">Containers</span>
        </a>
        <a href="/shani/0/components/0/inputs"
           shani-on="click:r" shani-target="#content" class="menu-item">
            <i class="mdi mdi-inbox-full"></i>
            <span class="font-sm menu-label">Inputs</span>
        </a>
    </div>
    <div class="tab-body container" id="content" style="--loader-size:2.5rem">
        <?php $app->ui()->import($app->view('/all')); ?>
    </div>
</div>