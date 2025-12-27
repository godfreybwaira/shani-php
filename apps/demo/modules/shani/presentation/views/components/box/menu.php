<div class="menubar accent-color">
    <a href="/" class="active menu-item menu-item-dir-v" shani-on="click->ui.select active-class:active">
        <i class="mdi mdi-home"></i>
        <span class="font-sm menu-label">Home</span>
    </a>
    <a href="/shani/0/components/0/shani"
       shani-on="click->ui.select active-class:active;ui.select->http.pull>>#mainbody" class="menu-item menu-item-dir-v">
        <i class="mdi mdi-code-tags"></i>
        <span class="font-sm menu-label">Shani</span>
    </a>
    <a href="/shani/0/components/0/stream" shani-http="@data-conn" data-conn="cname:@http-name"
       shani-on="click->ui.select active-class:active;ui.select->ui.loader @loader-bar-specs>>.layout-content;
       ui.loader->http.abort @conn;http.abort->http.pull>>#content;
       httpend->ui.loader.rmv>>.layout-content" class="menu-item menu-item-dir-v">
        <i class="mdi mdi-water"></i>
        <span class="font-sm menu-label">Streaming</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click->ui.select active-class:active;ui.select->ui.loader name:loader-top&size:.2rem>>.layout-content;
       cssadd->http.pull>>#content;httpend->ui.loader.rmv>>.layout-content">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 1</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click->ui.select active-class:active;ui.select->ui.loader @loader-black>>.layout-container;
       ui.loader->http.abort @conn;
       http.abort->http.pull>>#content;
       httpend->ui.loader.rmv>>.layout-container">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 2</span>
    </a>
    <a href="/shani/0/components/0/card" class="menu-item menu-item-dir-v" data-headers="content-type:application/json"
       shani-on="click->ui.select active-class:active;ui.select->ui.modal @modal-specs;
       ui.modal->http.pull>>#mdl123; 408->timeout;
       httpend->ui.loader.rmv>>#mdl123;
       httpstart->ui.loader @loader-circle-specs>>#mdl123;
       data->util.saveas @saved-file;"
       shani-http="@default-http" shani-headers="@data-headers">
        <i class="mdi mdi-inbox-full"></i>
        <span class="font-sm menu-label">Modal</span>
    </a>
    <a href="#" class="menu-item menu-item-dir-v" title="Settings" style="margin-top:auto">
        <i class="mdi mdi-cog"></i>
    </a>
</div>