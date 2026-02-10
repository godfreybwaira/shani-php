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
        <button class="button color-danger">
            Item 2
        </button>
    </div>
    <div class="col swapme">
        <button class="button color-success">
            Item 3
        </button>
        <button class="button color-success">
            Item 4
        </button>
    </div>
</div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-alert" shani-on="click->util.trigger --swap>>#nswap">
            <i class="mdi mdi-swap-horizontal-bold"></i> Click to Swap
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
        <button class="button color-success" shani-on="click->prop.bind #nwalk@data-dir:prev;prop.bind->util.trigger --walk>>#nwalk">
            <i class="mdi mdi-arrow-left"></i> Go Left
        </button>
        <button class="button color-info" shani-on="click->prop.bind #nwalk@data-dir:next;prop.bind->util.trigger --walk>>#nwalk">
            Go Right <i class="mdi mdi-arrow-right"></i>
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
            <i class="mdi mdi-trash-can-outline"></i> Remove
        </button>
    </div>
</div>
<div class="divider">Node Empty</div>
<div class="row" id="nempty" shani-on="--empty->node.empty">
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
        <button class="button color-danger" shani-on="click->util.trigger --empty>>#nempty">
            <i class="mdi mdi-trash-can-outline"></i> Empty
        </button>
    </div>
</div>
<div class="divider">Node Sort</div>
<div class="row" id="nsort" data-sort="asc"
     shani-on="--sort->node.sort order:@data-sort&row:.col&input:@textContent>>.sortme;
     --shuffle->node.shuffle">
    <div class="col">
        <button class="button color-alert sortme">
            Item 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert sortme">
            Item 5
        </button>
    </div>
    <div class="col">
        <button class="button color-alert sortme">
            Item 3
        </button>
    </div>
    <div class="col">
        <button class="button color-alert sortme">
            Item 1
        </button>
    </div>
    <div class="col">
        <button class="button color-alert sortme">
            Item 4
        </button>
    </div>
</div>
<div class="row">
    <div class="col">
        <button class="button color-success" shani-on="click->prop.bind #nsort@data-sort:asc;prop.bind->util.trigger --sort>>#nsort">
            <i class="mdi mdi-sort-ascending"></i> Sort Asc
        </button>
    </div>
    <div class="col">
        <button class="button color-success" shani-on="click->prop.bind #nsort@data-sort:desc;prop.bind->util.trigger --sort>>#nsort">
            <i class="mdi mdi-sort-descending"></i> Sort Desc
        </button>
    </div>
    <div class="col">
        <button class="button color-success" shani-on="click->util.trigger --shuffle>>#nsort">
            <i class="mdi mdi-shuffle"></i> Shuffle
        </button>
    </div>
</div>