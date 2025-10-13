(doc => {
    'use strict';
    doc.addEventListener('DOMContentLoaded', () => {
        Shanify(doc.body);
        Observers.mutate(doc.body);
        if (!window.Shani) {
            USER_DATA.fn = Utils.object();
            window.Shani = Utils.object({
                select: (selector, obj) => USER_DATA.attr.set(selector, Utils.object(obj)),
                action: (name, fn) => {
                    const n = name.toLowerCase();
                    if (n in USER_DATA.fn) {
                        console.warn(n + ' already exists.');
                    } else {
                        USER_DATA.fn[n] = fn;
                    }
                }
            });
            Object.freeze(window.Shani);
            Object.freeze(USER_DATA);
            doc.dispatchEvent(new Event('shani:init'));
        }
    });
    const USER_DATA = Object.setPrototypeOf({attr: new Map()}, null);
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
        const json = data => typeof data === 'string' ? Utils.object(JSON.parse(data)) : data;
        return {
            map2json(map) {
                const obj = Utils.object();
                map.forEach((v, k) => obj[k] = v);
                return obj;
            },
            input2form(shani) {
                if (shani.inf) {
                    return Utils.recursiveCall(shani.inf, [shani.emitter]);
                }
                const node = shani.emitter;
                if (['SELECT', 'INPUT', 'TEXTAREA'].includes(node.tagName)) {
                    const fd = new FormData();
                    if (!node.files) {
                        fd.append(node.name || 'value', node.value);
                    } else {
                        for (let f = 0; f < node.files.length; f++) {
                            fd.append(node.name || 'file[]', node.files[f]);
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
                            node += convert(obj[key], isArray ? 'item' : key.replace(/\s+/, '-'));
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
            before: 'beforebegin', after: 'afterend', discard: 'discard'
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
            const type = Utils.getSubtype(headers.get('content-type'));
            const plainText = (target.getAttribute('shani-xss') || shani.xss) === 'true' || type !== 'html';
            const mechanism = 'insertAdjacent' + (plainText ? 'Text' : 'HTML');
            if (['INPUT', 'TEXTAREA'].includes(target.tagName)) {
                setInputData(target, data, mode, mechanism);
            } else {
                setNodeData(target, data, mode, mechanism, plainText);
            }
        };
        const handleDataInsertion = (target, shani, resp, mode) => {
            const outf = target.getAttribute('shani-outf') || shani.outf;
            if (outf) {
                return Utils.recursiveCall(outf, [shani.emitter, target, resp]);
            }
            if (mode === 'discard') {
                return;
            }
            insertData(target, shani, resp.body || '', mode, resp.headers);
        };
        return {
            processResponse(shani, target, response, mode) {
                Utils.trigger(shani, 'data', response);
                target.forEach(node => handleDataInsertion(node, shani, response, mode));
            }
        };
    })();
    const Shani = (() => {
        const Obj = function (node, e, attrib) {
            this.event = e;
            this.emitter = node;
            this.poll = Utils.object();
            this.url = node.getAttribute('href') || node.getAttribute('action');
            setAttribs(this, node, Shani.SHANI_ATTR, 'shani-');
            setAttribs(this, node, Shani.HTML_ATTR, '');
            /**/
            this.actions = collectActions(Utils.explode(selectAttribute(node, attrib), ';'));
            const nodeActions = collectActions(Utils.explode(node.getAttribute(attrib), ';'));
            nodeActions.forEach((v, k) => this.actions.set(k, v));
            const headerline = this.headers;
            this.headers = new Headers(Utils.explode(selectAttribute(node, 'shani-headers'), '&'));
            new Headers(Utils.explode(headerline, '&')).forEach((v, k) => this.headers.set(k, v));
        };
        const setAttribs = (shani, node, attrs, prefix) => {
            attrs.forEach(a => {
                const attr = node.getAttribute(prefix + a);
                shani[a] = attr !== null ? attr : selectAttribute(node, prefix + a);
            });
        };
        const collectActions = actions => {
            const map = new Map();
            actions.forEach((str, evt) => {
                const parts = str.split('>>').map(s => s.trim());
                const pos = parts[0].search(/\s/), fn = pos > -1 ? parts[0].slice(0, pos) : parts[0];
                const params = pos > -1 ? parts[0].slice(pos + 1).split('&') : null;
                map.set(evt, Utils.object({fn: fn.toLowerCase(), params, selector: parts[1]}));
            });
            return map;
        };
        const selectAttribute = (node, name) => {
            for (let val of USER_DATA.attr) {
                if (name in val[1] && node.matches(val[0])) {
                    return val[1][name];
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
         */
        const sendReq = (shani, method, target, params) => {
            const mode = params ? params[0].trim() : 'replace';
            const type = Utils.getSubtype(shani.enctype);
            if (type && type !== 'form-data') {
                shani.headers.set('content-type', shani.enctype.trim());
            }
            if (shani.scheme === 'ws') {
                return WSocket(shani, target, mode);
            }
            if (shani.scheme === 'sse') {
                return ServerEvent(shani, target, mode);
            }
            let em = shani.emitter;
            if (em.tagName === 'FORM') {
                em = em.querySelector('fieldset') || em;
            }
            HTTP.createReq(shani, shani.method || method, request => {
                Utils.trigger(shani, 'start', {request});
                em.setAttribute('disabled', '');
            }, () => {
                em.removeAttribute('disabled');
                Utils.trigger(shani, 'end');
            }, resp => onSuccessReq(shani, target, resp, mode), () => {
                if (shani.poll.limit > 0) {
                    shani.poll.limit++;
                }
                const resp = Utils.object({headers: new Headers(), status: 400, body: ''});
                onSuccessReq(shani, target, resp, mode);
            });
        };
        const onSuccessReq = (shani, target, resp, mode) => {
            const text = Utils.code2text(resp.status);
            Utils.trigger(shani, '' + resp.status, resp);
            Utils.trigger(shani, text, resp);
            HTML.processResponse(shani, target, resp, mode);
            if (text === 'redirect') {
                const url = resp.headers.get('location');
                url === '#' ? location.reload() : location = url;
            }
        };
        const getCover = (target, size) => {
            let style = 'position:fixed;top:0;left:0;width:100%;height:100%;padding:1rem;';
            style += 'overflow-y:auto;font-size:' + (size || 100) + '%;background:#fff;z-index:998';
            const cover = doc.createElement('div');
            cover.style = style;
            for (const t of target) {
                cover.appendChild(t.cloneNode(true));
            }
            doc.body.insertBefore(cover, doc.body.firstChild);
            return cover;
        };
        /**
         * Move this element to a specified position, to another destination.
         * If a position is not given then the element is placed to the end.
         */
        const moveNode = (parent, emitter, params, clone) => {
            const index = parseInt(params[0]), len = parent.children.length + 1;
            const pos = index > 0 ? index - 1 : index + len;
            const kids = params[1] ? doc.querySelectorAll(params[1].trim()) : [emitter];
            kids.forEach(node => {
                if (Math.abs(index) <= len && index !== 0) {
                    const n = clone ? node.cloneNode(true) : node;
                    parent.insertBefore(n, parent.children[pos]);
                    if (clone) {
                        clone(n);
                    }
                }
            });
        };
        Obj.prototype = {
            /**
             * Read content from server.
             */
            r(obj) {
                if (this.history === 'true') {
                    history.pushState(null, '', this.url);
                }
                sendReq(this, 'GET', obj.targets, obj.params);
            },
            /**
             * Write content to server
             */
            w(obj) {
                sendReq(this, 'POST', obj.targets, obj.params);
            },
            trigger(obj) {
                for (const val of obj.params) {
                    obj.targets.forEach(node => {
                        node.dispatchEvent(new Event(val.trim(), {bubbles: true}));
                    });
                }
            },
            /**
             * Remove node from DOM
             */
            close(obj) {
                if (!obj.params) {
                    return obj.targets.forEach(node => Utils.removeNode(node));
                }
                const parent = Utils.getParentNode(this.emitter, obj.params);
                if (parent) {
                    Utils.removeNode(parent);
                }
            },
            print(obj) {
                if (window.print instanceof Function) {
                    const cover = getCover(obj.targets);
                    window.print();
                    cover.remove();
                }
            },
            /**
             * Offline search
             */
            search(obj) {
                const text = this.emitter.value.trim().toLowerCase();
                obj.targets.forEach(node => {
                    for (const row of node.children) {
                        row.style.display = row.textContent.toLowerCase().includes(text) ? null : 'none';
                    }
                });
            },
            /**
             * Full screen
             */
            fs(obj) {
                if (doc.fullscreenEnabled) {
                    const cover = getCover(obj.targets, 135);
                    doc.documentElement.requestFullscreen().then(() => {
                        doc.addEventListener('fullscreenchange', () => {
                            if (!doc.fullscreenElement) {
                                cover.remove();
                            }
                        });
                    }).catch(() => cover.remove());
                }
            },
            rmv(obj) {
                obj.targets.forEach(node => Utils.removeNode(node));
            },
            copyto(obj) {
                obj.targets.forEach(target => {
                    moveNode(target, this.emitter, obj.params, (node) => {
                        node.removeAttribute('shani-watch');
                        node.querySelectorAll('[id]').forEach(el => {
                            const id = Utils.getId();
                            node.querySelectorAll('[for="' + el.id + '"]').forEach(label => label.for = id);
                            el.id = id;
                        });
                    });
                });
            },
            moveto(obj) {
                obj.targets.forEach(node => moveNode(node, this.emitter, obj.params));
            },
            cssadd(obj) {
                obj.params.forEach(val => {
                    obj.targets.forEach(node => node.classList.add(val.trim()));
                });
            },
            cssrmv(obj) {
                obj.params.forEach(val => {
                    obj.targets.forEach(node => node.classList.remove(val.trim()));
                });
            },
            cssreplace(obj) {
                obj.params.forEach(val => {
                    const pos = val.indexOf(':'), key = val.slice(0, pos).trim();
                    const value = val.slice(pos + 1).trim();
                    obj.targets.forEach(node => node.classList.replace(key, value));
                });
            },
            csstoggle(obj) {
                obj.params.forEach(val => {
                    obj.targets.forEach(node => node.classList.toggle(val.trim()));
                });
            },
            /**
             * Remove properties from extisting node
             */
            proprmv(obj) {
                for (const p of obj.params) {
                    const key = p.trim();
                    obj.targets.forEach(node => {
                        if (key in node) {
                            const type = typeof node[key];
                            node[key] = type === 'boolean' ? false : type === 'number' ? 0 : '';
                        }
                    });
                }
            },
            /**
             * Add properties from extisting node
             */
            prop(obj) {
                for (const val of obj.params) {
                    const pos = val.indexOf(':'), key = (pos > -1 ? val.slice(0, pos) : val).trim();
                    const value = pos > -1 ? val.slice(pos + 1).trim() : key;
                    obj.targets.forEach(node => node[key] = key === value || value);
                }
            },
            /**
             * Map this.emitter properties to that of src element
             */
            propbind(obj) {
                for (const val of obj.params) {
                    const pos = val.indexOf(':'), thiskey = (pos > -1 ? val.slice(0, pos) : val).trim();
                    const thatkey = pos > -1 ? val.slice(pos + 1).trim() : thiskey;
                    obj.targets.forEach(node => this.emitter[thiskey] = node[thatkey]);
                }
            },
            /**
             * Toggle properties from extisting node
             */
            proptoggle(obj) {
                for (const val of obj.params) {
                    const key = val.trim();
                    obj.targets.forEach(node => {
                        const value = node[key];
                        node[key] = typeof value === 'boolean' ? !value : '' || value;
                    });
                }
            },
            /**
             * Create HTML modal element
             */
            makemodal(obj) {
                Utils.trigger(this, 'ui-modal', {specs: obj.params});
            },
            makeloader(obj) {
                Utils.trigger(this, 'ui-loader', {specs: obj.params, wrapper: obj.targets});
            }
        };
        window.addEventListener('popstate', e => history.go(0));
        return {
            HTML_ATTR: ['enctype', 'method'],
            SHANI_ATTR: ['watch', 'headers', 'timer', 'xss', 'inf', 'outf', 'cache', 'history', 'on', 'scheme'],
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
            const events = Utils.explode(node.getAttribute('watch-on'));
            events.forEach((v, k) => Shani.on(k, watch));
        };
        const addListener = node => {
            const events = Utils.explode(node.getAttribute('shani-on'));
            events.forEach((v, k) => {
                doc.addEventListener(k, listen);
                if (k === 'load') {
                    node.dispatchEvent(new Event(k, {bubbles: true}));
                } else if (k === 'demand') {
                    Observers.intersect(node);
                }
            });
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
            const action = shani.actions.get(event);
            const cb = action ? USER_DATA.fn[action.fn] || shani[action.fn] : null;
            if (cb instanceof Function) {
                const targets = action.selector ? doc.querySelectorAll(action.selector) : [shani.emitter];
                const result = cb.call(shani, Utils.object({
                    emitter: shani.emitter, params: action.params, targets, data
                }));
                if (result !== false) {
                    Utils.trigger(shani, action.fn);
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
            getParentNode(childNode, parentSelector) {
                const parent = childNode.parentElement;
                if (!parent || parent.matches(parentSelector)) {
                    return parent;
                }
                return Utils.getParentNode(parent, parentSelector);
            },
            explode(str, sep = '&') {
                const map = new Map();
                if (str) {
                    const raw = str.split(sep).map(s => s.trim());
                    for (let val of raw) {
                        const pos = val.indexOf(':'), key = pos > 0 ? val.slice(0, pos) : val;
                        const name = key.toLowerCase();
                        if (name.length > 0) {
                            map.set(name, pos > 0 ? val.slice(pos + 1) : null);
                        }
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
            recursiveCall(path, args, thisArg) {
                const keys = path.split('.');
                const traverse = (obj, idx) => {
                    if (idx === keys.length - 1) {
                        return obj[keys[idx]].apply(thisArg || USER_DATA.fn, args);
                    }
                    return traverse(obj[keys[idx]], idx + 1);
                };
                return traverse(USER_DATA.fn, 0);
            },
            code2text(code) {
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
            }
        };
    })();
    const HTTP = (() => {
        const createPayload = (shani, method) => {
            const fd = Convertor.input2form(shani);
            const payload = Utils.object({
                url: shani.url, data: null, headers: shani.headers
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
            createReq(shani, method, onStart, onEnd, onSuccess, onError) {
                const payload = createPayload(shani, method), req = Utils.object();
                if (shani.cache) {
                    const pos = shani.cache.search(/\s/);
                    req.cacheDuration = parseInt(pos > -1 ? shani.cache.slice(0, pos) : shani.cache);
                    req.cacheName = pos > -1 ? shani.cache.slice(pos + 1) : null;
                }
                req.options = Utils.object({
                    headers: payload.headers,
                    body: payload.data,
                    method: method
                });
                onStart(req);
                Fetcher.send(payload.url, req, onSuccess, onError, onEnd);
            }
        };
    })();
    const WSocket = (() => {
        const createPayload = shani => {
            const payload = Utils.object({url: shani.url, data: null, headers: shani.headers});
            const formdata = Convertor.input2form(shani);
            if (formdata) {
                const type = Utils.getSubtype(shani.headers.get('content-type'));
                payload.data = JSON.stringify({
                    headers: Convertor.map2json(shani.headers),
                    body: Convertor.form2(formdata, type)
                });
            }
            return payload;
        };
        const httpHandler = (shani, socket, target, mode) => {
            const on = (e, cb) => socket.addEventListener(e, cb);
            on('open', () => {
                const payload = createPayload(shani);
                Utils.trigger(shani, 'start', {request: payload});
                socket.send(payload.data || '');
            });
            on('message', e => {
                const resp = Utils.object({body: e.data || '', headers: new Headers()});
                HTML.processResponse(shani, target, resp, mode);
            });
            on('error', e => Utils.trigger(shani, e.type));
            on('close', e => Utils.trigger(shani, 'end'));
        };
        return (shani, target, mode) => {
            const scheme = location.protocol === 'http:' ? 'ws://' : 'wss://';
            const host = shani.url.indexOf('://') === -1 ? scheme + location.host : '';
            httpHandler(shani, new WebSocket(host + shani.url), target, mode);
        };
    })();
    const ServerEvent = (() => {
        const httpHandler = (shani, sse, target, mode) => {
            const on = (e, cb) => sse.addEventListener(e, cb);
            on('message', e => {
                Utils.trigger(shani, 'start');
                const resp = Utils.object({
                    body: e.data || '', headers: new Headers({'content-type': 'text/html'})
                });
                HTML.processResponse(shani, target, resp, mode);
            });
            on('error', e => Utils.trigger(shani, e.type));
            on('beforeunload', () => {
                sse.close();
                Utils.trigger(shani, 'end');
            });
        };
        return (shani, target, mode) => httpHandler(shani, new EventSource(shani.url), target, mode);
    })();
    const Fetcher = (() => {
        const cacheResponse = (cache, req, res, url) => {
            const headers = new Headers(res.headers);
            headers.set('x-expires', Date.now() + (req.cacheDuration * 1000));
            const cached = new Response(res.body, {
                statusText: res.statusText,
                status: res.status,
                headers
            });
            cache.put(url, cached);
        };
        const parseResponse = (res, onSuccess, accept) => {
            const type = accept || Utils.getSubtype(res.headers.get('content-type'));
            let promise;
            if (type === 'json') {
                promise = res.json();
            } else if (['html', 'plain', 'xml'].includes(type)) {
                promise = res.text();
            } else if (type === 'form') {
                promise = res.formData();
            } else {
                promise = res.blob().then(URL.createObjectURL);
            }
            promise.then(body => {
                onSuccess(Utils.object({headers: res.headers, status: res.status, body}));
            });
        };
        const fetchAndCache = (cache, url, req, type, onSuccess, onError) => {
            fetch(url, req.options).then(res => {
                cacheResponse(cache, req, res.clone(), url);
                parseResponse(res, onSuccess, type);
            }).catch(onError);
        };
        const handleCacheResponse = (url, req, type, onSuccess, onError, onEnd) => {
            caches.open(req.cacheName || 'pubcache').then(cache => {
                cache.match(url).then(res => {
                    const expires = res && res.headers.get('x-expires');
                    if (res && Date.now() < Number(expires)) {
                        parseResponse(res, onSuccess, type);
                    } else {
                        fetchAndCache(cache, url, req, type, onSuccess, onError);
                    }
                }).catch(() => fetchAndCache(cache, url, req, type, onSuccess, onError));
            }).catch(onError).finally(onEnd);
        };
        return {
            send(url, req, onSuccess, onError, onEnd) {
                const type = Utils.getSubtype(req.options.headers.get('accept')) || null;
                if (req.cacheDuration && 'caches' in window) {
                    handleCacheResponse(url, req, type, onSuccess, onError, onEnd);
                } else {
                    fetch(url, req.options).then(res =>
                        parseResponse(res, onSuccess, type)
                    ).catch(onError).finally(onEnd);
                }
            }
        };
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
            const COVER = 'modal-background';
            const getCloseBtn = classList => {
                if (classList) {
                    const btn = doc.createElement('button');
                    btn.className = 'button button-times ' + classList;
                    btn.setAttribute('type', 'button');
                    btn.setAttribute('shani-on', 'click:close>>.' + COVER);
                    btn.innerHTML = '&times;';
                    return btn;
                }
            };
            const createModal = specs => {
                const [id, classList] = specs[0].split(':').map(s => s.trim());
                const mdbg = doc.createElement('div'), modal = doc.createElement('div');
                const wrapper = doc.createElement('div');
                wrapper.id = id;
                wrapper.className = 'full-size';
                wrapper.style.setProperty('--loader-size', '2.5rem');
                if (specs.length > 1) {
                    const btn = getCloseBtn(specs[1].split(':')[1]);
                    btn.style.margin = 'var(--spacing)';
                    modal.appendChild(btn);
                }
                modal.className = classList;
                modal.appendChild(wrapper);
                mdbg.className = COVER;
                mdbg.appendChild(modal);
                doc.body.appendChild(mdbg);
            };
            Shani.on('ui-modal', e => createModal(e.detail.specs));
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
            Shani.on('error', e => {
                toast(e.detail.statusText || 'Failed to connect to server. Try again.', e.detail.status);
            })('redirect', e => {
                toast(e.detail.statusText || 'Redirecting...', e.detail.status);
            });
        })();
        const Loader = (() => {
            const createLoader = loader => {
                const [id, color] = loader.specs[0].split(':').map(s => s.trim());
                loader.wrapper.forEach(node => {
                    const bar = doc.createElement('div'), wrapper = doc.createElement('div');
                    bar.className = 'progress';
                    if (color) {
                        bar.style.setProperty('--color', color);
                    }
                    wrapper.id = id;
                    wrapper.className = 'progress-bar loader';
                    wrapper.appendChild(bar);
                    node.appendChild(wrapper);
                });
            };
            Shani.on('ui-loader', e => createLoader(e.detail));
        })();
    })();
})(document);