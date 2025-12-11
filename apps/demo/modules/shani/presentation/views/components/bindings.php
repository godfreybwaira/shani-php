<h3>Data Bindings</h3>
<div class="divider">propbind</div>
<div class="row">
    <div class="col">
        <div class="input-group">
            <label>Price:</label>
            <input type="text" id="unitprice" value="1300.00" readonly
                   shani-on="load::numberformat input:@value&prefix:@currency&output:value">
            <label>Qty:</label>
            <input type="text" id="plus10" value="0" class="width-md-2" readonly
                   data-suffix="kg" shani-on="compute::numbercalc
                   lvalue:@value&output:value&rvalue:@data-base&operator:@data-sign;
                   numbercalc::numberformat
                   input:@value&mindecimals:@data.mindec&suffix:@data-suffix&output:value;
                   numberformat::trigger update>>#totalprice;">
            <label>Total Price:</label>
            <input type="text" id="totalprice" data-summed readonly data-sign="*" class="width-md-2" data-suffix="/="
                   shani-on="load::trigger update&@data-base:#unitprice@value;
                   update::numbercalc
                   lvalue:#plus10@value&output:value&rvalue:@data-base&operator:@data-sign;
                   numbercalc::numberformat
                   input:@value&mindecimals:@data.mindec&output:value&prefix:@currency&suffix:@data-suffix;
                   numberformat::trigger update>>#vat;">
            <label>VAT (18%):</label>
            <input type="text" id="vat" readonly data-sign="*" data-vat="0.18" data-summed
                   class="width-md-2" data-suffix="/="
                   shani-on="update::numbercalc
                   lvalue:#totalprice@value&output:value&rvalue:@data-vat&operator:@data-sign;
                   numbercalc::numberformat
                   input:@value&mindecimals:@data.mindec&output:value&prefix:@currency&suffix:@data-suffix;
                   numberformat::trigger update>>#total;">
            <label>TOTAL:</label>
            <input type="text" id="total" readonly data-sign="+" class="width-md-2" data-suffix="/="
                   shani-on="update::numbercalc
                   lvalue:#totalprice@value&output:value&rvalue:#vat@value&operator:@data-sign;
                   numbercalc::numberformat input:@value&mindecimals:@data.mindec&output:value
                   &prefix:@currency&suffix:@data-suffix;">
        </div>
    </div>
</div>

<div class="row" id="par1" data-bind="click::trigger compute&#plus10@data-sign:@data-sign&#plus10@data-base:@data-base>>#plus10">
    <div class="col">
        <button class="button color-alert" data-sign="+" data-base="10" shani-on="#par1@data-bind">
            Add 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="-" data-base="10" shani-on="#par1@data-bind">
            Minus 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="*" data-base="10" shani-on="#par1@data-bind">
            Times 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="/" data-base="10" shani-on="#par1@data-bind">
            Divide By 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="%" data-base="5" shani-on="#par1@data-bind">
            Reminder By 5
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="^" data-base="2" shani-on="#par1@data-bind">
            Power 2
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="+" data-base="10%" shani-on="#par1@data-bind">
            Add 10%
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="-" data-base="10%" shani-on="#par1@data-bind">
            Minus 10%
        </button>
    </div>
</div>
<h4>Toggle binding</h4>
<div class="divider">Extras</div>
<div class="row">
    <div class="col">
        <ul class="list">
            <li>
                <label>
                    <input type="checkbox" class="toggle" id="binder" shani-ona="input::propbind
                           [data-check1]@checked:@!checked&[data-check2]@checked:@checked
                           &[data-check3]@checked:@checked;">
                    Check Me
                </label>
            </li>
        </ul>
    </div>
    <div class="col">
        <ul class="list">
            <li><label><input type="checkbox" class="checkmark" data-check1> Forward Binding</label></li>
            <li><label><input type="checkbox" class="checkmark" data-check1> Forward Binding</label></li>
            <li><label><input type="checkbox" class="checkmark" data-check2> Reverse Binding</label></li>
            <li><label><input type="checkbox" class="checkmark" data-check2> Reverse Binding</label></li>
            <li>
                <label>
                    <input type="checkbox" class="checkmark" data-check3 shani-ona="input::propbind
                           #binder@checked:@checked;">
                    Double Binding
                </label>
            </li>
        </ul>
    </div>
</div>