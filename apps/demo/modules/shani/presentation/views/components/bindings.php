<h3>Data Bindings</h3>
<div class="row">
    <div class="col">
        <div class="input-group" id="acc1">
            <label>Price:</label>
            <input type="text" id="unitprice" value="1300.00" readonly data-prefix="@currency" data-suffix="@money-suffix"
                   shani-on="load->number.format @numformatter;number.format->util.affix @numberaffix;">
            <label>Qty:</label>
            <input type="text" id="plus10" value="0" class="width-md-2" readonly
                   data-suffix="kg" shani-on="compute->number.calc
                   lvalue:@value&output:value&rvalue:@data-base&operator:@data-sign;
                   number.calc->number.format @numformatter;number.format->util.affix @numberaffix;
                   util.affix->util.trigger update>>#totalprice;">
            <label>Total Price:</label>
            <input type="text" id="totalprice" readonly data-sign="*" class="width-md-2"
                   data-suffix="@money-suffix" data-prefix="@currency" shani-on="load->util.trigger update;
                   update->prop.bind data-base:#unitprice@value;
                   prop.bind->number.calc
                   lvalue:#plus10@value&output:value&rvalue:@data-base&operator:@data-sign;
                   number.calc->number.format @numformatter;number.format->util.affix @numberaffix;
                   util.affix->util.trigger update>>#vat;">
            <label>VAT (18%):</label>
            <input type="text" id="vat" readonly data-sign="*" data-vat="0.18"
                   class="width-md-2" data-suffix="@money-suffix" data-prefix="@currency"
                   shani-on="update->number.calc
                   lvalue:#totalprice@value&output:value&rvalue:@data-vat&operator:@data-sign;
                   number.calc->number.format @numformatter;number.format->util.affix @numberaffix;
                   util.affix->util.trigger update>>#total;">
            <label>TOTAL:</label>
            <input type="text" id="total" readonly data-sign="+" class="width-md-2"
                   data-suffix="@money-suffix" data-prefix="@currency"
                   shani-on="update->number.calc
                   lvalue:#totalprice@value&output:value&rvalue:#vat@value&operator:@data-sign;
                   number.calc->number.format @numformatter;number.format->util.affix @numberaffix;">
        </div>
    </div>
</div>
<div class="row" id="par1" data-bind="
     click->prop.bind #plus10@data-sign:@data-sign&#plus10@data-base:@data-base;
     prop.bind->util.trigger compute>>#plus10,#results">
    <div class="col">
        <button class="button color-alert" data-sign="-" data-base="10" shani-on="#par1@data-bind">
            Minus 10
        </button>
    </div>
    <div class="col">
        <button class="button color-alert" data-sign="+" data-base="10" shani-on="#par1@data-bind">
            Add 10
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
                shani-on="compute->util.trigger click;
                click->number.accumulate
                initial:0&input:@value&operator:+&output:textContent>>#acc1 input;
                number.accumulate->number.format
                input:@textContent&mindecimals:@data.mindec&output:textContent;
                number.format->util.affix input:@textContent&output:textContent&prefix:@currency&suffix:@data-suffix">
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
                    <input type="checkbox" class="toggle" id="binder" shani-on="input->prop.bind
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
                    <input type="checkbox" class="checkmark css1 css2" data-check3 shani-on="input->prop.bind
                           #binder@checked:@checked;prop.bind->csstoggle css1:cls1&css2:cls2">
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
                   shani-on="keyup->prop.bind #text1b@value:@value">
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
                   shani-on="keyup->prop.bind #text2b@value:@value">
            <label>Output:</label>
            <input type="text" id="text2b" placeholder="See the result..."
                   shani-on="keyup->prop.bind #text2a@value:@value">
        </div>
    </div>
</div>
<h4>More bindings...</h4>
<div class="row row-stretch">
    <div class="col">
        <label>
            <input type="checkbox" class="toggle" shani-on="input->prop.bind
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
                   shani-on="keyup->prop.bind .output@textContent:@value;
                   prop.bind->util.trigger alter>>.output">
        </div>
    </div>
    <div class="col">
        <div class="output" data-prefix='He said, "' data-suffix='!"'
             shani-on="alter->util.affix input:@textContent&output:textContent&prefix:@data-prefix&suffix:@data-suffix">
            hey
        </div>
        <div class="output">
            hey
        </div>
        <div class="output" shani-on="alter->util.call input:@textContent&output:textContent&fn:ucase">
            hey
        </div>
    </div>
</div>
<div class="row row-stretch">
    <div class="col">
        <div class="input-group">
            <label>Countdown:</label>
            <input type="text" readonly placeholder="Click to count down..." value="10"
                   shani-on="click delay:0.2s&limit:10&steps:1s->number.compare lvalue:@value&rvalue:0&operator:gt;
                   number.compare->number.calc lvalue:@value&output:value&rvalue:-1&operator:+">
        </div>
    </div>
    <div class="col">
        <div class="input-group">
            <label>Date:</label>
            <input type="text" readonly data-val="2024-12-24T12:42:51.424Z" data-unit="d"
                   shani-on="click->date.diff rvalue:@data-val&output:value&unit:@data-unit">
        </div>
    </div>
</div>
<div class="row row-stretch">
    <div class="col">
        <div class="input-group">
            <label>Char Insertion</label>
            <input type="text" data-dash="-" data-lbr="(" data-rbr=")" maxlength="16"
                   shani-on="input name:--set-pos1->char.insert pos:1&char:@data-lbr&input:@value&output:value;
                   --set-pos1 name:--set-pos5->char.insert pos:5&char:@data-rbr&input:@value&output:value;
                   --set-pos5 name:--set-pos9->char.insert pos:9&char:@data-dash&input:@value&output:value;
                   --set-pos9 name:--set-pos13->char.insert pos:13&char:@data-dash&input:@value&output:value;"
                   placeholder="Start typing or paste some contennt...">
        </div>
    </div>
</div>
<h4>Random Generators</h4>
<div class="row">
    <div class="col">
        <div class="input-group">
            <button class="button color-alert" shani-on="click->util.trigger load>>#rint">
                Random Int
            </button>
            <input type="number" id="rint" min="10" max="99" readonly placeholder="From 10 to 99"
                   shani-on="load->random.int min:@min&max:@max&output:value">
            <button class="button color-alert" shani-on="click->util.trigger load>>#rfloat">
                Random Float
            </button>
            <input type="text" id="rfloat" min="1.0" max="2.9" readonly placeholder="From 1.0 to 2.9"
                   shani-on="load->random.float min:@min&max:@max&output:value;
                   random.float->number.format @numformatter">
            <button class="button color-alert" shani-on="click->util.trigger load>>#rdate">
                Random Date
            </button>
            <input type="text" id="rdate" min="2022-10-12" max="2024-12-01" readonly placeholder="From 2022-10-12 to 2024-12-01"
                   shani-on="load->random.date min:@min&max:@max&output:value">
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="input-group">
            <button class="button color-alert" shani-on="click->util.trigger load>>#rval">
                Random Value
            </button>
            <input type="text" id="rval" data-values="monday,tuesday,wednesday,thursday,friday"
                   shani-on="load->random.value values:@data-values&output:value">
            <button class="button color-alert" shani-on="click->util.trigger load>>#rstr">
                Random String
            </button>
            <input type="text" id="rstr" shani-on="load->random.str output:value;">
            <button class="button color-alert" shani-on="click->util.trigger load>>#rphone">
                Random Phone Number
            </button>
            <input type="text" data-dash="-" data-lbr="(" data-rbr=")" maxlength="16" id="rphone"
                   readonly data-prefix="+" min="12345678900" max="99999999999"
                   shani-on="load->random.int min:@min&max:@max&output:value;
                   random.int->util.affix input:@value&output:value&prefix:@data-prefix;
                   util.affix name:--set-pos1->char.insert pos:1&char:@data-lbr&input:@value&output:value;
                   --set-pos1 name:--set-pos5->char.insert pos:5&char:@data-rbr&input:@value&output:value;
                   --set-pos5 name:--set-pos9->char.insert pos:9&char:@data-dash&input:@value&output:value;
                   --set-pos9 name:--set-pos13->char.insert pos:13&char:@data-dash&input:@value&output:value;">
        </div>
    </div>
</div>