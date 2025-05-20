<style type="text/css">
    .tab-top{
        grid-template-areas: "a1""a2";
    }
    .tab-bottom{
        grid-template-areas: "a2""a1";
    }
    .tab-right{
        grid-template-areas: "a2 a1";
    }
    .tab-left{
        grid-template-areas: "a1 a2";
    }
</style>
<div class="modal-background">
    <div class="modal modal-center pos-center width-xs-10 height-xs-10">
        <div class="tab tab-top">
            <div class="tab-menu" style="grid-area: a1;">
                <a class="menu-item">Menu One</a>
                <a class="active menu-item menu-item-v">
                    <i class="mdi mdi-account-box icon"></i>
                    <span class="label">Menu One here</span>
                </a>
                <a class="menu-item menu-item-v">
                    <i class="mdi mdi-account-box icon"></i>
                    <span class="label">Menu One here</span>
                </a>
                <a class="menu-item menu-item-v">
                    <i class="mdi mdi-account-box icon"></i>
                    <span class="label">Menu One here</span>
                </a>
            </div>
            <div class="tab-body padding-xy" style="grid-area: a2;">
                <ul class="list list-size-md list-h shadow-sm list-border">
                    <li>Hello from here</li>
                    <li>hello from here</li>
                    <li>Hello from here</li>
                    <li>hello from here</li>
                    <li>Hello from here</li>
                    <li>hello from here</li>
                    <li>Hello from hereq</li>
                    <li>hello from herea</li>
                    <li>Hello from here</li>
                    <li>hello from herse</li>
                </ul>
            </div>
        </div>
    </div>
</div>