<style type="text/css">
    .tab-pos-t{
        grid-template-areas: "a1""a2";
    }
    .tab-pos-b{
        grid-template-areas: "a2""a1";
    }
    .tab-pos-r{
        grid-template-areas: "a2 a1";
    }
    .tab-pos-l{
        grid-template-areas: "a1 a2";
    }
</style>
<div class="toast color-7 width-sm-10 width-md-4 pos-tc padding-xy">
    Lorem ipsum dolor
</div>
<div class="modal-background">
    <div class="modal modal-type-c pos-c width-sm-11 width-md-9 height-sm-9">
        <div class="tab tab-pos-t">
            <div class="tab-menu" style="grid-area: a1;">
                <a class="menu-item">Menu One</a>
                <a class="active menu-item menu-item-dir-v">
                    <i class="mdi mdi-account-box size-md"></i>
                    <span class="label">Menu One here</span>
                </a>
                <a class="menu-item menu-item-dir-v">
                    <i class="mdi mdi-account-box"></i>
                    <span class="label">Menu One here</span>
                </a>
                <a class="menu-item menu-item--dir-v">
                    <i class="mdi mdi-account-box"></i>
                    <span class="label">Menu One here</span>
                </a>
            </div>
            <div class="tab-body padding-xy" style="grid-area: a2;">
                <ul class="list list-dir-v size-md shadow-sm">
                    <li>hello from here</li>
                    <li>Hello from here. Can this be too long?</li>
                    <li>hello from here</li>
                    <li class="active">Hello from here</li>
                    <li>hello from here</li>
                    <li>Hello from hereq</li>
                    <li>hello from herea</li>
                    <li>Hello from here</li>
                    <li>hello from herse</li>
                    <li>Last row</li>
                </ul>
            </div>
        </div>
    </div>
</div>