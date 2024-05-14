const ls = localStorage;
document.addEventListener('DOMContentLoaded', function (e) {
    ls.setItem('js', 'console.log("hey");');
    Function(ls.getItem('js'))();
});