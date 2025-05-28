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

<div class="layout-container">
    <div class="layout-menu color-theme">
        <div class="menu-group">
            <a class="menu-item dir-v">
                <i class="mdi mdi-professional-hexagon"></i>
                <span class="font-sm">Professional</span>
            </a>
            <a class="active menu-item dir-v">
                <i class="mdi mdi-account-group"></i>
                <span class="font-sm">All Users</span>
            </a>
            <a class="menu-item dir-v">
                <i class="mdi mdi-account"></i>
                <span class="font-sm">Profile</span>
            </a>
        </div>
        <div class="menu-group">
            <a class="menu-item dir-v">
                <i class="mdi mdi-cog"></i>
                <span class="font-sm">Settings</span>
            </a>
        </div>
    </div>
    <div class="layout-content">
        <div class="content-navbar">
            <div class="dropdown">
                <a href="#" class="menu-item dir-h">
                    <i class="mdi mdi-menu"></i>
                    <span>
                        Shani v2.0.0<i class="mdi mdi-chevron-down"></i>
                    </span>
                </a>
                <div class="dropdown-body shadow-sm">
                    <ul class="list size-md">
                        <li><a href="#">Sometime this could be a long sentence</a></li>
                        <li><a href="#">Module 2</a></li>
                        <li><a href="#">Module 3</a></li>
                    </ul>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="menu-item dir-h">
                    <i class="mdi mdi-account"></i>
                    <span>
                        User<i class="mdi mdi-chevron-down"></i>
                    </span>
                </a>
                <div class="dropdown-body pos-bl shadow-sm">
                    <ul class="list size-md">
                        <li><a href="#">Sometime this could be a long sentence</a></li>
                        <li><a href="#">Module 2</a></li>
                        <li><a href="#">Module 3</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="content-body">
            <ul class="breadcrumb padding-y">
                <li><a href="/"><i class="mdi mdi-home"></i></a></li>
                <li>
                    <div class="dropdown">
                        <a href="#">
                            Guest
                        </a>
                        <div class="dropdown-body shadow-sm">
                            <div class="list size-sm">
                                <a href="#">Class 1</a>
                                <a href="#">Class 2</a>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="dropdown">
                        <a href="#">Accounts</a>
                        <div class="dropdown-body shadow-sm">
                            <ul class="list size-sm">
                                <li><a href="#">Function 1</a></li>
                                <li><a href="#">Function 2</a></li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li title="You are here"><b>Home</b></li>
            </ul>
            <div>
                <div class="tab tab-pos-b">
                    <div class="tab-menu" style="grid-area: a1;">
                        <a class="menu-item dir-v">
                            <i class="mdi mdi-professional-hexagon"></i>
                            <span class="font-sm">Professional</span>
                        </a>
                        <a class="active menu-item dir-v">
                            <i class="mdi mdi-account-group"></i>
                            <span class="font-sm">All Users</span>
                        </a>
                        <a class="menu-item dir-v">
                            <i class="mdi mdi-cog"></i>
                            <span class="font-sm">Settings</span>
                        </a>
                        <a class="menu-item dir-v">
                            <i class="mdi mdi-account"></i>
                            <span class="font-sm">Profile</span>
                        </a>
                    </div>
                    <div class="tab-body padding-xy" style="grid-area: a2;">
                        <div class="tag-group">
                            <a href="#" class="tag color-alert">My awesome tag</a>
                            <a href="#" class="tag color-danger">awesome tag</a>
                            <a href="#" class="tag color-info">My tag</a>
                            <a href="#" class="tag color-success">Tag line</a>
                            <a href="#" class="tag color-disable">Disabled</a>
                        </div>
                        <div class="margin-x">&nbsp;</div>
                        <ul class="breadcrumb">
                            <li><a href="/"><i class="mdi mdi-home"></i></a></li>
                            <li>
                                <div class="dropdown">
                                    <a href="#">
                                        Guest
                                    </a>
                                    <div class="dropdown-body shadow-sm">
                                        <div class="list size-sm">
                                            <a href="#">Class 1</a>
                                            <a href="#">Class 2</a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="dropdown">
                                    <a href="#">Accounts</a>
                                    <div class="dropdown-body shadow-sm">
                                        <ul class="list size-sm">
                                            <li><a href="#">Function 1</a></li>
                                            <li><a href="#">Function 2</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li title="You are here"><b>Home</b></li>
                        </ul>
                        <div class="margin-x">&nbsp;</div>
                        <div class="dropdown">
                            <button class="button color-theme padding-md">
                                Click Me
                            </button>
                            <div class="dropdown-body pos-br shadow-sm size-sm">
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit.<br/>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit.<br/>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit.<br/>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit.<br/>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit.<br/>
                            </div>
                        </div>
                        <div class="divider">divider</div>
                        <a href="#">Link</a>
                        <div class="divider">divider</div>
                        <label for="toggle">
                            <input class="toggle input-mask" type="checkbox" name="choose" id="toggle">
                            Choose Me
                        </label>
                        <div class="choice-group input-mask dir-h" data-label="Choose Month">
                            <label>
                                <input class="choice" type="radio" name="choice">
                                <span>Jan</span>
                            </label>
                            <label>
                                <input class="choice" checked type="radio" name="choice">
                                <span>Feb</span>
                            </label>
                            <label>
                                <input class="choice" checked type="radio" name="choice">
                                <span>Feb</span>
                            </label>
                            <label>
                                <input class="choice" checked type="radio" name="choice">
                                <span>Feb</span>
                            </label>
                            <label>
                                <input class="choice" checked type="radio" name="choice">
                                <span>Feb</span>
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
                        <div class="margin-x">&nbsp;</div>
                        <div class="input-group size-md dir-h">
                            <label>Age</label>
                            <input type="text" name="choice">
                            <label>Years</label>
                            <input type="text" name="choice">
                            <label>Years</label>
                            <select name="gender" id="id">
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="U">Unidentified</option>
                            </select>
                        </div>
                        <div class="divider">divider</div>
                        <div class="input-group size-md dir-h">
                            <input placeholder="Please enter your name..." type="text" name="choice">
                        </div>
                        <div class="margin-x">&nbsp;</div>
                        <input class="input-box padding-md" placeholder="Please enter your name..." type="text" name="choice">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>