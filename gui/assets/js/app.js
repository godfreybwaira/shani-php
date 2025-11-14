/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('[shani-on]', {
        'shani-headers': 'x-request-mode:async',
        'shani-http': 'timeout:5s'
    });
    Shani.define('ucase', (a) => a.emitter.value = a.emitter.value.toUpperCase());
    Shani.define('formatter', (obj) => obj.emitter.innerHTML = obj.data.body);
    Shani.define('inf', (obj) => new FormData());
    Shani.define('data.mindec', 2);
    Shani.define('data.maxdec', 4);
    Shani.define('currency', 'TZS ');
    Shani.define('timeout', (a) => console.log('request timed out (408)'));
//    Shani.on('200', e => console.log(e.type));
});