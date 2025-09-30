(doc => {
    'use strict';
    doc.addEventListener('DOMContentLoaded', () => {
        Shanify(doc.body);
        Observers.mutate(doc.body);
        if (!window.Shani) {
            USER_DATA.attr = Utils.object();
            USER_DATA.fn = Utils.object();
            window.Shani = Utils.object({
                attr: (selector, obj) => USER_DATA.attr[selector] = Utils.object(obj),
                define: (fnName, fn) => {
                    if (!(fnName in USER_DATA.fn)) {
                        USER_DATA.fn[fnName] = fn;
                    } else {
                        console.warn(fnName + ' already exists.');
                    }
                }
            });
            Object.freeze(window.Shani);
            Object.freeze(USER_DATA);
            doc.dispatchEvent(new Event('shani:init'));
        }
    });
    const USER_DATA = Object.setPrototypeOf({}, null);
    const Observers = (() => {
        const runScript = node => {
            if (node.hasAttribute('src')) {
                const found = doc.head.querySelector('script[src="' + node.src + '"]') !== null;
                if (!found) {
                    doc.head.appendChild(node);
                    node.addEventListener('load', e => Function(node.textContent)());
                }
            } else {
                Function(node.textContent)();
            }
        };
        const addNode = node => {
            if (node instanceof Element) {
                if (node.tagName === 'SCRIPT') {
                    return runScript(node);
                }
                Shanify(node);
            }
        };
        const mo = changes => {
            for (let change of changes) {
                for (let node of change.addedNodes) {
                    addNode(node);
                }
            }
        };
        const demand = function (changes) {
            for (let change of changes) {
                if (change.isIntersecting) {
                    change.target.dispatchEvent(new Event('demand', {bubbles: true}));
                    this.disconnect();
                }
            }
        };
        return {
            mutate(node) {
                new MutationObserver(mo).observe(node, {subtree: true, childList: true});
            }, intersect(node) {
                new IntersectionObserver(demand).observe(node);
            }
        };
    })();
    const Convertor = (() => {
        const json = (data) => typeof data === 'string' ? Utils.object(JSON.parse(data)) : data;
        return {
            map2json(map) {
                const obj = Utils.object();
                for (let m of map) {
                    obj[m[0]] = m[1];
                }
                return obj;
            },
            input2form(node) {
                if (['SELECT', 'INPUT', 'TEXTAREA'].indexOf(node.tagName) > -1) {
                    const name = node.getAttribute('name'), fd = new FormData();
                    if (!node.files) {
                        fd.append(name || 'value', node.value);
                    } else {
                        for (let f = 0; f < node.files.length; f++) {
                            fd.append(name || 'file[]', node.files[f]);
                        }
                    }
                    return fd;
                }
                return node.tagName === 'FORM' ? new FormData(node) : null;
            },
            form2json(fd) {
                const data = Utils.object(), keys = [];
                for (let input of fd) {
                    if (keys.indexOf(input[0]) > -1) {
                        continue;
                    }
                    keys.push(input[0]);
                    const vals = fd.getAll(input[0]), key = input[0].replace(/\[\]/g, '');
                    if (vals.length > 1) {
                        data[key] = [];
                        for (let val of vals) {
                            data[key].push(val);
                        }
                    } else {
                        data[key] = vals[0];
                    }
                }
                return data;
            },
            json2xml(data) {
                const convert = (obj, tag) => {
                    let node = '<' + tag + '>';
                    if (typeof obj === 'object') {
                        const isArray = obj instanceof Array;
                        for (let key in obj) {
                            node += convert(obj[key], isArray ? 'item' : key.replace(/[ ]+/, '-'));
                        }
                    } else {
                        node += obj;
                    }
                    return node + '</' + tag + '>';
                };
                return '<?xml version="1.0"?>' + convert(json(data), 'data');
            },
            json2yaml(data) {
                const convert = (obj, indent) => {
                    let str = '';
                    const isArray = obj instanceof Array;
                    for (let p in obj) {
                        const key = '  '.repeat(indent) + (isArray ? '-' : p + ':');
                        if (typeof obj[p] !== 'object') {
                            str += key + ' ' + obj[p] + '\r\n';
                        } else {
                            str += key + '\r\n' + convert(obj[p], indent + 1);
                        }
                    }
                    return str;
                };
                return convert(json(data), 0).trim();
            },
            json2csv(obj) {
                const enclose = val => {
                    return '"' + (val !== null || val !== undefined ? (val instanceof Array ? val.join('|') : val) : '') + '"';
                };
                obj = json(obj);
                const data = obj instanceof Array ? obj : [obj];
                let str = Object.keys(data[0]).map(enclose).join(',');
                for (let row of data) {
                    const rows = [];
                    for (let col of row) {
                        rows.push(enclose(col));
                    }
                    str += '\r\n' + rows.join(',');
                }
                return str;
            },
            urlencoded(fd) {
                const keys = [];
                let output = '';
                for (let input of fd) {
                    if (keys.indexOf(input[0]) > -1) {
                        continue;
                    }
                    const vals = fd.getAll(input[0]);
                    for (let val of vals) {
                        output += '&' + input[0] + '=' + encodeURIComponent(val);
                    }
                    keys.push(input[0]);
                }
                return output.slice(1);
            },
//            file2json(file) {
//                const fr = new FileReader();
//                fr.readAsDataURL(file);
//                return new Promise(function (ok) {
//                    fr.addEventListener('load', e => {
//                        ok(Utils.object({
//                            name: file.name, size: file.size, type: file.type,
//                            base64: e.target.result.slice(e.target.result.indexOf(',') + 1)
//                        }));
//                    });
//                });
//            },
            form2(fd, type) {
                switch (type) {
                    case 'json':
                        return JSON.stringify(this.form2json(fd));
                    case 'xml':
                        return this.json2xml(this.form2json(fd));
                    case 'yaml':
                        return this.json2yaml(this.form2json(fd));
                    case 'csv':
                        return this.json2csv(this.form2json(fd));
                    case 'x-www-form-urlencoded':
                        return this.urlencoded(fd);
                }
                return fd;
            }
        };
    })();
    const HTML = (() => {
        const INSERT_MODES = {
            prepend: 'afterbegin', append: 'beforeend', replace: 'replace',
            before: 'beforebegin', after: 'afterend', ignore: 'ignore'
        };
        const setInputData = (target, data, mode, mechanism) => {
            if (mode === 'prepend') {
                target.value = data + target.value;
            } else if (mode === 'append') {
                target.value += data;
            } else if (mode === 'replace') {
                target.value = data;
            } else {
                target[mechanism](INSERT_MODES[mode], data);
            }
        };
        const setNodeData = (target, data, mode, mechanism, plainText) => {
            if (mode === 'replace') {
                if (plainText) {
                    target.textContent = data;
                } else {
                    target.innerHTML = data;
                }
            } else {
                target[mechanism](INSERT_MODES[mode], data);
            }
        };
        const insertData = (target, shani, data, mode, headers) => {
            const type = Utils.getSubtype(headers?.get('content-type'));
            const plainText = (target.getAttribute('shani-xss') || shani.xss) === 'true' || type !== 'html';
            const mechanism = 'insertAdjacent' + (plainText ? 'Text' : 'HTML');
            if (Utils.isInput(target)) {
                setInputData(target, data, mode, mechanism);
            } else {
                setNodeData(target, data, mode, mechanism, plainText);
            }
        };
        const handleDataInsertion = (target, shani, resp) => {
            const formatter = target.getAttribute('shani-formatter') || shani.formatter;
            if (formatter) {
                return Utils.recursiveCall(formatter, [target, shani.emitter, resp]);
            }
            const mode = target.getAttribute('shani-insert') || shani.insert || 'replace';
            if (mode === 'ignore') {
                return;
            }
            insertData(target, shani, resp.data || '', mode, resp.headers);
        };
        return {
            processResponse(shani, response) {
                Utils.trigger(shani, 'data', response);
                if (shani.target) {
                    doc.querySelectorAll(shani.target).forEach(target => handleDataInsertion(target, shani, response));
                } else {
                    handleDataInsertion(shani.emitter, shani, response);
                }
            }
        };
    })();
    const Shani = (() => {
        const Obj = function (node, e, attrib) {
            this.event = e;
            this.emitter = node;
            this.poll = Utils.object();
            this.params = Utils.explode(node.getAttribute(attrib), ';');
            this.url = node.getAttribute('href') || node.getAttribute('action') || node.value;
            setAttribs(this, node, Shani.SHANI_ATTR, 'shani-');
            setAttribs(this, node, Shani.HTML_ATTR, '');
        };
        const setAttribs = (shani, node, attrs, prefix) => {
            for (const a of attrs) {
                shani[a] = node.getAttribute(prefix + a) || getGlobalAttr(prefix + a, node);
            }
        };
        const getGlobalAttr = (attr, node) => {
            for (let a in USER_DATA.attr) {
                if (node.matches(a)) {
                    return USER_DATA.attr[a][attr] || null;
                }
            }
            return null;
        };
        /**
         * Make HTTP request at a given interval
         * @param {object} shani
         */
        const countdown = shani => {
            const t = shani.timer.split(':');
            const start = Number(t[0] || 0) * 1000;
            shani.poll.steps = Number(t[1] || -1) * 1000;
            shani.poll.limit = parseInt(t[2]) || null;
            setTimeout(Utils.trigger, start, shani, shani.event.type);
        };
        /**
         * Send HTTP request
         * @param {object} shani
         * @param {string} method HTTP request type
         */
        const sendReq = (shani, method) => {
            if (shani.scheme === 'ws') {
                return WSocket(shani);
            }
            if (shani.scheme === 'sse') {
                return ServerEvent(shani);
            }
            let em = shani.emitter;
            if (em.tagName === 'FORM') {
                em = em.querySelector('fieldset') || em;
            }
            HTTP.send(shani, shani.method || method, request => {
                Utils.trigger(shani, 'start', {request});
                em.setAttribute('disabled', '');
            }, response => {
                em.removeAttribute('disabled');
                Utils.trigger(shani, 'end', response);
            });
        };
        const getTarget = shani => {
            if (!shani.target) {
                return shani.emitter;
            }
            const targets = doc.querySelectorAll(shani.target);
            if (targets.length === 1) {
                return targets[0];
            }
            const wrapper = doc.createElement('div');
            for (const t of targets) {
                wrapper.appendChild(t.cloneNode(true));
            }
            return wrapper;
        };
        const getCover = (shani, size) => {
            let style = 'position:fixed;top:0;left:0;width:100%;height:100%;padding:1rem;';
            style += 'overflow-y:auto;font-size:' + (size || 100) + '%;background:#fff;z-index:998';
            const cover = doc.createElement('div');
            cover.style = style;
            cover.innerHTML = getTarget(shani).outerHTML;
            doc.body.insertBefore(cover, doc.body.firstChild);
            return cover;
        };
        Obj.prototype = {
            /**
             * Read content from server.
             */
            r() {
                if (this.history === 'true') {
                    history.pushState(null, '', this.url);
                }
                sendReq(this, 'GET');
            },
            /**
             * Write content to server
             */
            w() {
                sendReq(this, 'POST');
            },
            trigger(params) {
                for (const val of params) {
                    this.emitter.dispatchEvent(new Event(val.trim(), {bubbles: true}));
                }
            },
            /**
             * Remove node from DOM
             */
            close() {
                const node = !this.target ? this.emitter : Utils.getParentNode(this.emitter, this.target);
                if (node) {
                    return Utils.removeNode(node);
                }
                doc.querySelectorAll(this.target).forEach(node => Utils.removeNode(node));
            },
            print() {
                if (window.print instanceof Function) {
                    const cover = getCover(this);
                    window.print();
                    Utils.removeNode(cover);
                }
            },
            /**
             * Offline search
             */
            search() {
                const text = this.emitter.value.trim().toLowerCase(), target = getTarget(this);
                for (const row of target.children) {
                    row.style.display = row.textContent.toLowerCase().includes(text) ? null : 'none';
                }
            },
            /**
             * Full screen
             */
            fs() {
                if (doc.fullscreenEnabled) {
                    const cover = getCover(this, 135);
                    doc.documentElement.requestFullscreen().then(() => {
                        doc.addEventListener('fullscreenchange', () => {
                            if (!doc.fullscreenElement) {
                                Utils.removeNode(cover);
                            }
                        });
                    }).catch(() => Utils.removeNode(cover));
                }
            },
            copy() {
                const target = getTarget(this);
                if (['INPUT', 'TEXTAREA'].indexOf(target.tagName) > -1) {
                    target.select();
                    doc.execCommand('copy');
                } else {
                    const box = doc.createElement('TEXTAREA');
                    box.style.width = 0;
                    box.style.height = 0;
                    doc.body.appendChild(box);
                    box.value = target.innerText;
                    box.select();
                    doc.execCommand('copy');
                    box.remove();
                }
            },
            rmv(params) {
                if (params) {
                    doc.querySelectorAll(params[0]).forEach(target => Utils.removeNode(target));
                } else {
                    Utils.removeNode(this.emitter);
                }
            },
            moveto(params) {
                const pos = params[0].lastIndexOf(' ');
                const idx = parseInt(params[0].slice(pos + 1));
                const selector = isNaN(idx) ? params[0] : params[0].slice(0, pos);
                const parent = doc.querySelector(selector);
                Actions.moveto(this.emitter, parent, idx);
            },
            cssadd(params) {
                Actions.addcss(this.emitter, params);
            },
            cssrmv(params) {
                Actions.rmcss(this.emitter, params);
            },
            cssreplace(params) {
                Actions.replacecss(this.emitter, params);
            },
            csstoggle(params) {
                Actions.togglecss(this.emitter, params);
            },
            cssaddt(params) {
                doc.querySelectorAll(this.target).forEach(target => Actions.addcss(target, params));
            },
            cssrmvt(params) {
                doc.querySelectorAll(this.target).forEach(target => Actions.rmcss(target, params));
            },
            cssreplacet(params) {
                doc.querySelectorAll(this.target).forEach(target => Actions.replacecss(target, params));
            },
            csstogglet(params) {
                doc.querySelectorAll(this.target).forEach(target => Actions.togglecss(target, params));
            },
            /**
             * Remove properties from extisting node
             * @param {array} params
             */
            proprmv(params) {
                for (const p of params) {
                    const key = p.trim();
                    if (key in this.emitter) {
                        const type = typeof this.emitter[key];
                        this.emitter[key] = type === 'boolean' ? false : type === 'number' ? 0 : '';
                    }
                }
            },
            /**
             * Add properties from extisting node
             * @param {array} params
             */
            prop(params) {
                for (const val of params) {
                    const pos = val.indexOf(':'), key = val.slice(0, pos).trim();
                    const value = val.slice(pos + 1).trim();
                    this.emitter[key] = key === value || value.length === 0 || value;
                }
            },
            /**
             * Map this.emitter properties to that of src element
             * @param {array} params
             */
            propbind(params) {
                if (this.event.detail) {
                    const src = this.event.detail.shani.emitter;
                    for (const val of params) {
                        const pos = val.indexOf(':'), thiskey = val.slice(0, pos).trim();
                        const thatkey = val.slice(pos + 1).trim() || thiskey;
                        this.emitter[thiskey] = src[thatkey];
                    }
                }
            },
            /**
             * Toggle properties from extisting node
             * @param {array} params
             */
            proptoggle(params) {
                for (const val of params) {
                    const key = val.trim(), value = this.emitter[key];
                    this.emitter[key] = typeof value === 'boolean' ? !value : '' || value;
                }
            },
            /**
             * Call user defined function using this node and target node as parameter
             * @param {array} params
             * @param {object} data
             */
            udf(params, data) {
                const pos = params[0].indexOf(' '), fn = pos > -1 ? params[0].slice(0, pos) : params[0];
                const selector = pos > -1 ? params[0].slice(pos + 1).trim() : null;
                const targets = selector ? doc.querySelectorAll(selector) : [null];
                targets.forEach(target => Utils.recursiveCall(fn, [this.emitter, target, data]));
            },
            /**
             * Map target node properties to a function call
             * @param {array} params
             * @param {array} data
             */
            udfbind(params, data) {
                if (this.event.detail) {
                    const src = this.event.detail.shani.emitter;
                    Utils.recursiveCall(params[0], [this.emitter, src, data]);
                }
            }
        };
        window.addEventListener('popstate', e => history.go(0));
        return {
            HTML_ATTR: ['enctype', 'method'],
            SHANI_ATTR: ['watch', 'headers', 'timer', 'insert', 'xss', 'formatter', 'history', 'on', 'scheme', 'target'],
            create(node, event, attrib) {
                if (!node.hasAttribute('disabled')) {
                    const shani = new Obj(node, event, attrib);
                    if (shani.timer) {
                        return countdown(shani);
                    }
                    Utils.trigger(shani, event.type);
                }
            },
            on(e, cb) {
                doc.addEventListener('shani:on:' + e, cb);
                return Shani.on;
            }
        };
    })();
    const Actions = (() => {

        return {
            /**
             * Move this element to a specified position, to another destination.
             * If a position is not given then the element is placed to the end.
             */
            moveto(target, parent, index) {
                if (parent) {
                    const idx = isNaN(index) ? -1 : index, len = parent.children.length + 1;
                    if (Math.abs(idx) <= len && idx !== 0) {
                        const pos = idx > 0 ? idx - 1 : idx + len;
                        parent.insertBefore(target, parent.children[pos]);
                    }
                }
            },
            /**8
             * Add CSS class(es) to extisting node
             */
            addcss(target, params) {
                params.forEach(val => target.classList.add(val.trim()));
            },
            /**
             * Remove CSS class(es) from extisting node
             */
            rmcss(target, params) {
                params.forEach(val => target.classList.remove(val.trim()));
            },
            /**
             * Replace CSS class(es) to extisting node
             */
            replacecss(target, params) {
                for (const val of params) {
                    const pos = val.indexOf(':'), key = val.slice(0, pos);
                    target.classList.replace(key.trim(), val.slice(pos + 1));
                }
            },
            /**
             * Toggle CSS class(es) to extisting node
             */
            togglecss(target, params) {
                params.forEach(val => target.classList.toggle(val.trim()));
            }
        };
    })();
    const Shanify = (() => {
        const listen = e => {
            const node = getTargetNode(e.target.closest('[shani-on]'), e.type);
            if (node) {
                if (['A', 'AREA', 'FORM'].indexOf(node.tagName) > -1) {
                    e.preventDefault();
                }
                Shani.create(node, e, 'shani-on');
            }
        };
        const getTargetNode = (node, evt) => {
            if (node) {
                const values = node.getAttribute('shani-on');
                if (values?.includes(evt + ':')) {
                    return node;
                }
                return getTargetNode(Utils.getParentNode(node, '[shani-on]'), evt);
            }
            return null;
        };
        const setWatchEvents = node => {
            const events = Utils.explode(node.getAttribute('watch-on'), ',');
            for (let e of events) {
                Shani.on(e[0], watch);
            }
        };
        const addListener = node => {
            const events = Utils.explode(node.getAttribute('shani-on'), ',');
            for (let e of events) {
                doc.addEventListener(e[0], listen);
                if (e[0] === 'load') {
                    node.dispatchEvent(new Event(e[0], {bubbles: true}));
                } else if (e[0] === 'demand') {
                    Observers.intersect(node);
                }
            }
        };
        const watch = e => {
            const evt = Utils.getEventName(e.type);
            doc.querySelectorAll('[watch-on]').forEach(watcher => {
                const events = watcher.getAttribute('watch-on');
                if (events?.includes(evt + ':')) {
                    if (e.detail.shani.emitter.matches(watcher.getAttribute('shani-watch'))) {
                        Shani.create(watcher, e, 'watch-on');
                    }
                }
            });
        };
        return root => {
            setWatchEvents(root);
            addListener(root);
            const nodes = root.querySelectorAll('[shani-on],[watch-on]');
            nodes.forEach(node => setWatchEvents(node));
            nodes.forEach(node => addListener(node));
        };
    })();
    const Utils = (() => {
        const callNext = (shani, event, data) => {
            const str = shani.params.get(event)?.trim();
            if (str) {
                const pos = str.indexOf(' '), fn = pos > -1 ? str.slice(0, pos) : str;
                if (shani[fn] instanceof Function) {
                    const params = pos > -1 ? str.slice(pos + 1).split(',') : null;
                    shani[fn](params, data);
                    Utils.trigger(shani, fn);
                }
            }
        };
        const resubmit = shani => {
            if (shani.emitter.isConnected && shani.poll.steps > -1 && (!shani.poll.limit || (--shani.poll.limit) > 0)) {
                setTimeout(Utils.trigger, shani.poll.steps, shani, shani.event.type);
            }
        };
        return {
            removeNode(node) {
                node.style.opacity = 0;
                node.addEventListener('transitionend', () => node.remove());
            },
            selectNode(children, activeChild, cssClass) {
                for (const row of children) {
                    row.classList.remove(cssClass);
                }
                activeChild.classList.add(cssClass);
            },
            getEventName(evt) {
                return evt.slice(evt.lastIndexOf(':') + 1);
            },
            isInput(node) {
                return ['INPUT', 'TEXTAREA'].includes(node.tagName);
            },
            getParentNode(childNode, parentSelector) {
                const parent = childNode.parentElement;
                if (!parent || parent.matches(parentSelector)) {
                    return parent;
                }
                return Utils.getParentNode(parent, parentSelector);
            },
            explode(str, sep = ',') {
                const map = new Map();
                if (str) {
                    const raw = str.trim().split(sep);
                    for (let val of raw) {
                        const pos = val.indexOf(':'), key = pos > 0 ? val.slice(0, pos) : val;
                        map.set(key.toLowerCase().trim(), pos > 0 ? val.slice(pos + 1).trim() : null);
                    }
                }
                return map;
            },
            object(o) {
                return Object.setPrototypeOf(o || {}, null);
            },
            trigger(shani, event, data = {}) {
                const evt = Utils.getEventName(event);
                callNext(shani, evt, data);
                data.shani = shani;
                if (shani.event.detail?.shani?.event?.type !== evt) {
                    doc.dispatchEvent(new CustomEvent('shani:on:' + evt, {detail: Utils.object(data)}));
                }
                if (evt === 'end')
                    resubmit(shani);
            },
            getReqHeaders(shani) {
                const type = Utils.getSubtype(shani.enctype), headers = Utils.explode(shani.headers);
                if (type && type !== 'form-data') {
                    headers.set('content-type', shani.enctype.trim());
                }
                return headers;
            },
            getSubtype(header) {
                if (header) {
                    const subtype = header.slice(header.indexOf('/') + 1).split(';')[0];
                    const plusPos = subtype.indexOf('+');
                    return plusPos < 0 ? subtype : subtype.slice(plusPos + 1);
                }
                return null;
            },
            getId() {
                return Date.now().toString(36);
            },
            recursiveCall(path, args, thisArg = null) {
                const keys = path.split('.');
                const traverse = (obj, index) => {
                    if (index === keys.length - 1) {
                        return obj[keys[index]].apply(thisArg, args);
                    }
                    return traverse(obj[keys[index]], index + 1);
                };
                return traverse(USER_DATA.fn, 0);
            }
        };
    })();
    const HTTP = (() => {
        const getHttpResponse = xhr => {
            const resp = Utils.object({code: xhr.status, status: xhr.statusText});
            if (xhr.readyState >= 4) {
                resp.data = xhr.response;
                resp.headers = Utils.explode(xhr.getAllResponseHeaders(), '\r\n');
            }
            return resp;
        };
        const httpHandler = (shani, xhr, cb) => {
            const on = (e, cb) => xhr.addEventListener(e, cb);
            on('readystatechange', e => {
                if (e.target.readyState === 4) {
                    HTTP.fire(shani, getHttpResponse(xhr), xhr.status);
                }
            });
            on('error', e => {
                if (shani.poll.limit > 0) {
                    shani.poll.limit++;
                }
                HTTP.fire(shani, getHttpResponse(xhr), 400);
            });
            on('abort', e => HTTP.fire(shani, getHttpResponse(xhr), 410));
            on('timeout', e => HTTP.fire(shani, getHttpResponse(xhr), 408));
            on('loadstart', e => HTTP.fire(shani, getHttpResponse(xhr), 102));
            on('loadend', e => cb(getHttpResponse(xhr)));

            xhr.upload.addEventListener('progress', e => {
                if (e.lengthComputable) {
                    const response = getHttpResponse(xhr);
                    response.bytes = Utils.object({loaded: e.loaded, total: e.total});
                    HTTP.fire(shani, response, 102);
                }
            });
        };
        const redirect = headers => {
            const url = headers.get('location');
            url === '#' ? location.reload() : location = url;
        };
        const createPayload = (shani, method) => {
            const fd = Convertor.input2form(shani.emitter);
            const payload = Utils.object({
                url: shani.url, data: null, headers: Utils.getReqHeaders(shani)
            });
            if (fd) {
                if (method.toUpperCase() === 'GET') {
                    const mark = shani.url.indexOf('?') < 0 ? '?' : '&';
                    payload.url = shani.url + mark + Convertor.urlencoded(fd);
                } else {
                    const type = Utils.getSubtype(payload.headers.get('content-type'));
                    payload.data = Convertor.form2(fd, type);
                }
            }
            return payload;
        };
        return {
            send(shani, method, startCb, endCb) {
                const payload = createPayload(shani, method), xhr = new XMLHttpRequest();
                startCb(payload);
                xhr.open(method, payload.url, true);
                for (let h of payload.headers) {
                    xhr.setRequestHeader(h[0], h[1]);
                }
                xhr.send(payload.data);
                httpHandler(shani, xhr, endCb);
            },
            statusText(code) {
                if (code > 199 && code < 300) {
                    return 'success';
                }
                if (code > 299 && code < 400) {
                    return 'redirect';
                }
                if (code > 399 && code < 500) {
                    return 'error';
                }
                return code < 200 ? 'info' : 'offline';
            },
            fire(shani, response, code) {
                const status = HTTP.statusText(code);
                if (!(response.code > 0)) {
                    response.code = code;
                }
                Utils.trigger(shani, '' + code, response);
                Utils.trigger(shani, status, response);
                HTML.processResponse(shani, response);
                if (status === 'redirect') {
                    redirect(response.headers);
                }
            }
        };
    })();
    const WSocket = (() => {
        const createPayload = shani => {
            const payload = Utils.object({
                url: shani.url, data: null, headers: Utils.getReqHeaders(shani)
            });
            const formdata = Convertor.input2form(shani.emitter);
            if (formdata) {
                const type = Utils.getSubtype(payload.headers.get('content-type'));
                payload.data = '{"data":' + Convertor.form2(formdata, type) + ',"headers":';
                payload.data += JSON.stringify(Convertor.map2json(payload.headers)) + '}';
            }
            return payload;
        };
        const httpHandler = (shani, socket) => {
            const on = (e, cb) => socket.addEventListener(e, cb);
            on('open', () => {
                const payload = createPayload(shani);
                Utils.trigger(shani, 'start', {request: payload});
                socket.send(payload.data || '');
            });
            on('message', e => {
                const resp = Utils.object({data: e.data || null, headers: null});
                HTML.processResponse(shani, resp);
            });
            on('error', e => Utils.trigger(shani, e.type));
            on('close', e => Utils.trigger(shani, 'end'));
        };
        return shani => {
            const scheme = location.protocol === 'http:' ? 'ws' : 'wss';
            const host = shani.url.indexOf('://') === -1 ? scheme + '://' + location.host : '';
            httpHandler(shani, new WebSocket(host + shani.url));
        };
    })();
    const ServerEvent = (() => {
        const httpHandler = (shani, sse) => {
            const on = (e, cb) => sse.addEventListener(e, cb);
            const events = Utils.explode(shani.on || 'message');
            Utils.trigger(shani, 'start');
            for (let evt of events) {
                const name = Utils.getEventName(evt[0]);
                on(name, e => {
                    const resp = Utils.object({
                        data: e.data || null, headers: new Map().set('content-type', 'text/html')
                    });
                    HTML.processResponse(shani, resp);
                });
            }
            on('error', e => Utils.trigger(shani, e.type));
            on('beforeunload', () => {
                sse.close();
                Utils.trigger(shani, 'end');
            });
        };
        return shani => httpHandler(shani, new EventSource(shani.url));
    })();
    const UI = (() => {
        const Carousel = (() => {
            const rotateItems = (carousel, cb) => {
                const children = carousel.querySelectorAll('.carousel-body>*');
                const currentActive = carousel.querySelector('.carousel-body>.active');
                const currentIdx = Array.from(children).indexOf(currentActive);
                const nextIdx = cb(children.length, currentIdx);
                Utils.selectNode(children, children[nextIdx], 'active');
            };
            const rotate = () => {
                doc.querySelectorAll('.carousel').forEach(node => {
                    if (node.getAttribute('ui-attr') === 'auto') {
                        rotateItems(node, (total, idx) => (idx + 1) % total);
                    }
                });
                setTimeout(rotate, 5000);
            };
            doc.addEventListener('click', e => {
                if (e.target.classList?.contains('carousel-next')) {
                    // Calculate next index: cycle to 0 if at end.
                    rotateItems(e.target.parentElement, (total, idx) => (idx + 1) % total);
                } else if (e.target.classList?.contains('carousel-prev')) {
                    // Calculate previous index: add total length to avoid negative modulus.
                    rotateItems(e.target.parentElement, (total, idx) => (idx - 1 + total) % total);
                }
            });
            setTimeout(rotate, 5000);
        })();
        const Selection = (() => {
            const select = target => {
                const parent = Utils.getParentNode(target, '.accordion,.menubar');
                if (parent) {
                    const child = getEmittingChild(target, parent);
                    Utils.selectNode(parent.children, child, 'active');
                }
            };
            const getEmittingChild = (target, parent) => {
                while (target !== parent && target.parentElement !== parent) {
                    target = target.parentElement;
                }
                return target;
            };
            doc.addEventListener('click', e => select(e.target));
        })();
        const Modal = (() => {
            const getCloseBtn = (attr, selector) => {
                if (attr !== null) {
                    const position = attr.slice(attr.indexOf(':') + 1);
                    const btn = doc.createElement('button');
                    btn.className = 'button button-times ' + position;
                    btn.setAttribute('type', 'button');
                    btn.setAttribute('shani-on', 'click:close');
                    btn.setAttribute('shani-target', selector);
                    btn.innerHTML = '&times;';
                    return btn;
                }
            };
            const createModal = (specs, data) => {
                const mdbg = doc.createElement('div'), modal = doc.createElement('div');
                const wrapper = doc.createElement('div'), btn = getCloseBtn(data, '.modal-background');
                btn.style.margin = 'var(--spacing)';
                wrapper.className = 'loader-spin full-size';
                wrapper.id = Utils.getId();
                wrapper.style.setProperty('--loader-size', '2.5rem');
                modal.className = specs;
                modal.appendChild(btn);
                modal.appendChild(wrapper);
                mdbg.className = 'modal-background';
                mdbg.appendChild(modal);
                doc.body.appendChild(mdbg);
                Shani.on('end', () => wrapper.classList.remove('loader-spin'));
                return wrapper.id;
            };
            const closeOtherModals = shani => {
                if (!shani.timer && shani.target) {
                    doc.querySelectorAll('.modal-background').forEach(mc => {
                        if (!mc.querySelector(shani.target)) {
                            Utils.removeNode(mc);
                        }
                    });
                }
            };
            Shani.on('start', e1 => {
                const shani = e1.detail.shani, specs = shani.emitter.getAttribute('ui-class');
                if (specs?.split(' ').indexOf('modal') > -1) {
                    const attr = shani.emitter.getAttribute('ui-data');
                    shani.target = '#' + createModal(specs, attr);
                }
                Shani.on('data', e => closeOtherModals(e.detail.shani));
            });
        })();
        const Toaster = (() => {
            const toast = (message, code) => {
                const content = code + ' &CenterDot; ' + message;
                let toaster = doc.getElementById('oer89trJ');
                const color = code === 200 ? 'success' : (code > 399 ? 'danger' : 'info');
                if (toaster) {
                    toaster.remove();
                }
                toaster = doc.createElement('div');
                toaster.id = 'oer89trJ';
                toaster.innerHTML = content;
                toaster.className = 'toaster pos-tc width-md-5 width-sm-10 color-' + color;
                doc.body.appendChild(toaster);
                setTimeout(() => {
                    toaster.style.transform = 'translateY(-100%)';
                    toaster.addEventListener('transitionend', e => e.target.remove());
                }, 3000 + toaster.innerText.length * 64);
            };
            Shani.on('abort', e => {
                toast(e.detail.status || 'Request cancelled.', e.detail.code);
            })('error', e => {
                toast(e.detail.status || 'Failed to connect to server. Try again.', e.detail.code);
            })('timeout', e => {
                toast(e.detail.status || 'Response takes too long.', e.detail.code);
            })('redirect', e => {
                toast(e.detail.status || 'Redirecting...', e.detail.code);
            })('data', e => {
                const specs = e.detail.shani.emitter.getAttribute('ui-class');
                if (specs?.split(' ').indexOf('toaster') > -1) {
                    toast(e.detail.data || '(No data returned)', e.detail.code);
                }
            })('copy', e => toast('Copied!', 200));
        })();
    })();
})(document);