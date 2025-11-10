<div class="tab tab-pos-b">
    <div class="tab-menu menubar">
        <a href="/shani/0/components/0/generator" shani-cache="age:30s" class="active menu-item"
           shani-on="click::read>>#content;httpend::loaderrmv>>#content;
           httpstart::loadercreate name:loader-spin>>#content;">
            <i class="mdi mdi-cog"></i>
            <span class="font-sm menu-label">Generator</span>
        </a>
        <a href="/shani/0/components/0/all" shani-on="click::read>>#content" class="menu-item">
            <i class="mdi mdi-tab"></i>
            <span class="font-sm menu-label">Components</span>
        </a>
        <a href="/shani/0/components/0/containers"
           shani-on="click::read>>#content" class="menu-item">
            <i class="mdi mdi-rectangle"></i>
            <span class="font-sm menu-label">Containers</span>
        </a>
        <a href="/shani/0/components/0/inputs"
           shani-on="click::read mode:replace>>#content" class="menu-item">
            <i class="mdi mdi-inbox-full"></i>
            <span class="font-sm menu-label">Inputs</span>
        </a>
    </div>
    <div class="tab-body container" id="content">
        <?php $web->import($web->view('/all')); ?>
    </div>
</div>