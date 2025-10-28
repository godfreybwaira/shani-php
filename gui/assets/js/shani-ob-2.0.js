(doc => {
    'use strict';
    doc.addEventListener('DOMContentLoaded', () => {
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
        Shanify(doc.body);
        Observers.mutate(doc.body);
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
            this.actions = collectActions(node.getAttribute(attrib));
            this.headers = new Headers(Utils.explode(this.headers));
            this.http = Utils.explode(this.http);
        };
        const setAttribs = (shani, node, attrs, prefix) => {
            attrs.forEach(a => shani[a] = node.getAttribute(prefix + a));
        };
        const collectActions = actionStr => {
            const actions = Utils.explode(actionStr, ';'), map = new Map();
            for (const key in actions) {
                const parts = actions[key].split('>>').map(s => s.trim());
                const pos = parts[0].search(/\s/), fn = pos > -1 ? parts[0].slice(0, pos) : parts[0];
                const params = pos > -1 ? Utils.explode(parts[0].slice(pos + 1)) : null;
                map.set(key, Utils.object({fn: fn.toLowerCase(), params, selector: parts[1]}));
            }
            return map;
        };
        /**
         * Make HTTP request at a given interval
         * @param {object} shani
         */
        const countdown = shani => {
            const t = Utils.explode(shani.timer);
            const start = Utils.time2ms(t.start) || 0;
            shani.poll.steps = Utils.time2ms(t.steps) || -1;
            shani.poll.limit = parseInt(t.limit) || null;
            setTimeout(Utils.trigger, start, shani, shani.event.type);
        };
        const onConnect = shani => {
            if (shani.http.timerId) {
                clearTimeout(shani.http.timerId);
            }
            Utils.trigger(shani, 'start');
        };
        /**
         * Send HTTP request
         */
        const sendReq = (shani, method, target, params) => {
            const mode = params?.mode || 'replace', timeout = shani.http.timeout;
            const type = Utils.getSubtype(shani.enctype);
            if (type && type !== 'form-data') {
                shani.headers.set('content-type', shani.enctype);
            }
            if (timeout) {
                shani.http.timerId = setTimeout(() => Utils.trigger(shani, 'timeout'), Utils.time2ms(timeout));
            }
            if (shani.http.scheme === 'sse') {
                return HttpRequest.sse(shani, target, mode, onConnect);
            } else if ('scheme' in shani.http) {
                return HttpRequest.wsocket(shani, target, mode, onConnect);
            }
            let em = shani.emitter;
            if (em.tagName === 'FORM') {
                em = em.querySelector('fieldset') || em;
            }
            HttpRequest.http(shani, shani.method || method, request => {
                em.setAttribute('disabled', '');
                Utils.trigger(shani, 'start', {request});
            }, () => {
                em.removeAttribute('disabled');
                Utils.trigger(shani, 'end');
            }, resp => onSuccessReq(shani, target, resp, mode), err => {
                const status = err.name === 'AbortError' ? 408 : 400;
                if (shani.poll.limit !== null) {
                    shani.poll.limit++;
                }
                const resp = Utils.object({headers: new Headers(), status, body: ''});
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
        const getCover = (target, pageSize, fontSize) => {
            const id = Utils.getId(), style = doc.createElement('style');
            let s = '#' + id + '{width:100%;min-height:100%;padding:1rem;overflow-y:auto;';
            s += 'font-size:' + (fontSize || 100) + '%}body>:not(#' + id + '){display:none}';
            s += '@media print{#' + id + '{padding:12mm;print-color-adjust:exact;' + pageSize + '}}';
            s += '@page{margin:0;page-break-after:always;break-after:page}';
            style.type = 'text/css';
            style.textContent = s;
            const cover = doc.createElement('div');
            cover.appendChild(style);
            cover.id = id;
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
            const index = parseInt(params.pos), len = parent.children.length + 1;
            const pos = index > 0 ? index - 1 : index + len;
            const kids = params.target ? doc.querySelectorAll(params.target) : [emitter];
            kids.forEach(node => {
                if (Math.abs(index) <= len && index !== 0) {
                    const n = clone ? node.cloneNode(true) : node;
                    parent.insertBefore(n, parent.children[pos]);
                    !clone || clone(n);
                }
            });
        };
        const addNode = (obj, handler) => {
            obj.targets.forEach(target => {
                const node = doc.createElement(obj.params['-tag']);
                delete obj.params['-tag'];
                for (const key in obj.params) {
                    node.setAttribute(key, obj.params[key] || '');
                }
                handler(target, node);
            });
        };
        Obj.prototype = {
            /**
             * Read content from server.
             */
            read(obj) {
                if (this.history === 'true') {
                    history.pushState(null, '', this.url);
                }
                sendReq(this, 'GET', obj.targets, obj.params);
            },
            /**
             * Write content to server
             */
            write(obj) {
                sendReq(this, 'POST', obj.targets, obj.params);
            },
            trigger(obj) {
                for (const val in obj.params) {
                    obj.targets.forEach(node => {
                        node.dispatchEvent(new Event(val, {bubbles: true}));
                    });
                }
            },
            /**
             * Remove node from DOM
             */
            close(obj) {
                if (obj.selector) {
                    const parent = Utils.getParentNode(this.emitter, obj.selector);
                    if (parent) {
                        return Utils.removeNode(parent);
                    }
                    obj.targets.forEach(node => Utils.removeNode(node));
                }
            },
            print(obj) {
                if (window.print instanceof Function) {
                    const size = obj.params.size || 'auto';
                    const cover = getCover(obj.targets, 'size:' + size);
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
                    const cover = getCover(obj.targets, '', 135);
                    doc.documentElement.requestFullscreen().then(() => {
                        doc.addEventListener('fullscreenchange', () => {
                            doc.fullscreenElement || cover.remove();
                        });
                    }).catch(() => cover.remove());
                }
            },
            nodermv(obj) {
                obj.targets.forEach(node => Utils.removeNode(node));
            },
            nodeappend(obj) {
                addNode(obj, (target, node) => target.appendChild(node));
            },
            nodeprepend(obj) {
                addNode(obj, (target, node) => target.insertBefore(node, target.firstChild));
            },
            nodereplace(obj) {
                addNode(obj, (target, node) => target.innerHTML = node.outerHTML);
            },
            nodeaddprev(obj) {
                addNode(obj, (target, node) => target.parentElement.insertBefore(node, target));
            },
            nodeaddnext(obj) {
                addNode(obj, (target, node) => target.parentElement.insertBefore(node, target.nextElementSibling));
            },
            nodecopyto(obj) {
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
            nodemoveto(obj) {
                obj.targets.forEach(node => moveNode(node, this.emitter, obj.params));
            },
            cssadd(obj) {
                for (const key in obj.params) {
                    obj.targets.forEach(node => node.classList.add(key));
                }
            },
            cssrmv(obj) {
                for (const key in obj.params) {
                    obj.targets.forEach(node => node.classList.remove(key));
                }
            },
            cssreplace(obj) {
                for (const key in obj.params) {
                    obj.targets.forEach(node => node.classList.replace(key, obj.params[key]));
                }
            },
            csstoggle(obj) {
                for (const key in obj.params) {
                    obj.targets.forEach(node => node.classList.toggle(key));
                }
            },
            cssexists(obj) {
                for (const node of obj.targets) {
                    for (const key in obj.params) {
                        if (!node.classList.contains(key)) {
                            return false;
                        }
                    }
                }
                return true;
            },
            /**
             * Remove properties from extisting node
             */
            proprmv(obj) {
                obj.targets.forEach(node => {
                    for (const key in obj.params) {
                        if (key in node) {
                            const type = typeof node[key];
                            node[key] = type === 'boolean' ? false : type === 'number' ? 0 : '';
                        }
                    }
                });
            },
            /**
             * Add properties from extisting node
             */
            prop(obj) {
                for (const key in obj.params) {
                    obj.targets.forEach(node => node[key] = key === obj.params[key] || obj.params[key]);
                }
            },
            /**
             * Map this.emitter properties to that of src element
             */
            propbind(obj) {
                obj.targets.forEach(node => {
                    for (const key in obj.params) {
                        const thatkey = obj.params[key] === null ? key : obj.params[key];
                        this.emitter[key] = node[thatkey];
                    }
                });
            },
            /**
             * Toggle properties from extisting node
             */
            proptoggle(obj) {
                for (const val in obj.params) {
                    obj.targets.forEach(node => {
                        node[val] = typeof node[val] === 'boolean' ? !node[val] : '' || node[val];
                    });
                }
            },
            propexists(obj) {
                for (const p in obj.params) {
                    for (const node of obj.targets) {
                        if (!(p in node)) {
                            return false;
                        }
                    }
                }
                return true;
            },
            saveas(obj) {
                const type = obj.params.type || obj.data.headers.get('content-type');
                const a = doc.createElement('a');
                a.download = obj.params.name;
                a.href = URL.createObjectURL(new Blob([obj.data.body], {type}));
                a.click();
                URL.revokeObjectURL(a.href);
            },
            /**
             * Create HTML modal element
             */
            makemodal(obj) {
                Utils.trigger(this, 'ui-modal', {specs: obj.params});
            },
            makeloader(obj) {
                Utils.trigger(this, 'ui-loader', {specs: obj.params, wrapper: obj.targets});
            },
            /**
             * Cancel ongoing HTTP connection
             */
            abortconn(obj) {
                if (!obj.params) {
                    for (const key in Utils.controller) {
                        Utils.controller[key].abort();
                    }
                } else {
                    Utils.controller[obj.params.name]?.abort();
                }
            }
        };
        window.addEventListener('popstate', e => history.go(0));
        return {
            HTML_ATTR: ['enctype', 'method'],
            SHANI_ATTR: ['watch', 'headers', 'timer', 'xss', 'inf', 'outf', 'cache', 'history', 'on', 'http'],
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
            for (const e in events) {
                Shani.on(e, watch);
            }
        };
        const addListener = node => {
            const events = Utils.explode(node.getAttribute('shani-on'));
            for (const e in events) {
                doc.addEventListener(e, listen);
                if (e === 'load') {
                    node.dispatchEvent(new Event(e, {bubbles: true}));
                } else if (e === 'demand') {
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
        const setUserAttributes = root => {
            for (let sel of USER_DATA.attr) {
                if (root.matches(sel[0])) {
                    addAttributes(root, sel[1]);
                }
                const nodes = root.querySelectorAll(sel[0]);
                nodes.forEach(node => addAttributes(node, sel[1]));
            }
        };
        const addAttributes = (node, obj) => {
            for (const key in obj) {
                let value = node.getAttribute(key);
                if (value === null) {
                    value = obj[key];
                } else if (['shani-http', 'shani-headers'].includes(key)) {
                    value = mergeString(obj[key], value, '&');
                } else if (['shani-on', 'watch-on'].includes(key)) {
                    value = mergeString(obj[key], value, ';');
                }
                node.setAttribute(key, value);
            }
        };
        const mergeString = (val1, val2, sep) => {
            const v1 = Utils.explode(val1, sep), v2 = Utils.explode(val2, sep);
            for (const k in v2) {
                v1[k] = v2[k];
            }
            let str = '';
            for (const k in v1) {
                str += sep + k + ':' + v1[k];
            }
            return str.slice(sep.length);
        };
        return root => {
            setUserAttributes(root);
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
                    emitter: shani.emitter, params: action.params,
                    selector: action.selector, targets, data
                }));
                result === false || Utils.trigger(shani, action.fn);
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
                const map = Utils.object();
                if (str) {
                    const raw = str.split(sep).map(s => s.trim());
                    for (let val of raw) {
                        const pos = val.indexOf(':'), key = pos > 0 ? val.slice(0, pos) : val;
                        if (key.length > 0) {
                            map[key] = pos > 0 ? val.slice(pos + 1).trim() : null;
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
                evt !== 'end' || resubmit(shani);
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
            },
            time2ms(time) {
                if (time) {
                    time = time.trim();
                    if (/^\d+(\.\d+)?[smhdy]$/.test(time)) {
                        const TIME_UNITS = {
                            s: 1, m: 60, h: 3600, d: 24 * 3600, y: 24 * 3600 * 365
                        };
                        const unit = time.slice(-1).toLowerCase();
                        const val = parseFloat(time.slice(0, -1));
                        return Math.round(TIME_UNITS[unit] * val * 1000);
                    }
                    throw new Error('Invalid time interval ' + time);
                }
                return time;
            },
            controller: Object.setPrototypeOf({}, null)
        };
    })();
    const HttpRequest = (() => {
        const createHttpPayload = (shani, method) => {
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
        const createWSocketPayload = shani => {
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
        return {
            http(shani, method, onStart, onEnd, onSuccess, onError) {
                const payload = createHttpPayload(shani, method), req = Utils.object();
                if (shani.cache) {
                    const params = Utils.explode(shani.cache);
                    req.cacheAge = Utils.time2ms(params.age);
                    req.cacheName = params.name || 'pubcache';
                }
                req.conn = shani.http.conn || 'conn';
                req.options = Utils.object({
                    headers: payload.headers,
                    body: payload.data,
                    method: method,
                    credentials: shani.http.credentials,
                    mode: shani.http.mode
                });
                onStart(req);
                Fetcher.send(payload.url, req, onSuccess, onError, onEnd);
            },
            sse(shani, target, mode, onConnect) {
                const sse = new EventSource(shani.url, {
                    withCredentials: shani.http.credentials === 'include'
                });
                const on = (e, cb) => sse.addEventListener(e, cb);
                on('message', e => {
                    const resp = Utils.object({
                        body: e.data || '', headers: new Headers({'content-type': 'text/html'})
                    });
                    HTML.processResponse(shani, target, resp, mode);
                });
                on('open', e => onConnect(shani));
                on('error', e => Utils.trigger(shani, e.type));
                on('beforeunload', () => {
                    sse.close();
                    Utils.trigger(shani, 'end');
                });
            },
            wsocket(shani, target, mode, onConnect) {
                const host = shani.url.contains('://') ? '' : shani.http.scheme + '://' + location.host;
                const socket = new WebSocket(host + shani.url);
                const on = (e, cb) => socket.addEventListener(e, cb);
                on('open', e => {
                    onConnect(shani);
                    const payload = createWSocketPayload(shani);
                    socket.send(payload.data || '');
                });
                on('message', e => {
                    const resp = Utils.object({body: e.data || '', headers: new Headers()});
                    HTML.processResponse(shani, target, resp, mode);
                });
                on('error', e => Utils.trigger(shani, e.type));
                on('close', e => Utils.trigger(shani, 'end'));
            }
        };
    })();
    const Fetcher = (() => {
        const cacheResponse = (cache, req, res, url) => {
            const headers = new Headers(res.headers);
            headers.set('x-expires', Date.now() + req.cacheAge);
            const cached = new Response(res.body, {
                statusText: res.statusText,
                status: res.status,
                headers
            });
            cache.put(url, cached);
        };
        const parseResponse = (res, accept, onSuccess, onError) => {
            if (res.status === 206) {
                handleStream(res, onSuccess);
            } else {
                handleNonStream(res, accept, onSuccess, onError);
            }
        };
        const handleStream = (res, onSuccess) => {
            const reader = res.body.getReader(), decoder = new TextDecoder();
            const readChunk = () => {
                reader.read().then(data => {
                    if (data.done) {
                        return;
                    }
                    onSuccess(Utils.object({
                        headers: res.headers, status: res.status, body: decoder.decode(data.value)
                    }));
                    readChunk();
                }).catch(e => null);
            };
            readChunk();
        };
        const handleNonStream = (res, accept, onSuccess, onError) => {
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
            }).catch(onError);
        };
        const fetchWithRetry = (url, req, responseHandler) => {
            if (!Utils.controller[req.conn] || Utils.controller[req.conn].signal.aborted) {
                Utils.controller[req.conn] = new AbortController();
            }
            req.options.signal = Utils.controller[req.conn].signal;
            return fetch(url, req.options).then(responseHandler);
        };
        const fetchAndCache = (cache, url, req, type, onSuccess, onError) => {
            fetchWithRetry(url, req, res => {
                cacheResponse(cache, req, res.clone(), url);
                parseResponse(res, type, onSuccess, onError);
            }).catch(onError);
        };
        const handleCacheResponse = (url, req, type, onSuccess, onError, onEnd) => {
            caches.open(req.cacheName).then(cache => {
                cache.match(url).then(res => {
                    const expires = res && res.headers.get('x-expires');
                    if (res && Date.now() < Number(expires)) {
                        parseResponse(res, type, onSuccess, onError);
                    } else {
                        fetchAndCache(cache, url, req, type, onSuccess, onError);
                    }
                }).catch(() => fetchAndCache(cache, url, req, type, onSuccess, onError));
            }).catch(onError).finally(onEnd);
        };
        return {
            send(url, req, onSuccess, onError, onEnd) {
                const type = Utils.getSubtype(req.options.headers.get('accept')) || null;
                if (req.cacheAge && 'caches' in window) {
                    handleCacheResponse(url, req, type, onSuccess, onError, onEnd);
                } else {
                    fetchWithRetry(url, req, res => {
                        parseResponse(res, type, onSuccess, onError);
                    }).catch(onError).finally(onEnd);
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
            const select = e => {
                const parent = Utils.getParentNode(e.target, '.accordion,.menubar');
                if (parent) {
                    const child = getEmittingChild(e.target, parent);
                    Utils.selectNode(parent.children, child, 'active');
                }
            };
            const getEmittingChild = (target, root) => {
                while (target !== root && target.parentElement !== root) {
                    target = target.parentElement;
                }
                return target;
            };
            doc.addEventListener('click', select);
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
                const mdbg = doc.createElement('div'), modal = doc.createElement('div');
                const wrapper = doc.createElement('div');
                wrapper.id = specs.id;
                wrapper.className = 'full-size';
                wrapper.style.setProperty('--loader-size', '2.5rem');
                if ('close-btn' in specs) {
                    const btn = getCloseBtn(specs['close-btn']);
                    btn.style.margin = 'var(--spacing)';
                    modal.appendChild(btn);
                }
                modal.className = specs.classes;
                modal.appendChild(wrapper);
                mdbg.className = COVER;
                mdbg.appendChild(modal);
                doc.body.appendChild(mdbg);
            };
            Shani.on('ui-modal', e => createModal(e.detail.specs));
        })();
        const Toaster = (() => {
            const toast = (resp, message) => {
                const content = resp.status + ' &CenterDot; ' + (resp.statusText || message);
                let toaster = doc.getElementById('oer89trJ');
                const color = resp.status === 200 ? 'success' : (resp.status > 399 ? 'danger' : 'info');
                !toaster || toaster.remove();
                toaster = doc.createElement('div');
                toaster.id = 'oer89trJ';
                toaster.innerHTML = content;
                toaster.className = 'toaster pos-tc width-md-5 width-sm-10 color-' + color;
                doc.body.appendChild(toaster);
                setTimeout(() => {
                    toaster.style.transform = 'translateY(-100%)';
                    toaster.addEventListener('transitionend', e => toaster.remove());
                }, 3000 + toaster.innerText.length * 64);
            };
            Shani.on('error', e => toast(e.detail, 'No connection to server. Try again.'));
            Shani.on('redirect', e => toast(e.detail, 'Redirecting...'));
        })();
        const Loader = (() => {
            const createLoader = loader => {
                loader.wrapper.forEach(node => {
                    const bar = doc.createElement('div'), wrapper = doc.createElement('div');
                    bar.className = 'progress';
                    !loader.specs.color || bar.style.setProperty('--color', loader.specs.color);
                    wrapper.id = loader.specs.id;
                    wrapper.className = 'progress-bar loader';
                    wrapper.appendChild(bar);
                    node.appendChild(wrapper);
                });
            };
            Shani.on('ui-loader', e => createLoader(e.detail));
        })();
    })();
})(document);