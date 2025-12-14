<h3>Data Bindings</h3>
<div class="row">
    <div class="col">
        <div class="input-group" id="acc1">
            <label>Price:</label>
            <input type="text" id="unitprice" value="1300.00" readonly data-prefix="@currency" data-suffix="@money-suffix"
                   shani-on="load::numberformat @numformatter;numberformat::affix @numberaffix;">
            <label>Qty:</label>
            <input type="text" id="plus10" value="0" class="width-md-2" readonly
                   data-suffix="kg" shani-on="compute::numbercalc
                   lvalue:@value&output:value&rvalue:@data-base&operator:@data-sign;
                   numbercalc::numberformat @numformatter;numberformat::affix @numberaffix;
                   affix::trigger update>>#totalprice;">
            <label>Total Price:</label>
            <input type="text" id="totalprice" data-summed readonly data-sign="*" class="width-md-2"
                   data-suffix="@money-suffix" data-prefix="@currency" shani-on="load::trigger update;
                   update::propbind data-base:#unitprice@value;
                   propbind::numbercalc
                   lvalue:#plus10@value&output:value&rvalue:@data-base&operator:@data-sign;
                   numbercalc::numberformat @numformatter;numberformat::affix @numberaffix;
                   affix::trigger update>>#vat;">
            <label>VAT (18%):</label>
            <input type="text" id="vat" readonly data-sign="*" data-vat="0.18" data-summed
                   class="width-md-2" data-suffix="@money-suffix" data-prefix="@currency"
                   shani-on="update::numbercalc
                   lvalue:#totalprice@value&output:value&rvalue:@data-vat&operator:@data-sign;
                   numbercalc::numberformat @numformatter;numberformat::affix @numberaffix;
                   affix::trigger update>>#total;">
            <label>TOTAL:</label>
            <input type="text" id="total" readonly data-sign="+" class="width-md-2"
                   data-suffix="@money-suffix" data-prefix="@currency"
                   shani-on="update::numbercalc
                   lvalue:#totalprice@value&output:value&rvalue:#vat@value&operator:@data-sign;
                   numbercalc::numberformat @numformatter;numberformat::affix @numberaffix;">
        </div>
    </div>
</div>

<div class="row" id="par1" data-bind="
     click::propbind #plus10@data-sign:@data-sign&#plus10@data-base:@data-base;
     propbind::trigger compute>>#plus10,#results">
    <div class="col">
        <button class="button color-alert" shani-debug="true" data-sign="+" data-base="10" shani-on="#par1@data-bind">
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
    <div class="col">
        <button class="button color-alert" data-suffix="@money-suffix" id="results"
                shani-on="compute::trigger click;
                click::numberaccumulate
                initial:0&input:@value&operator:+&output:textContent>>#acc1 input;
                numberaccumulate::numberformat
                input:@textContent&mindecimals:@data.mindec&output:textContent;
                numberformat::affix input:@textContent&output:textContent&prefix:@currency&suffix:@data-suffix">
            Result
        </button>
    </div>
</div>
<h4>Toggle binding</h4>
<div class="row">
    <div class="col">
        <ul class="list">
            <li>
                <label>
                    <input type="checkbox" class="toggle" id="binder" shani-on="input::propbind
                           [data-check1]@checked:@checked&[data-check2]@checked:@!checked
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
                    <input type="checkbox" class="checkmark css1 css2" data-check3 shani-on="input::propbind
                           #binder@checked:@checked;propbind::csstoggle css1:cls1&css2:cls2">
                    Double Binding
                </label>
            </li>
        </ul>
    </div>
</div>
<h4>One way binding</h4>
<div class="row row-stretch">
    <div class="col">
        <div class="input-group">
            <label>Input:</label>
            <input type="text" placeholder="Write something..."
                   shani-on="keyup::propbind #text1b@value:@value">
            <label>Output:</label>
            <input type="text" id="text1b" placeholder="See the result...">
        </div>
    </div>
</div>
<h4>Two ways binding</h4>
<div class="row row-stretch">
    <div class="col">
        <div class="input-group">
            <label>Input:</label>
            <input type="text" id="text2a" placeholder="Write something..."
                   shani-on="keyup::propbind #text2b@value:@value">
            <label>Output:</label>
            <input type="text" id="text2b" placeholder="See the result..."
                   shani-on="keyup::propbind #text2a@value:@value">
        </div>
    </div>
</div>
<h4>More bindings...</h4>
<div class="row row-stretch">
    <div class="col">
        <label>
            <input type="checkbox" class="toggle" shani-on="input::propbind
                   option[value=TZ]@selected:@checked;">
            Tanzania
        </label>
    </div>
    <div class="col">
        <div class="input-group">
            <label>Select a country</label>
            <select>
                <option value="">My country is...</option>
                <option value="TZ">Tanzania</option>
                <option value="KE">Kenya</option>
                <option value="UG">Uganda</option>
                <option value="RW">Rwanda</option>
            </select>
        </div>
    </div>
</div>
<h4>Even more bindings...</h4>
<div class="row row-stretch">
    <div class="col">
        <div class="input-group">
            <label>Input:</label>
            <input type="text" placeholder="Write something..."
                   shani-on="keyup::propbind .output@textContent:@value;
                   propbind::trigger alter>>.output">
        </div>
    </div>
    <div class="col">
        <div class="output" data-prefix='He said, "' data-suffix='!"'
             shani-on="alter::affix input:@textContent&output:textContent&prefix:@data-prefix&suffix:@data-suffix">
            hey
        </div>
        <div class="output">
            hey
        </div>
        <div class="output" shani-debug="true"
             shani-on="alter::transform input:@textContent&output:textContent&transformer:ucase">
            hey
        </div>
    </div>
</div>
