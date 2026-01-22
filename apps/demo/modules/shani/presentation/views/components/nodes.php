<h3>Node Manipulation</h3>
<div class="divider">Node Copy</div>
<div class="col" id="ncopy" data-pos="0" shani-on="--copy->node.copy pos:@data-pos>>#copyhere">
    <button class="button color-info">
        Item 1
    </button>
</div>
<div class="row" id="copyhere">
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
        <button class="button color-danger" shani-on="click->util.trigger --copy>>#ncopy">
            Click to Copy (Pos 3)
        </button>
    </div>
</div>
<div class="divider">Node Move</div>
<div class="col" id="nmove" data-pos="0" shani-on="--move->node.move pos:@data-pos>>#movehere">
    <button class="button color-info">
        Item 1
    </button>
</div>
<div class="row" id="movehere">
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
        <button class="button color-danger" shani-on="click->util.trigger --move>>#nmove">
            Click to Move (Pos 3)
        </button>
    </div>
</div>
<div class="divider">Node Replace</div>
<div class="row">
    <div class="col" id="nreplace" shani-on="--replace->node.replace>>.replaceme">
        <button class="button color-alert">
            Item 1
        </button>
    </div>
    <div class="col replaceme">
        <button class="button color-success">
            Item 2
        </button>
    </div>
    <div class="col replaceme">
        <button class="button color-info">
            Item 3
        </button>
    </div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-danger" shani-on="click->util.trigger --replace>>#nreplace">
            Click to Replace
        </button>
    </div>
</div>
<div class="divider">Node Swap</div>
<div class="row">
    <div class="col" id="nswap" shani-on="--swap->node.swap>>.swapme">
        <button class="button color-danger">
            Item 1
        </button>
    </div>
    <div class="col swapme">
        <button class="button color-success">
            Item 2
        </button>
    </div>
</div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-alert" shani-on="click->util.trigger --swap>>#nswap">
            Click to Swap
        </button>
    </div>
</div>
<div class="divider">Node Walk</div>
<div class="row">
    <div class="col" id="nwalk" shani-on="--walk->node.walk direction:@data-dir" data-dir="next">
        <button class="button color-danger">
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
        <button class="button color-info" shani-on="click->prop.bind #nwalk@data-dir:next;prop.bind->util.trigger --walk>>#nwalk">
            Go Right
        </button>
        <button class="button color-success" shani-on="click->prop.bind #nwalk@data-dir:prev;prop.bind->util.trigger --walk>>#nwalk">
            Go Left
        </button>
    </div>
</div>
<div class="divider">Node Remove</div>
<div class="row" id="nremove" shani-on="--remove->node.rmv">
    <div class="col">
        <button class="button color-info">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-info">
            Item 2
        </button>
    </div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-alert" shani-on="click->util.trigger --remove>>#nremove">
            Remove
        </button>
    </div>
</div>
<div class="divider">Node Clear</div>
<div class="row" id="nclear" shani-on="--clear->node.clear">
    <div class="col">
        <button class="button color-success">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-success">
            Item 2
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