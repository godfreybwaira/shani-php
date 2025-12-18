<div class="tab tab-pos-b">
    <div class="tab-menu menubar" id="parent1" data-prop="prop7:val7&prop8:@data-prop8" data-prop8="val8">
        <a href="/shani/0/components/0/generator" shani-cache="@data-cache"
           class="active menu-item" data-maxage="30s"
           shani-on="click->read>>#content;httpend->loaderrmv>>#content;
           httpstart->loadercreate name:loader-spin&size:2.5rem>>#content;">
            <i class="mdi mdi-cog"></i>
            <span class="font-sm menu-label">Generator</span>
        </a>
        <a href="/shani/0/components/0/all" shani-on="click->read>>#content" class="menu-item">
            <i class="mdi mdi-tab"></i>
            <span class="font-sm menu-label">Components</span>
        </a>
        <a href="/shani/0/components/0/containers"
           shani-on="click->read>>#content" class="menu-item">
            <i class="mdi mdi-rectangle"></i>
            <span class="font-sm menu-label">Containers</span>
        </a>
        <a href="/shani/0/components/0/inputs" data-read="mode:@data-mode"
           data-prop="val1" data-prop3="prop3:val3" data-prop4="val4" data-prop5="prop5:@d-v5&prop6:val6" d-v5="val5"
           data-headers="x-H1:v1&x-H2:v2,v22"
           shani-on="click->read #parent1@data-prop&prop1:@data-prop&prop2:val2&@data-prop3&data-prop4&@data-prop5>>#content;" data-mode="replace"
           shani-headers="@data-headers" class="menu-item">
            <i class="mdi mdi-inbox-full"></i>
            <span class="font-sm menu-label">Inputs</span>
        </a>
        <a href="/shani/0/components/0/bindings" shani-on="click->read>>#content" class="menu-item">
            <i class="mdi mdi-anchor"></i>
            <span class="font-sm menu-label">Bindings</span>
        </a>
    </div>
    <div class="tab-body container" id="content">
        <?php $web->import($web->view('/all')); ?>
    </div>
</div>