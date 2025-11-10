<div class="menubar accent-color">
    <a href="/" shani-on="click::nodeprepend -tag:input&readonly&type:text&id:id12&placeholder:some texts&name:my_name>>#mainbody" class="active menu-item menu-item-dir-v">
        <i class="mdi mdi-home"></i>
        <span class="font-sm menu-label">Home</span>
    </a>
    <a href="/shani/0/components/0/shani"
       shani-on="click::read>>#mainbody" class="menu-item menu-item-dir-v">
        <i class="mdi mdi-code-tags"></i>
        <span class="font-sm menu-label">Shani</span>
    </a>
    <a href="/shani/0/components/0/stream" shani-http="conn:aaa"
       shani-on="click::loadercreate name:loader-top&color:red&size:.2rem>>.layout-content;
       loadercreate::abortconn name:aaa;abortconn::read mode:replace>>#content;
       httpend::loaderrmv>>.layout-content" class="menu-item menu-item-dir-v">
        <i class="mdi mdi-water"></i>
        <span class="font-sm menu-label">Streaming</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click::loadercreate name:loader-top&size:.2rem>>.layout-content;
       cssadd::read>>#content;httpend::loaderrmv>>.layout-content">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 1</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click::loadercreate name:loader-spin&color:#000&size:2.5rem>>.layout-container;
       loadercreate::abortconn name:aaa;
       abortconn::read>>#content;
       httpend::loaderrmv>>.layout-container">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 2</span>
    </a>
    <a href="/shani/0/components/0/card" class="menu-item menu-item-dir-v"
       shani-on="click::modalcreate id:mdl123&classes:modal modal-type-c width-sm-10 height-sm-10 pos-c&close-btn:pos-tr;
       modalcreate::read mode:replace>>#mdl123; 408::timeout; httpend::loaderrmv>>#mdl123;
       httpstart::loadercreate name:loader-spin&size:2.5rem>>#mdl123;
       data::saveas name:file22.txt&type:text/plain;"
       shani-http="credentials:same-origin&mode:cors&timeout:2s"
       shani-headers="content-type:application/json">
        <i class="mdi mdi-inbox-full"></i>
        <span class="font-sm menu-label">Modal</span>
    </a>
    <a href="#" class="menu-item menu-item-dir-v" title="Settings" style="margin-top:auto">
        <i class="mdi mdi-cog"></i>
    </a>
</div>