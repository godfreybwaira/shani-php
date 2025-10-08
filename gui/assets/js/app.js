/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('*', {
        'shani-headers': 'x-request-mode:async'
    });
    Shani.define('ucase', (a, b, c) => a.value = a.value.toUpperCase());
    Shani.define('formatter', (target, src, resp) => target.innerHTML = resp.body);
    Shani.define('inf', (target) => new FormData());
});