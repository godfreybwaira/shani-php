<h3>Data Bindings</h3>
<div class="divider">propbind</div>
<div class="row">
    <div class="col">
        <div class="input-group">
            <label>Unit Price:</label>
            <input type="text" id="unitprice" value="$100" readonly>
            <label>Qty:</label>
            <input type="text" id="plus10" value="1" class="width-md-2"
                   shani-on="numberformat::trigger update>>#totalprice;
                   numberbind::numberformat invalue:value&mindecimals:2;
                   compute::numberbind
                   invalue:value&outvalue:value&basevalue:data-base&operator:data-sign;">
            <label>Total Price:</label>
            <input type="text" id="totalprice" readonly data-sign="*" class="width-md-2"
                   shani-log="true"
                   shani-on="load::propbindthis data-base:value>>#unitprice;
                   numberbind::numberformat invalue:value&mindecimals:2;
                   numberformat::trigger update>>#vat;
                   propbindthis::trigger update;
                   update::numberbind
                   invalue:value&outvalue:value&basevalue:data-base&operator:data-sign>>#plus10;">
            <label>VAT (18%):</label>
            <input type="text" id="vat" readonly data-sign="*" data-vat="0.18" class="width-md-2"
                   shani-on="numberbind::numberformat invalue:value&mindecimals:2;
                   numberformat::trigger update>>#total;
                   update::numberbind
                   invalue:value&basevalue:data-vat&operator:data-sign>>#totalprice;">
            <label>GRAND TOTAL:</label>
            <input type="text" id="total" readonly data-sign="*" data-vat="0.18" class="width-md-2"
                   shani-on="update::numbersum invalue:value&outvalue>>#vat,#totalprice;
                   numbersum::numberformat invalue:value&mindecimals:2;">
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-alert" data-sign="+" data-base="10"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Add 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="-" data-base="10"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Minus 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="*" data-base="10"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Times 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="/" data-base="10"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Divide By 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="%" data-base="5"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Reminder By 5
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="^" data-base="2"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Power 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="+" data-base="10%"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Add 10%
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="-" data-base="10%"
                shani-on="click::propbind data-sign&data-base>>#plus10;
                propbind::trigger compute>>#plus10">
            Minus 10%
        </button>
    </div>
</div>
<div class="divider">Buttons</div>
<div class="row">
    <div class="col" id="row1">
        <button class="button accent-color" shani-on="change::nodecopyto pos:1>>#r22" id="btn1">
            Simple Button
        </button>
    </div>
    <div class="col width-sm-1">
        <button class="button button-times">&times;</button>
    </div>
    <div class="col">
        <button class="button button-type-outline">Outline Button</button>
    </div>
    <div class="col width-sm-max width-md-1q">
        <button class="button button-block color-success">
            Button Block
        </button>
    </div>
    <div class="col">
        <button class="button accent-color container">
            Button with empty Badge
            <span class="badge color-danger pos-tr badge-empty">9+</span>
        </button>
    </div>
    <div class="col">
        <button class="button accent-color container">
            Button with Badge
            <span class="badge color-danger pos-tr">9+</span>
        </button>
    </div>
</div>
<div class="divider">Loading Inputs</div>
<div class="row row-stretch" id="r22">
    <div class="col">
        <button class="button color-alert loader-spin">
            Loading...
        </button>
    </div>
    <div class="col">
        <button class="button color-alert loader-bottom">
            Bottom loading...
        </button>
    </div>
    <div class="col">
        <button class="button color-alert loader-top">
            Top loading...
        </button>
    </div>
    <div class="col">
        <div class="choice-group loader-spin">
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Jan</span>
            </label>
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Feb</span>
            </label>
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Mar</span>
            </label>
        </div>
    </div>
    <div class="col">
        <div class="input-group loader-spin">
            <label>www.</label>
            <input type="url" name="website" placeholder="My website...">
            <label>.com</label>
        </div>
    </div>
    <div class="col">
        <div class="input-group loader-spin">
            <input placeholder="Please enter your name..." type="text" name="name">
        </div>
    </div>
</div>
<div class="row row-stretch">
    <div class="col">
        <div class="input-line loader-spin">
            <!--order matters-->
            <input type="text" name="name" placeholder="E.g: Misas Miubka">
            <label class="font-sm">Your name</label>
        </div>
    </div>
    <div class="col">
        <div class="input-line loader-bottom">
            <!--order matters-->
            <input type="text" name="name" placeholder="E.g: Misas Miubka">
            <label>Your name</label>
        </div>
    </div>
    <div class="col">
        <label>Multi-select Input</label>
        <div class="choice-group loader-bottom">
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Jan</span>
            </label>
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Feb</span>
            </label>
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Mar</span>
            </label>
        </div>
    </div>
</div>
<div class="divider">Toggle Buttons</div>
<div class="row row-no-gap row-stretch">
    <div class="col">
        <label>
            <input class="toggle" type="checkbox" id="chb"
                   shani-on="change::propbind checked>>#chb2;
                   propbind::trigger change>>#two,#row1 #btn1">
            Toggle
        </label>
    </div>
    <div class="col">
        <label>
            <input type="color" class="checkmark">
            Color
        </label>
    </div>
    <div class="col">
        <label>
            <input class="checkmark" type="checkbox" id="chb2"
                   shani-on="change::propbind checked>>#chb;
                   propbind::trigger propbind>>#chb">
            Checkbox
        </label>
    </div>
    <div class="col">
        <label>
            <input class="checkmark" name="chl" type="radio">
            Choose A
        </label>
        <label>
            <input class="checkmark" name="chl" type="radio">
            Choose B
        </label>
    </div>
    <div class="col">
        <label>
            <input class="toggle input-mask" type="checkbox">
            Toggle with mask
        </label>
    </div>
</div>
<div class="divider">Choice inputs</div>
<div class="row row-no-gap row-stretch">
    <div class="col">
        <label>Multi-select Input</label>
        <div class="choice-group">
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Jan</span>
            </label>
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Feb</span>
            </label>
            <label>
                <input class="choice" type="checkbox" name="choice">
                <span>Mar</span>
            </label>
        </div>
    </div>
    <div class="col width-sm-max width-md-1q">
        <label>Single-select Input</label>
        <div class="choice-group">
            <label>
                <input class="choice" type="radio" name="gender">
                <span>Male</span>
            </label>
            <label>
                <input class="choice" checked type="radio" name="gender">
                <span>female</span>
            </label>
        </div>
    </div>
    <div class="col">
        <label>Choice Input With mask</label>
        <div class="choice-group input-mask" data-label="Choose a Color">
            <label>
                <input class="choice" type="radio" name="color">
                <span>Blue</span>
            </label>
            <label>
                <input class="choice" checked type="radio" name="color">
                <span>Brown</span>
            </label>
        </div>
    </div>
</div>
<div class="divider">Input Groups</div>
<div class="row row-no-gap row-stretch">
    <div class="col width-sm-max width-md-4">
        <label class="font-sm">Your Website</label>
        <div class="input-group">
            <label>www.</label>
            <input type="url" id="url" data-percent="-18%" shani-on="keyup::trigger yes>>#bas;
                   reverse::numberbind
                   thatvalue:value&thisvalue:data-percent&result:value&precision:5&format:true&operator:->>#bas"
                   name="website" placeholder="My website...">
           <!--<input type="url" id="url" shani-on="keyup::propbind value>>#bas;propbind::trigger yes>>#lbl1" name="website" placeholder="My website...">-->
            <label>.com</label>
        </div>
    </div>
    <div class="col width-md-8 width-sm-max">
        <label class="font-sm">Login</label>
        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username">
            <label>Password</label>
            <input type="password" name="password">
        </div>
    </div>
</div>
<div class="row row-stretch">
    <div class="col">
        <!--<label id="lbl1" shani-on="yes::propcomputethisby innerHTML:value>>#url">Demonstrating shani-propbind function</label>-->
        <input type="text" name="a" data-percent="18%" id="bas" placeholder="Demonstrating shani-propbind function"
               shani-on="yes::numberbind
               thatvalue:value&thisvalue:data-percent&result:value&precision:5&format:true&operator:+>>#url;
               keyup::trigger reverse>>#url"
               class="input-box">
<!--        <input type="text" name="a" value="1" id="bas" placeholder="Demonstrating shani-propbind function"
               shani-on="keyup::propbind value>>#url;propbind::trigger propbind>>#url" class="input-box">-->
    </div>
</div>
<div class="row row-no-gap row-stretch">
    <div class="col width-sm-max width-md-4">
        <label>Single Input Box</label>
        <div class="input-group">
            <input placeholder="Please enter your name..." type="text" name="name">
        </div>
    </div>
    <div class="col width-sm-max width-md-4">
        <label>Simple Input Box</label>
        <input class="input-box" placeholder="Please enter your location..." type="text" name="location">
    </div>
    <div class="col width-sm-max width-md-4">
        <label>Simple File Input</label>
        <div class="input-group">
            <label>Photo</label>
            <input placeholder="Please upload your picture..." type="file" name="file">
        </div>
    </div>
</div>
<div class="row row-no-gap row-stretch">
    <div class="col width-sm-max width-md-4">
        <div class="input-line">
            <!--order matters-->
            <input type="text" name="name" placeholder="E.g: Misas Miubka">
            <label>Your name</label>
        </div>
    </div>
    <div class="col width-sm-max width-md-4">
        <div class="input-line">
            <!--order matters-->
            <select name="number" id="num" required>
                <option value="">Select a number</option>
                <option value="1">One</option>
                <option value="2" id="two" shani-on="change::propbindthis selected:checked>>#chb">Two</option>
                <option value="3">Three</option>
                <option value="4">Four</option>
            </select>
            <label>Choose a number</label>
        </div>
    </div>
    <div class="col width-sm-max width-md-4">
        <div class="input-line">
            <!--order matters-->
            <input type="file" name="file">
            <label>Upload your photo</label>
        </div>
    </div>
</div>