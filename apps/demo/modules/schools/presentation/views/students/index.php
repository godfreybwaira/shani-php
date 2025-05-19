<style type="text/css">
    .grid-layout{
        gap: 1rem;
        grid-template-areas:
            "header header header"
            "main main main"
            "footer footer section";
    }
    .grid-layout>div{
    }
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
<div class="grid-layout color-theme"></div>
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
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla a mollitia tenetur voluptate esse nostrum saepe aut. Iure rem placeat maxime beatae ipsam amet porro officia minima, consequatur, velit. Similique.</p>
            </div>
        </div>
    </div>
</div>