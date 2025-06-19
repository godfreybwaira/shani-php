<div class="divider">Buttons</div>
<div class="row row-no-gap row-stretch">
    <div class="col">
        <button class="button color-theme">
            Simple Button
        </button>
    </div>
    <div class="col width-sm-1">
        <button class="button button-times color-danger"></button>
    </div>
    <div class="col">
        <button class="button button-block color-success">
            Button Block
        </button>
    </div>
    <div class="col width-sm-max width-md-max">
        <button class="button color-theme container">
            Button with empty Badge
            <span class="badge color-danger pos-tr badge-empty">9+</span>
        </button>
    </div>
    <div class="col">
        <button class="button color-theme container">
            Button with Badge
            <span class="badge color-danger pos-tr">9+</span>
        </button>
    </div>
</div>
<div class="divider">Toggle Buttons</div>
<div class="row row-no-gap row-stretch">
    <div class="col">
        <label>
            <input class="toggle" type="checkbox">
            Toggle
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
    <div class="col width-sm-max width-md-3">
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
            <input type="url" name="website" placeholder="My website...">
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