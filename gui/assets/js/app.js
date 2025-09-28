/* global Shani */

document.addEventListener('shani:init', () => {
    Shani.attr('*', {
        'shani-headers': 'x-request-mode:async'
    });
    Shani.fn.ucase = ($this) => $this.value = $this.value.toUpperCase();
});