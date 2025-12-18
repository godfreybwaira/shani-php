<div class="menubar accent-color">
    <a href="/" shani-on="click->nodeprepend -tag:input&readonly&type:text&id:id12&placeholder:some texts&name:my_name>>#mainbody" class="active menu-item menu-item-dir-v">
        <i class="mdi mdi-home"></i>
        <span class="font-sm menu-label">Home</span>
    </a>
    <a href="/shani/0/components/0/shani"
       shani-on="click->read>>#mainbody" class="menu-item menu-item-dir-v">
        <i class="mdi mdi-code-tags"></i>
        <span class="font-sm menu-label">Shani</span>
    </a>
    <a href="/shani/0/components/0/stream" shani-http="@data-conn" data-conn="conn:@http-name"
       shani-on="click->loadercreate @loader-bar-specs>>.layout-content;
       loadercreate->abortconn @conn;abortconn->read>>#content;
       httpend->loaderrmv>>.layout-content" class="menu-item menu-item-dir-v">
        <i class="mdi mdi-water"></i>
        <span class="font-sm menu-label">Streaming</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click->loadercreate name:loader-top&size:.2rem>>.layout-content;
       cssadd->read>>#content;httpend->loaderrmv>>.layout-content">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 1</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click->loadercreate @loader-black>>.layout-container;
       loadercreate->abortconn @conn;
       abortconn->read>>#content;
       httpend->loaderrmv>>.layout-container">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 2</span>
    </a>
    <a href="/shani/0/components/0/card" class="menu-item menu-item-dir-v" data-headers="content-type:application/json"
       shani-on="click->modalcreate @modal-specs;
       modalcreate->read>>#mdl123; 408->timeout;
       httpend->loaderrmv>>#mdl123;
       httpstart->loadercreate @loader-circle-specs>>#mdl123;
       data->saveas @saved-file;"
       shani-http="@default-http" shani-headers="@data-headers">
        <i class="mdi mdi-inbox-full"></i>
        <span class="font-sm menu-label">Modal</span>
    </a>
    <a href="#" class="menu-item menu-item-dir-v" title="Settings" style="margin-top:auto">
        <i class="mdi mdi-cog"></i>
    </a>
</div>