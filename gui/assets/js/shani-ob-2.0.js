(doc => {
    'use strict';
    doc.addEventListener('DOMContentLoaded', () => {
        if (!window.Shani) {
            window.Shani = Utils.object({
                select: (selector, obj) => USER_DATA.attr.set(selector, Utils.object(obj)),
                action: (name, fn) => {
                    const n = name.toLowerCase();
                    if (USER_DATA.fn.has(n)) {
                        console.warn(n + ' already exists.');
                    } else {
                        USER_DATA.fn.set(n, fn);
                    }
                },
                on: Shani.on
            });
            Object.freeze(window.Shani);
            Object.freeze(USER_DATA);
            doc.dispatchEvent(new Event('shani:init'));
        }
        Shanify(doc.body);
        Observers.mutate(doc.body);
    });
    const USER_DATA = Object.setPrototypeOf({attr: new Map(), fn: new Map()}, null);
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
        const toJson = data => typeof data === 'string' ? Utils.object(JSON.parse(data)) : data;
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
                return '<?xml version="1.0"?>' + convert(toJson(data), 'data');
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
                return convert(toJson(data), 0).trim();
            },
            json2csv(obj) {
                const enclose = val => {
                    return '"' + (val !== null || val !== undefined ? (val instanceof Array ? val.join('|') : val) : '') + '"';
                };
                obj = toJson(obj);
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
        const Obj = function (node, e) {
            this.event = e;
            this.emitter = node;
            this.poll = Utils.object();
            this.url = node.getAttribute('href') || node.getAttribute('action');
            setAttribs(this, node, Shani.SHANI_ATTR, 'shani-');
            setAttribs(this, node, Shani.HTML_ATTR, '');
            /**/
            this.actions = collectActions(node.getAttribute('shani-on'));
            this.headers = new Headers(Utils.explode(this.headers));
            this.http = Utils.explode(this.http);
        };
        const setAttribs = (shani, node, attrs, prefix) => {
            attrs.forEach(a => shani[a] = node.getAttribute(prefix + a));
        };
        const collectActions = evtStr => {
            const events = Utils.splitEvents(evtStr), map = new Map();
            for (const evt in events) {
                if (events[evt] === null) {
                    throw new Error('Syntax error on ' + evt);
                }
                const parts = events[evt].split(SEP_SELECTOR).map(s => s.trim());
                const pos = parts[0].search(SEP_FN), fn = pos > -1 ? parts[0].slice(0, pos) : parts[0];
                const params = pos > -1 ? Utils.explode(parts[0].slice(pos + SEP_FN.length)) : null;
                const ep = evt.split(SEP_FN).map(s => s.trim()), evtParams = Utils.explode(ep[1]);
                map.set(ep[0], Utils.object({fn: fn.trim().toLowerCase(), params, evtParams, selector: parts[1]}));
            }
            return map;
        };
        const onConnect = shani => {
            if (shani.http.timerId) {
                clearTimeout(shani.http.timerId);
            }
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
                return HttpClient.sse(shani, target, mode, onConnect);
            }
            if ('scheme' in shani.http) {
                return HttpClient.wsocket(shani, target, mode, onConnect);
            }
            let em = shani.emitter;
            if (em.tagName === 'FORM') {
                em = em.querySelector('fieldset') || em;
            }
            HttpClient.http(shani, shani.method || method, request => {
                em.setAttribute('disabled', '');
                Utils.trigger(shani, 'httpstart', {request});
            }, () => {
                onConnect(shani);
                em.removeAttribute('disabled');
                Utils.trigger(shani, 'httpend');
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
                const node = doc.createElement(obj.params['_tag']);
                delete obj.params['_tag'];
                for (const key in obj.params) {
                    node.setAttribute(key, obj.params[key] || '');
                }
                handler(target, node);
            });
        };
        const setNodeValue = (node, key, val) => {
            key in node ? node[key] = val : node.setAttribute(key, val);
        };
        const bindTargetNodeValue = (obj, shani, flip) => {
            for (const key in obj.params) {
                let val = obj.params[key];
                val = flip === null ? key === val || val : getNodeValue(shani.emitter, val || key, flip);
                obj.targets.forEach(node => setNodeValue(node, key, val));
            }
        };
        const bindSourceNodeValue = (obj, shani, flip) => {
            obj.targets.forEach(node => {
                for (const key in obj.params) {
                    const val = getNodeValue(node, obj.params[key] || key, flip);
                    setNodeValue(shani.emitter, key, val);
                }
            });
        };
        const getNodeValue = (node, key, flip) => {
            const val = key in node ? node[key] : node.getAttribute(key);
            return !flip ? val : typeof val === 'boolean' ? !val : val || '';
        };
        const compute = (ov, nv, sign) => {
            const value = nv.search('%') === nv.length - 1 ? ov * parseFloat(nv) * 0.01 : parseFloat(nv);
            switch (sign) {
                case '+':
                    return ov + value;
                case '-':
                    return ov - value;
                case '*':
                    return ov * value;
                case '/':
                    return ov / value;
                case '%':
                    return ov % value;
                case '^':
                    return Math.pow(ov, value);
                default:
                    throw new Error('valid math signs are: +-*/%^');
            }
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
                    obj.targets.forEach(Utils.removeNode);
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
                obj.targets.forEach(Utils.removeNode);
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
                        } else {
                            node.removeAttribute(key);
                        }
                    }
                });
            },
            /**
             * Check if property value is exactly equal to the given value
             */
            propequal(obj) {
                for (const node of obj.targets) {
                    for (const key in obj.params) {
                        const val = getNodeValue(node, key);
                        const newVal = obj.params[key] || typeof val === 'boolean' || '';
                        if (val !== newVal) {
                            return false;
                        }
                    }
                }
                return true;
            },
            /**
             * this.emitter value = that.node value
             */
            propbindthis(obj) {
                bindSourceNodeValue(obj, this);
            },
            /**
             * this.emitter value = !that.node value
             */
            proptogglethis(obj) {
                bindSourceNodeValue(obj, this, true);
            },
            /**
             * Add properties to extisting node
             */
            propset(obj) {
                bindTargetNodeValue(obj, null, null);
            },
            /**
             * that.node value = this.emitter value
             */
            propbind(obj) {
                bindTargetNodeValue(obj, this, false);
            },
            /**
             * that.node value = !this.emitter value
             */
            proptoggle(obj) {
                bindTargetNodeValue(obj, this, true);
            },
            propexists(obj) {
                for (const node of obj.targets) {
                    for (const p in obj.params) {
                        if (!(p in node || node.hasAttribute(p))) {
                            return false;
                        }
                    }
                }
                return true;
            },
            propcomputeby(obj) {
                const tkey = obj.params.thatprop || obj.params.thisprop;
                const p = obj.params.precision || 4, f = obj.params.format || true;
                const val = getNodeValue(this.emitter, obj.params.thisprop).trim().replace(/,/, '');
                if (!(/^-?\d+(\.\d+)?%?$/.test(val))) {
                    throw new Error('Invalid number format: ' + val);
                }
                obj.targets.forEach(node => {
                    const oldVal = parseFloat(getNodeValue(node, tkey).replace(/,/, ''));
                    const newVal = compute(oldVal, val, obj.params.sign), nv = newVal.toFixed(p);
                    setNodeValue(node, tkey, f ? parseFloat(nv).toLocaleString() : nv);
                });
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
            modalcreate(obj) {
                Utils.trigger(this, 'ui-modal', {specs: obj.params});
            },
            loadercreate(obj) {
                Utils.trigger(this, 'ui-loader', {specs: obj.params, wrapper: obj.targets});
            },
            loaderrmv(obj) {
                Utils.trigger(this, 'ui-loader-rmv', {specs: obj.params, wrapper: obj.targets});
            },
            /**
             * Cancel ongoing HTTP connection
             */
            abortconn(obj) {
                if (!obj.params) {
                    for (const key in Utils.connection) {
                        Utils.closeConn(key);
                    }
                } else {
                    Utils.closeConn(obj.params.name);
                }
            }
        };
        return {
            HTML_ATTR: ['enctype', 'method'],
            SHANI_ATTR: ['headers', 'xss', 'inf', 'outf', 'cache', 'history', 'on', 'http'],
            create(node, event) {
                if (!node.hasAttribute('disabled')) {
                    const shani = new Obj(node, event);
                    const evt = Utils.getEventName(event.type);
                    const p = shani.actions.get(evt).evtParams;
                    if (p.steps) {
                        shani.poll.steps = Utils.time2ms(p.steps) || -1;
                        shani.poll.limit = parseInt(p.limit) || null;
                    }
                    Utils.trigger(shani, evt);
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
                Shani.create(node, e);
            }
        };
        const getTargetNode = (node, evt) => {
            if (node) {
                const evtStr = node.getAttribute('shani-on');
                if (Utils.eventExists(evt, evtStr)) {
                    return node;
                }
                return getTargetNode(Utils.getParentNode(node, '[shani-on]'), evt);
            }
            return null;
        };
        const addListener = node => {
            const events = Utils.splitEvents(node.getAttribute('shani-on'));
            for (const evt in events) {
                const e = Utils.getEventFromString(evt);
                doc.addEventListener(e, listen);
                if (e === 'load') {
                    node.dispatchEvent(new Event(e, {bubbles: true}));
                } else if (e === 'demand') {
                    Observers.intersect(node);
                }
            }
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
                    value = mergeParams(value, obj[key], SEP_PARAM, SEP_VAL);
                } else if (key === 'shani-on') {
                    value = mergeParams(value, obj[key], SEP_EVT, SEP_ACTION);
                }
                node.setAttribute(key, value);
            }
        };
        const mergeParams = (oldVal, newVal, sep1, sep2) => {
            const ov = Utils.explode(oldVal, sep1, sep2), nv = Utils.explode(newVal, sep1, sep2);
            for (const k in ov) {
                nv[k] = ov[k];
            }
            let str = '';
            for (const k in nv) {
                str += sep1 + k + sep2 + nv[k];
            }
            return str.slice(sep1.length);
        };
        return root => {
            setUserAttributes(root);
            addListener(root);
            root.querySelectorAll('[shani-on]').forEach(addListener);
        };
    })();
    const SEP_ACTION = '::', SEP_EVT = ';', SEP_PARAM = '&', SEP_VAL = ':', SEP_SELECTOR = '>>', SEP_FN = '<<';
    const Utils = (() => {
        const callNext = (shani, action, data) => {
            const cb = action ? USER_DATA.fn.get(action.fn) || shani[action.fn] : null;
            if (cb instanceof Function) {
                const targets = action.selector ? doc.querySelectorAll(action.selector) : [shani.emitter];
                const result = cb.call(shani, Utils.object({
                    emitter: shani.emitter, params: action.params,
                    selector: action.selector, targets, data
                }));
                result === false || Utils.trigger(shani, action.fn, data);
            }
        };
        const recall = (shani, data) => {
            if (shani.emitter.isConnected && shani.poll.steps > -1 && (!shani.poll.limit || (--shani.poll.limit) > 0)) {
                const evt = Utils.getEventName(shani.event.type), action = shani.actions.get(evt);
                setTimeout(prepareCall, shani.poll.steps, shani, action, data, evt);
            }
        };
        const prepareCall = (shani, action, data, evt) => {
            timer.delete(shani.emitter);
            callNext(shani, action, data);
            doc.dispatchEvent(new CustomEvent('shani:on:' + evt, {detail: data}));
            evt !== 'httpend' || recall(shani, data);
        };
        /**
         * Timer for a delayed actions
         * @type Map
         */
        const timer = new Map();
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
            getEventFromString(str) {
                return str.split(SEP_FN)[0].trim();
            },
            eventExists(evt, evtStr) {
                const events = Utils.splitEvents(evtStr);
                for (const e in events) {
                    if (Utils.getEventFromString(e) === evt) {
                        return true;
                    }
                }
                return false;
            },
            getParentNode(childNode, parentSelector) {
                const parent = childNode.parentElement;
                if (!parent || parent.matches(parentSelector)) {
                    return parent;
                }
                return Utils.getParentNode(parent, parentSelector);
            },
            explode(str, sep, keySep) {
                const map = Utils.object(), ksep = keySep || SEP_VAL;
                if (str) {
                    const pair = str.split(sep || SEP_PARAM).map(s => s.trim());
                    for (let val of pair) {
                        const pos = val.indexOf(ksep), key = pos > 0 ? val.slice(0, pos) : val;
                        if (key.length > 0) {
                            map[key] = pos > 0 ? val.slice(pos + ksep.length).trim() : null;
                        }
                    }
                }
                return map;
            },
            splitEvents(str) {
                return Utils.explode(str, SEP_EVT, SEP_ACTION);
            },
            object(o) {
                return Object.setPrototypeOf(o || {}, null);
            },
            trigger(shani, evt, data = {}) {
                const action = shani.actions.get(evt), delay = action?.evtParams?.delay;
                data = Utils.object(data);
                data.shani = shani;
                if (delay) {
                    clearTimeout(timer.get(shani.emitter));
                    const id = setTimeout(prepareCall, Utils.time2ms(delay), shani, action, data, evt);
                    timer.set(shani.emitter, id);
                } else
                    prepareCall(shani, action, data, evt);
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
                        return obj.get(keys[idx]).apply(thisArg, args);
                    }
                    return traverse(obj.get(keys[idx]), idx + 1);
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
                    throw new Error('Invalid duration ' + time);
                }
                return time;
            },
            connection: Object.setPrototypeOf({}, null),
            closeConn(name) {
                const cn = Utils.connection[name];
                if (cn) {
                    if (cn instanceof AbortController) {
                        cn.abort();
                    } else {
                        cn.close();
                    }
                }
            }
        };
    })();
    const HttpClient = (() => {
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
                req.conn = shani.http.conn || 'http';
                req.options = Utils.object({
                    headers: payload.headers,
                    body: payload.data,
                    method: method,
                    credentials: shani.http.credentials,
                    mode: shani.http.mode
                });
                onStart(req);
                FetchClient.send(payload.url, req, onSuccess, onError, onEnd);
            },
            sse(shani, target, mode, onConnect) {
                const name = shani.http.conn || 'sse';
                Utils.connection[name] = new EventSource(shani.url, {
                    withCredentials: shani.http.credentials === 'include'
                });
                const on = (e, cb) => Utils.connection[name].addEventListener(e, cb);
                on('message', e => {
                    const resp = Utils.object({
                        body: e.data || '', headers: new Headers({'content-type': 'text/html'})
                    });
                    HTML.processResponse(shani, target, resp, mode);
                });
                on('open', e => {
                    onConnect(shani);
                    Utils.trigger(shani, 'httpstart');
                });
                on('error', e => {
                    onConnect(shani);
                    Utils.trigger(shani, 'error');
                });
                on('close', e => Utils.trigger(shani, 'httpend'));
            },
            wsocket(shani, target, mode, onConnect) {
                const host = shani.url.contains('://') ? '' : shani.http.scheme + '://' + location.host;
                const name = shani.http.conn || 'ws';
                Utils.connection[name] = new WebSocket(host + shani.url);
                const on = (e, cb) => Utils.connection[name].addEventListener(e, cb);
                on('open', e => {
                    onConnect(shani);
                    const payload = createWSocketPayload(shani);
                    Utils.trigger(shani, 'httpstart', {request: payload});
                    Utils.connection[name].send(payload.data || '');
                });
                on('error', e => {
                    onConnect(shani);
                    Utils.trigger(shani, 'error');
                });
                on('message', e => {
                    const resp = Utils.object({body: e.data || '', headers: new Headers()});
                    HTML.processResponse(shani, target, resp, mode);
                });
                on('close', e => Utils.trigger(shani, 'httpend'));
            }
        };
    })();
    const FetchClient = (() => {
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
            if (!Utils.connection[req.conn] || Utils.connection[req.conn].signal.aborted) {
                Utils.connection[req.conn] = new AbortController();
            }
            req.options.signal = Utils.connection[req.conn].signal;
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
                    btn.setAttribute('shani-on', 'click' + SEP_ACTION + 'close' + SEP_SELECTOR + '.' + COVER);
                    btn.innerHTML = '&times;';
                    return btn;
                }
            };
            const createModal = specs => {
                const mdbg = doc.createElement('div'), modal = doc.createElement('div');
                const wrapper = doc.createElement('div');
                wrapper.id = specs.id;
                wrapper.className = 'full-size';
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
        const Loader = (() => {
            const createLoader = loader => {
                const {name, color, size} = loader.specs;
                loader.wrapper.forEach(node => {
                    !color || node.style.setProperty('--loader-color', color);
                    !size || node.style.setProperty('--loader-size', size);
                    node.classList.add(name);
                });
            };
            const rmvLoader = loader => {
                loader.wrapper.forEach(node => {
                    ['--loader-color', '--loader-size'].forEach(p => node.style.removeProperty(p));
                    node.classList.remove('loader-spin', 'loader-bottom', 'loader-top');
                });
            };
            Shani.on('ui-loader', e => createLoader(e.detail));
            Shani.on('ui-loader-rmv', e => rmvLoader(e.detail));
        })();
    })();
})(document);