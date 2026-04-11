/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('[shani-on]', {
        'shani-headers': '@default-headers',
        'shani-http': '@default-http'
    });
    Shani.select('a[shani-on]', {'shani-http': '@http-a'});
    Shani.select('form[shani-on]', {'shani-http': '@http-form'});
    ////////////////////////
    Shani.define('default-headers', 'x-request-mode:async');
    Shani.define('default-http', 'timeout:10s');
    Shani.define('default-cache', 'age:@cache.maxage&name:@cache.name');
    Shani.define('cache.maxage', '20s');
    Shani.define('cache.name', 'aaa');
    Shani.define('http-a', 'url:@href');
    Shani.define('http-form', 'url:@action');
    Shani.define('evt-delay', 'delay:0.01s');
    Shani.define('modal-specs', 'id:mdl123&classes:modal modal-type-c width-sm-10 height-sm-10 pos-c&close-btn:pos-tr');
    Shani.define('loader-circle-specs', 'name:loader-spin&size:2.5rem');
    Shani.define('loader-bar-specs', 'name:loader-top&color:red&size:.2rem&thickness:.3rem');
    Shani.define('loader-black', 'name:loader-spin&color:#000&size:2.5rem');
    Shani.define('saved-file', 'name:file.txt&type:text/plain');
    Shani.define('http-name', 'aaa');
    Shani.define('conn', 'name:@http-name');
    //////////////////////////
    Shani.define('currency', 'TZS ');
    Shani.define('money-suffix', '/=');
    Shani.define('numformatter', 'mindecimals:2&maxdecimals:4&@num.io');
    Shani.define('num.io', 'input:@value&output:value');
    Shani.define('numberaffix', 'prefix:@data-prefix&suffix:@data-suffix&@num.io');
    //////////////////////////
    Shani.define('ucase', p => typeof p.input === 'string' ? p.input.toUpperCase() : p.input);
//    Shani.on('data', d => console.log(d.detail));
    Shani.define('data-formatter', d => d.body);
});