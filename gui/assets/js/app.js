/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('*', {
        'shani-headers': 'x-request-mode:async'
    });
    Shani.action('ucase', (a) => a.emitter.value = a.emitter.value.toUpperCase());
    Shani.action('formatter', (obj) => obj.emitter.innerHTML = obj.body);
    Shani.action('inf', (obj) => new FormData());
    Shani.action('test', (obj) => {
        return false; //try to return true...
    });
    Shani.action('hello', (target) => console.log('hello'));
});