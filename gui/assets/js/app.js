/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('[shani-on]', {
        'shani-headers': 'x-request-mode:http.req-mode',
        'shani-http': 'timeout:http.timeout'
    });
    Shani.define('ucase', (a) => a.emitter.value = a.emitter.value.toUpperCase());
    Shani.define('formatter', (obj) => obj.emitter.innerHTML = obj.data.body);
    Shani.define('inf', obj => new FormData());
    Shani.define('data.mindec', 2);
    Shani.define('data.maxdec', 4);
    Shani.define('currency', 'TZS ');
    Shani.define('cache.maxage', '20s');
    Shani.define('cache.name', 'pubcache');
    Shani.define('http.name', 'aaa');
    Shani.define('http.credentials', 'same-origin');
    Shani.define('http.mode', 'cors');
    Shani.define('http.timeout', '2s');
    Shani.define('http.req-mode', 'async');
    Shani.define('timeout', a => console.log('request timed out (408)'));
//    Shani.on('200', e => console.log(e.type));
});