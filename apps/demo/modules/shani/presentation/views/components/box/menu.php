<div class="menubar accent-color">
    <a href="/" shani-on="click:r>>#mainbody" class="active menu-item menu-item-dir-v">
        <i class="mdi mdi-home"></i>
        <span class="font-sm menu-label">Home</span>
    </a>
    <a href="/shani/0/components/0/shani"
       shani-on="click:r>>#mainbody" class="menu-item menu-item-dir-v">
        <i class="mdi mdi-code-tags"></i>
        <span class="font-sm menu-label">Shani</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click:cssadd loader-top>>.layout-content;cssadd:r>>#content;end:cssrmv loader-top>>.layout-content">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 1</span>
    </a>
    <a href="/shani/0/components/0/generator" class="menu-item menu-item-dir-v"
       shani-on="click:makeloader ldr123:red>>.layout-content;makeloader:r>>#content;end:rmv>>#ldr123">
        <i class="mdi mdi-loading"></i>
        <span class="font-sm menu-label">Loader 2</span>
    </a>
    <a href="/shani/0/components/0/card" class="menu-item menu-item-dir-v"
       shani-on="click:makemodal mdl123:modal modal-type-c width-sm-10 height-sm-10 pos-c&close-btn:pos-tr;
       makemodal:r replace>>#mdl123; data: hello;
       start:cssadd loader-spin>>#mdl123;
       end:cssrmv loader-spin>>#mdl123">
        <i class="mdi mdi-inbox-full"></i>
        <span class="font-sm menu-label">Modal</span>
    </a>
    <a href="#" class="menu-item menu-item-dir-v" title="Settings" style="margin-top:auto">
        <i class="mdi mdi-cog"></i>
    </a>
</div>