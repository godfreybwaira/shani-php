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
        <div class="tab tab-pos-t">
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
                <button class="button color-theme size-md">
                    Click Me
                </button>
                <label for="toggle">
                    <input class="toggle input-mask" type="checkbox" name="choose" id="toggle">
                    Choose Me
                </label>
                <div class="choice-group input-mask size-md dir-h">
                    <label>
                        <input class="choice" type="radio" name="choice">
                        <span>Jan</span>
                    </label>
                    <label>
                        <input class="choice" checked type="radio" name="choice">
                        <span>Feb</span>
                    </label>
                    <label>
                        <input class="choice" type="radio" name="choice">
                        <span>March</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>