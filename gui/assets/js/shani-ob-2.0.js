(doc => {
    'use strict';
    doc.addEventListener('DOMContentLoaded', function () {
        Shanify(this.body);
        Observers.mutate(this.body);
    });
    const Observers = (() => {
        const runScript = (node) => {
            if (node.hasAttribute('src')) {
                const found = doc.head.querySelector('script[src="' + node.src + '"]') !== null;
                if (!found) {
                    doc.head.appendChild(node);
                    node.addEventListener('load', function () {
                        Function(this.textContent)();
                    });
                }
            } else {
                Function(node.textContent)();
            }
        };
        const addNode = (node) => {
            if (node instanceof Element) {
                if (node.tagName === 'SCRIPT') {
                    return runScript(node);
                }
                Shanify(node);
            }
        };
        const mo = (changes) => {
            for (let change of changes) {
                for (let node of change.addedNodes) {
                    addNode(node);
                }
            }
        };
        const demand = function (changes) {
            for (let change of changes) {
                if (change.isIntersecting) {
                    change.target.dispatchEvent(new Event('demand'));
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
        const json = (data) => {
            if (typeof data === 'string') {
                return Utils.object(JSON.parse(data));
            }
            return data;
        };
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
                const enclose = (val) => {
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
                return output.substring(1);
            },
//            file2json(file) {
//                const fr = new FileReader();
//                fr.readAsDataURL(file);
//                return new Promise(function (ok) {
//                    fr.addEventListener('load', function (e) {
//                        ok(Utils.object({
//                            name: file.name, size: file.size, type: file.type,
//                            base64: e.target.result.substring(e.target.result.indexOf(',') + 1)
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
        const setInputData = (target, data, modes, mode, mechanism) => {
            if (mode === 'prepend') {
                target.value = data + target.value;
            } else if (mode === 'append') {
                target.value += data;
            } else if (mode === 'replace') {
                target.value = data;
            } else {
                target[mechanism](modes[mode], data);
            }
        };
        const setNodeData = (target, data, modes, mode, mechanism, plainText) => {
            if (mode === 'replace') {
                if (plainText) {
                    target.textContent = data;
                } else {
                    target.innerHTML = data;
                }
            } else {
                target[mechanism](modes[mode], data);
            }
        };
        const insertData = (target, shani, modes, data, type) => {
            const mode = shani.insert || 'replace';
            const plainText = shani.xss === 'true' || type !== 'html';
            const mechanism = 'insertAdjacent' + (plainText ? 'Text' : 'HTML');
            if (Utils.isInput(target)) {
                setInputData(target, data, modes, mode, mechanism);
            } else {
                setNodeData(target, data, modes, mode, mechanism, plainText);
            }
            if (mode === 'swap') {
                target.remove();
            }
        };
        const mutateCSS = (node, params) => {
            const args = params.split(' ');
            if (args[0] === 'replace') {
                return node.classList.replace(args[1], args[2]);
            }
            for (let i = 1; i < args.length; i++) {
                node.classList[args[0]](args[1]);
            }
        };
        return {
            processResponse(shani, response) {
                const modes = Utils.object({
                    prepend: 'afterbegin', append: 'beforeend', replace: 'replace',
                    swap: 'afterend', before: 'beforebegin', after: 'afterend'
                });
                Utils.emitEvent(shani, 'on:data', response);
                const type = Utils.getSubtype(response?.headers.get('content-type'));
                doc.querySelectorAll(shani.target).forEach(target => insertData(target, shani, modes, response.data || '', type));
            },
            handleCss(node, css, evt) {
                const handlers = Utils.explode(css);
                for (let handler of handlers) {
                    if (handler[0] === evt || handler[0] === '*') {
                        mutateCSS(node, handler[1]);
                    }
                }
            }
        };
    })();
    const Shani = (() => {
        const Obj = function (node, e) {
            this.event = e;
            this.emitter = node;
            this.timer = Utils.object();
            this.url = node.getAttribute('href') || node.getAttribute('action') || node.value;
            setAttribs(this, node, Shani.SHANI_ATTR, 'shani-');
            setAttribs(this, node, Shani.HTML_ATTR, '');
        };
        const setAttribs = (shani, node, attrs, prefix) => {
            for (const a of attrs) {
                shani[a] = node.getAttribute(prefix + a) || GLOBAL_ATTR[a] || null;
            }
        };
        let GLOBAL_ATTR = {};
        /**
         * Make HTTP request at a regular interval
         * @param {type} shani Shani object
         * @returns {undefined}
         */
        const doPolling = shani => {
            const poll = shani.poll.split(':');
            shani.timer.limit = parseInt(poll[2]) || null;
            shani.timer.steps = Number(poll[1] || -1) * 1000;
            setTimeout(shani[shani.fn].bind(shani), Number(poll[0] || 0) * 1000);
        };
        /**
         * Resend a polling HTTP request
         * @param {type} shani
         * @returns {undefined}
         */
        const resubmit = shani => {
            if (shani.emitter.isConnected && shani.timer.steps > -1 && (!shani.timer.limit || (--shani.timer.limit) > 0)) {
                setTimeout(shani[shani.fn].bind(shani), shani.timer.steps);
            }
        };
        /**
         * Send HTTP request
         * @param {type} shani Shani object
         * @param {string} method HTTP request type
         * @returns {unresolved}
         */
        const sendReq = (shani, method) => {
            if (shani.scheme === 'ws') {
                return WSocket(shani);
            }
            if (shani.scheme === 'sse') {
                return ServerEvent(shani);
            }
            let rem = shani.emitter;
            if (rem.tagName === 'FORM') {
                rem = rem.querySelector('fieldset') || rem;
            }
            rem.style.opacity = 0.4;
            const targets = doc.querySelectorAll(shani.target);
            HTTP.send(shani, shani.method || method, (request) => {
                targets.forEach(node => node.style.opacity = 0.4);
                Utils.emitEvent(shani, 'on:start', {request});
                rem.setAttribute('disabled', 'disabled');
            }, response => {
                targets.forEach(node => node.style.opacity = null);
                rem.style.opacity = null;
                rem.removeAttribute('disabled');
                Utils.emitEvent(shani, 'on:end', response);
                resubmit(shani);
            });
        };
        const getTarget = (shani) => {
            if (!shani.target) {
                return shani.emitter;
            }
            const watchers = doc.querySelectorAll(shani.target);
            if (watchers.length === 1) {
                return watchers[0];
            }
            const wrapper = doc.createElement('div');
            for (const watcher of watchers) {
                wrapper.appendChild(watcher.cloneNode(true));
            }
            return wrapper;
        };
        const getCover = (shani, size) => {
            const cover = doc.createElement('div');
            cover.style = 'position:fixed;top:0;left:0;width:100%;height:100%;padding:1rem;';
            cover.style += 'overflow-y:auto;font-size:' + (size || 100) + '%;background:#fff;z-index:998';
            cover.innerHTML = getTarget(shani).outerHTML;
            doc.body.insertBefore(cover, doc.body.firstChild);
            return cover;
        };
        Obj.prototype = {
            /**
             * Read content from server. history.pushState(null, doc.title, this.url) will be added in future
             */
            r() {
                sendReq(this, 'GET');
            },
            /**
             * Write content to server
             */
            w() {
                sendReq(this, 'POST');
            },
            /**
             * Remove node from DOM
             * @returns {undefined}
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
             * @returns {undefined}
             */
            search() {
                const text = this.emitter.value.trim().toLowerCase(), target = getTarget(this);
                for (const row of target.children) {
                    row.style.display = row.textContent.toLowerCase().indexOf(text) < 0 ? 'none' : null;
                }
            },
            /**
             * Full screen
             * @returns {undefined}
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
            }
        };
        if (!window.Shani) {
            window.Shani = obj => GLOBAL_ATTR = Utils.object(obj);
        }
        return {
            HTML_ATTR: ['enctype', 'method'],
            SHANI_ATTR: ['watch', 'header', 'poll', 'insert', 'xss', 'css', 'on', 'fn', 'scheme', 'target'],
            create(node, event) {
                const shani = new Obj(node, event);
                Utils.emitEvent(shani, 'on:' + event.type);
                if (shani[shani.fn] instanceof Function) {
                    if (!shani.poll || shani.scheme === 'ws') {
                        shani[shani.fn]();
                    } else if (shani.poll) {
                        doPolling(shani);
                    }
                }
            },
            on(e, cb) {
                doc.addEventListener('shani:on:' + e, cb);
                return Shani.on;
            }
        };
    })();
    const Shanify = (() => {
        const listen = (e) => {
            const node = e.target.closest('[shani-on~=' + e.type + ']');
            if (node) {
                if (['A', 'AREA', 'FORM'].indexOf(node.tagName) > -1) {
                    e.preventDefault();
                }
                if (!node.hasAttribute('disabled')) {
                    Shani.create(node, e);
                }
            }
        };
        const setDefaultEvents = node => {
            let events = node.getAttribute('shani-on');
            if (events === null && !node.hasAttribute('watch-on')) {
                events = node.tagName === 'FORM' ? 'submit' : (Utils.isInput(node) || node.tagName === 'SELECT' ? 'change' : 'click');
                node.setAttribute('shani-on', events);
            }
            const watchEvents = node.getAttribute('watch-on');
            if (watchEvents !== null) {
                const eventList = Utils.explode(watchEvents, ' ');
                for (let e of eventList) {
                    Shani.on(e[0], watch); //watch for event
                }
            }
            return events;
        };
        const addListener = node => {
            const evtList = Utils.explode(setDefaultEvents(node), ' ');
            for (let evt of evtList) {
                if (evt[0] === 'load') {
                    node.addEventListener(evt[0], listen);
                    node.dispatchEvent(new Event(evt[0]));
                } else if (evt[0] === 'demand') {
                    node.addEventListener(evt[0], listen);
                    Observers.intersect(node);
                } else {
                    doc.addEventListener(evt[0], listen);
                }
            }
        };
        const watch = e => {
            const evt = e.type.substring(e.type.lastIndexOf(':') + 1);
            doc.querySelectorAll('[shani-watch]').forEach(watcher => {
                const events = watcher.getAttribute('watch-on');
                if (events.split(',').indexOf(evt) > -1 || events === '*') {
                    if (e.detail.shani.emitter.matches(watcher.getAttribute('shani-watch'))) {
                        Shani.create(watcher, e);
                    }
                }
            });
        };

        return root => {
            if (root.hasAttribute('shani-fn')) {
                addListener(root);
            }
            root.querySelectorAll('[shani-fn]').forEach(node => addListener(node));
        };
    })();
    const Utils = (() => {
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
            isInput(node) {
                return ['INPUT', 'TEXTAREA'].indexOf(node.tagName) > -1;
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
                        const pos = val.indexOf(':'), key = pos > 0 ? val.substring(0, pos) : val;
                        map.set(key.toLowerCase().trim(), pos > 0 ? val.substring(pos + 1).trim() : null);
                    }
                }
                return map;
            },
            object(o) {
                return Object.setPrototypeOf(o || {}, null);
            },
            emitEvent(shani, evt, data = {}) {
                const event = evt.substring(evt.lastIndexOf(':') + 1);
                if (shani.css !== null) {
                    HTML.handleCss(shani.emitter, shani.css, event);
                }
                data.shani = shani;
                doc.dispatchEvent(new CustomEvent('shani:' + evt, {detail: Utils.object(data)}));
            },
            getReqHeaders(shani) {
                const type = Utils.getSubtype(shani.enctype), headers = Utils.explode(shani.header, '|');
                if (type && type !== 'form-data') {
                    headers.set('content-type', shani.enctype.trim());
                }
                return headers;
            },
            getSubtype(header) {
                if (header) {
                    const subtype = header.substring(header.indexOf('/') + 1).split(';')[0];
                    const plusPos = subtype.indexOf('+');
                    return plusPos < 0 ? subtype : subtype.substring(plusPos + 1);
                }
                return null;
            }
        };
    })();
    const HTTP = (() => {
        const getHttpResponse = (xhr) => {
            const resp = Utils.object({code: xhr.status, status: xhr.statusText});
            if (xhr.readyState >= 4) {
                resp.data = xhr.response;
                resp.headers = Utils.explode(xhr.getAllResponseHeaders(), '\r\n');
            }
            return resp;
        };
        const httpHandler = (shani, xhr, cb) => {
            const on = (e, cb) => xhr.addEventListener(e, cb);
            on('readystatechange', function () {
                if (this.readyState === 4) {
                    HTTP.fire(shani, getHttpResponse(xhr), xhr.status);
                }
            });
            on('error', () => {
                if (shani.timer.limit > 0) {
                    shani.timer.limit++;
                }
                HTTP.fire(shani, getHttpResponse(xhr), 400);
            });
            on('abort', () => HTTP.fire(shani, getHttpResponse(xhr), 410));
            on('timeout', () => HTTP.fire(shani, getHttpResponse(xhr), 408));
            on('loadstart', () => HTTP.fire(shani, getHttpResponse(xhr), 102));
            on('loadend', () => cb(getHttpResponse(xhr)));

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const response = getHttpResponse(xhr);
                    response.bytes = Utils.object({loaded: e.loaded, total: e.total});
                    HTTP.fire(shani, response, 102);
                }
            });
        };
        const redirect = (headers) => {
            const url = headers.get('location');
            if (url === '#') {
                window.location.reload();
            } else {
                window.location = url;
            }
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
                Utils.emitEvent(shani, 'on:' + code, response);
                Utils.emitEvent(shani, 'on:' + status, response);
                HTML.processResponse(shani, response);
                if (status === 'redirect') {
                    redirect(response.headers);
                }
            }
        };
    })();
    const WSocket = (() => {
        const createPayload = (shani) => {
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
                socket.send(payload.data || '');
                Utils.emitEvent(shani, 'on:start', {request: payload});
            });
            on('message', (e) => {
                const resp = Utils.object({data: e.data || null, headers: null});
                Utils.emitEvent(shani, 'on:' + e.type, resp);
                HTML.processResponse(shani, resp);
            });
            on('error', (e) => Utils.emitEvent(shani, 'on:' + e.type));
            on('close', () => {
                Utils.emitEvent(shani, 'on:end');
            });
        };
        return (shani) => {
            const scheme = location.protocol === 'http:' ? 'ws' : 'wss';
            const host = shani.url.indexOf('://') === -1 ? scheme + '://' + location.host : '';
            httpHandler(shani, new WebSocket(host + shani.url));
        };
    })();
    const ServerEvent = (() => {
        const httpHandler = (shani, sse) => {
            const on = (e, cb) => sse.addEventListener(e, cb);
            const evt = Utils.explode(shani.on || 'message');
            for (let e of evt) {
                on(e[0], (e) => {
                    Utils.emitEvent(shani, 'on:start');
                    const resp = Utils.object({
                        data: e.data || null, headers: new Map().set('content-type', 'text/html')
                    });
                    Utils.emitEvent(shani, 'on:' + e.type, resp);
                    HTML.processResponse(shani, resp);
                });
            }
            on('error', (e) => Utils.emitEvent(shani, 'on:' + e.type));
            on('beforeunload', () => {
                sse.close();
                Utils.emitEvent(shani, 'on:end');
            });
        };
        return (shani) => httpHandler(shani, new EventSource(shani.url));

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
            const addCloseBtn = (modal, attr) => {
                if (attr !== null) {
                    const position = attr.substring(attr.indexOf(':') + 1), target = '#' + modal.parentElement.id;
                    const btn = doc.createElement('button');
                    btn.className = 'button button-times ' + position;
                    btn.setAttribute('type', 'button');
                    btn.setAttribute('shani-fn', 'close');
                    btn.setAttribute('shani-target', target);
                    btn.innerHTML = '&times;';
                    modal.appendChild(btn);
                }
            };
            const createModal = (specs, data) => {
                const mdbg = doc.createElement('div'), modal = doc.createElement('div');
                modal.className = specs;
                mdbg.className = 'modal-background';
                mdbg.id = Date.now().toString(36);
                modal.id = mdbg.id + 'mdl';
                mdbg.appendChild(modal);
                doc.body.appendChild(mdbg);
                addCloseBtn(modal, data);
                return modal;
            };
            const closeOtherModals = shani => {
                if (!shani.poll) {
                    doc.querySelectorAll('.modal-background').forEach(mc => {
                        if (mc.querySelector(shani.target) === null) {
                            Utils.removeNode(mc);
                        }
                    });
                }
            };
            Shani.on('start', e1 => {
                const shani = e1.detail.shani, specs = shani.emitter.getAttribute('ui-class');
                if (specs?.split(' ').indexOf('modal') > -1) {
                    const spinner = Loader.getSpinner(), attr = shani.emitter.getAttribute('ui-data');
                    const modal = createModal(specs, attr);
                    shani.target ||= '#' + modal.id;
                    shani.insert ||= 'append';
                    modal.appendChild(spinner);
                    Shani.on('data', () => spinner.remove());
                }
                Shani.on('data', e => closeOtherModals(e.detail.shani));
            });
        })();
        const Loader = (() => {
            const getLoader = () => {
                let loader = doc.getElementById('ldrf17tl0');
                if (loader) {
                    loader.remove();
                }
                loader = doc.createElement('div');
                loader.id = 'ldrf17tl0';
                const bar = doc.createElement('div');
                bar.classList.add('progress');
                loader.className = 'progress-bar loader';
                loader.appendChild(bar);
                return loader;
            };
            Shani.on('start', e => {
                if (e.detail.shani.emitter.getAttribute('ui-class') === 'spinner') {
                    doc.querySelectorAll(e.detail.shani.target).forEach(node => {
                        node.appendChild(Loader.getSpinner());
                    });
                } else {
                    const loader = getLoader();
                    doc.body.appendChild(loader);
                    Shani.on('end', () => loader.remove());
                }
            });
            return {
                getSpinner() {
                    const spinner = doc.createElement('div');
                    spinner.className = 'spinner';
                    return spinner;
                }
            };
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
            });
        })();
    })();
})(document);