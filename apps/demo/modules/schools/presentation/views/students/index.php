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
<div class="toaster color-alert width-sm-10 width-md-4 pos-bc padding-xy">
    Curabitur pretium tincidunt lacus. Nulla gravida orci a odio.
</div>
<div class="modal-background">
    <div class="modal modal-type-c pos-c width-sm-11 width-md-10 height-sm-10">
        <div class="tab tab-pos-b">
            <div class="tab-menu" style="grid-area: a1;">
                <a class="menu-item">Menu One</a>
                <a class="active menu-item dir-v">
                    <i class="mdi mdi-professional-hexagon size-md"></i>
                    <span class="label font-sm">Menu One here</span>
                </a>
                <a class="menu-item dir-v">
                    <i class="mdi mdi-account-box"></i>
                    <span class="label">Menu One here</span>
                </a>
                <a class="menu-item dir-v">
                    <i class="mdi mdi-account-box"></i>
                    <span class="label">Menu One here</span>
                </a>
            </div>
            <div class="tab-body padding-xy" style="grid-area: a2;">
                <ul class="list dir-h borders size-md">
                    <li>
                        <span class="list-title">Maths</span>
                        <div class="list-body">26.9%</div>
                    </li><li>
                        <span class="list-title">English</span>
                        <div class="list-body">54.19%</div>
                    </li><li>
                        <span class="list-title">Civics</span>
                        <div class="list-body">0.9%</div>
                    </li>
                </ul>
                <div class="card size-md shadow-sm dir-h">
                    <div class="card-title">My Title Here</div>
                    <img src="<?= $app->ui()->asset('/img/cross.jpg'); ?>" alt="cross" title="Cross">
                    <div class="card-body">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>