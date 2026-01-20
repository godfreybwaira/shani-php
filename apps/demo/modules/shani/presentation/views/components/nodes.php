<h3>Node Manipulation</h3>
<div class="divider">Node Copy</div>
<div class="row" id="ncopy" shani-on="--copy->node.copy pos:@data-pos>>.copyme">
    <div class="col">
        <button class="button color-alert">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 3
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 4
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 5
        </button>
    </div>
</div>
<div class="row">
    <div class="col copyme" data-pos="2">
        <button class="button color-danger" shani-on="click->util.trigger --copy>>#ncopy">
            Click Me (Copy Pos 2)
        </button>
    </div>
    <div class="col copyme" data-pos="4">
        <button class="button color-info">
            Copy Pos 4
        </button>
    </div>
</div>
<div class="divider">Node Move</div>
<div class="row" id="nmove" shani-on="--move->node.move pos:@data-pos>>.moveme">
    <div class="col">
        <button class="button color-alert">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 3
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 4
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 5
        </button>
    </div>
</div>
<div class="row">
    <div class="col moveme" data-pos="2">
        <button class="button color-danger" shani-on="click->util.trigger --move>>#nmove">
            Click Me (Move Pos 2)
        </button>
    </div>
    <div class="col moveme" data-pos="4">
        <button class="button color-info">
            Move Pos 4
        </button>
    </div>
</div>
<div class="divider">Node Replace</div>
<div class="row" id="nreplace" shani-on="--replace->node.replace pos:@data-pos>>.replaceme">
    <div class="col">
        <button class="button color-alert">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 3
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 4
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 5
        </button>
    </div>
</div>
<div class="row">
    <div class="col replaceme" data-pos="2">
        <button class="button color-danger" shani-on="click->util.trigger --replace>>#nreplace">
            Click Me (replace Pos 2)
        </button>
    </div>
    <div class="col replaceme" data-pos="4">
        <button class="button color-info">
            replace Pos 4
        </button>
    </div>
</div>
<div class="divider">Node Swap</div>
<div class="row" id="nswap" shani-on="--swap->node.swap pos:@data-pos>>.swapme">
    <div class="col">
        <button class="button color-alert">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 3
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 4
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 5
        </button>
    </div>
</div>
<div class="row">
    <div class="col swapme" data-pos="2">
        <button class="button color-danger" shani-on="click->util.trigger --swap>>#nswap">
            Click Me (swap Pos 2)
        </button>
    </div>
    <div class="col swapme" data-pos="4">
        <button class="button color-info">
            swap Pos 4
        </button>
    </div>
</div>
<div class="divider">Node Remove</div>
<div class="row" id="nremove" shani-on="--remove->node.rmv">
    <div class="col">
        <button class="button color-alert">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 3
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 4
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 5
        </button>
    </div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-danger" shani-on="click->util.trigger --remove>>#nremove">
            Remove
        </button>
    </div>
</div>
<div class="divider">Node Clear</div>
<div class="row" id="nclear" shani-on="--clear->node.clear">
    <div class="col">
        <button class="button color-alert">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 3
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 4
        </button>
    </div>
    <div class="col">
        <button class="button color-alert">
            Item 5
        </button>
    </div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-danger" shani-on="click->util.trigger --clear>>#nclear">
            Clear
        </button>
    </div>
</div>