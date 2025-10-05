/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.select('*', {
        'shani-headers': 'x-request-mode:async'
    });
    Shani.define('ucase', ($this) => $this.value = $this.value.toUpperCase());
    Shani.define('formatter', (target, src, resp) => target.innerHTML = resp.body);
    Shani.define('inf', (target) => new FormData());
});