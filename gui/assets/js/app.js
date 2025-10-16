/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('[shani-on],[watch-on]', {
        'shani-headers': 'x-request-mode:async',
        'shani-http': 'timeout:5s'
    });
    Shani.action('ucase', (a) => a.emitter.value = a.emitter.value.toUpperCase());
    Shani.action('formatter', (obj) => obj.emitter.innerHTML = obj.data.body);
    Shani.action('inf', (obj) => new FormData());
    Shani.action('test', (obj) => {
        return false; //try to return true...
    });
    Shani.action('timeout', (a) => console.log('request timed out (408)'));
});