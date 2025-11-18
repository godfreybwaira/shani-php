<h3>Data Bindings</h3>
<div class="divider">propbind</div>
<div class="row">
    <div class="col">
        <div class="input-group">
            <label>Price:</label>
            <input type="text" id="unitprice" value="100.00" readonly
                   shani-on="load::numberformat input:@value&prefix:@currency">
            <label>Qty:</label>
            <input type="text" id="plus10" value="0" class="width-md-2" readonly
                   data-suffix="kg" shani-on="compute::numberbind
                   input:@value&output:value&basevalue:@data-base&operator:@data-sign;
                   numberbind::numberformat input:@value&mindecimals:@data.mindec&suffix:@data-suffix;
                   numberformat::trigger update>>#totalprice;">
            <label>Total Price:</label>
            <input type="text" id="totalprice" readonly data-sign="*"
                   class="width-md-2" data-suffix="/="
                   shani-on="load::propbindthis data-base:@value>>#unitprice;
                   propbindthis delay:@delay-onload::trigger update;
                   update::numberbind
                   input:@value&output:value&basevalue:@data-base&operator:@data-sign>>#plus10;
                   numberbind::numberformat input:@value&mindecimals:@data.mindec
                   &prefix:@currency&suffix:@data-suffix;
                   numberformat::trigger update>>#vat;">
            <label>VAT (18%):</label>
            <input type="text" id="vat" readonly data-sign="*" data-vat="0.18"
                   class="width-md-2" data-suffix="/="
                   shani-on="update::numberbind
                   input:@value&basevalue:@data-vat&operator:@data-sign>>#totalprice;
                   numberbind::numberformat input:@value&mindecimals:@data.mindec
                   &prefix:@currency&suffix:@data-suffix;
                   numberformat::trigger update>>#total;">
            <label>TOTAL:</label>
            <input type="text" id="total" readonly data-sign="*" data-vat="0.18"
                   class="width-md-2" data-suffix="/="
                   shani-on="update::numbersum input:@value>>#vat,#totalprice;
                   numbersum::numberformat input:@value&mindecimals:@data.mindec
                   &prefix:@currency&suffix:@data-suffix;">
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