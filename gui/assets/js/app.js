/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.attr('*', {
        'shani-headers': 'x-request-mode:async'
    });
    Shani.define('ucase', ($this) => $this.value = $this.value.toUpperCase());
    Shani.define('formatter', (target, src, resp) => target.innerHTML = resp.data);
    Shani.define('inf', (target) => new FormData());
});