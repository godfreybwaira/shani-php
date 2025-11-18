/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('[shani-on]', {
        'shani-headers': 'x-request-mode:async',
        'shani-http': 'timeout:1.5s'
    });
    Shani.define('ucase', (a) => a.emitter.value = a.emitter.value.toUpperCase());
    Shani.define('formatter', (obj) => obj.emitter.innerHTML = obj.data.body);
    Shani.define('inf', obj => new FormData());
    Shani.define('data.mindec', 2);
    Shani.define('data.maxdec', 4);
    Shani.define('currency', 'TZS ');
    Shani.define('cache.maxage', '15s');
    Shani.define('cache.name', 'pubcache');
    Shani.define('http.name', 'aaa');
    Shani.define('http.credentials', 'same-origin');
    Shani.define('http.mode', 'cors');
    Shani.define('delay-onload', '0.01s');
    //////////////////////////
    Shani.define('timeout', a => console.log('request timed out (408)'));
//    Shani.on('200', e => console.log(e.type));
});