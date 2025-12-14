(doc => {
    'use strict';
    doc.addEventListener('DOMContentLoaded', () => {
        if (!window.Shani) {
            window.Shani = Utils.object({
                select: (selector, obj) => UDF.attr.set(selector, Utils.object(obj)),
                define: (name, val, replace) => {
                    const n = name.toLowerCase();
                    if (!replace && UDF.map.has(n)) {
                        console.warn(name + ' already exists.');
                    } else {
                        UDF.map.set(n, val);
                    }
                },
                on: Shani.on
            });
            Object.freeze(window.Shani);
            Object.freeze(UDF);
            doc.dispatchEvent(new Event('shani:init'));
        }
        Shanify(doc.body);
        Observers.mutate(doc.body);
    });
    const UDF = Object.setPrototypeOf({attr: new Map(), map: new Map()}, null);
    const Observers = (() => {
        const runScript = node => {
            if (Utils.nodeKeyExists(node, 'src')) {
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
            input2form(node) {
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
                        const isArray = Array.isArray(obj);
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
                    const isArray = Array.isArray(obj);
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
                    return '"' + (val !== null || val !== undefined ? (Array.isArray(val) ? val.join('|') : val) : '') + '"';
                };
                obj = toJson(obj);
                const data = Array.isArray(obj) ? obj : [obj];
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
        const setInputData = (target, output, data, params) => {
            const value = Utils.getNodeValue(target, output);
            if (params.mode === 'prepend') {
                Utils.setNodeValue(target, output, data + value);
            } else if (params.mode === 'append') {
                Utils.setNodeValue(target, output, value + data);
            } else {
                insertData(target, data, params);
            }
        };
        const insertData = (target, data, params) => {
            const modes = {
                prepend: 'afterbegin', append: 'beforeend',
                before: 'beforebegin', after: 'afterend'
            };
            const key = 'insertAdjacent' + (params.escape ? 'Text' : 'HTML');
            target[key](modes[params.mode], data);
        };
        const handleDataInsertion = (target, resp, params) => {
            if (params.mode !== 'discard') {
                const data = params.outf ? Utils.calludf(params.outf, resp) : resp.body || '';
                const isInput = ['INPUT', 'TEXTAREA'].includes(target.tagName);
                const output = params.output || isInput ? 'value' : params.escape ? 'textContent' : 'innerHTML';
                if (params.mode === 'replace') {
                    return Utils.setNodeValue(target, output, data);
                }
                return isInput ? setInputData(target, output, data, params) : insertData(target, data, params);
            }
        };
        return {
            processResponse(shani, target, response, params) {
                Utils.trigger(shani, 'data', response);
                target.forEach(node => handleDataInsertion(node, response, params));
            }
        };
    })();
    const Shani = (() => {
        const Obj = function (node, e) {
            this.event = e;
            this.emitter = node;
            setShaniAttrs(this, node);
            this.poll = Utils.object();
            this.actions = collectActions(node);
            this.headers = new Headers(this.headers);
            /**for HTTP read() and write() sync become false**/
            this.sync = true;
        };
        const setShaniAttrs = (shani, node) => {
            ['headers', 'cache', 'http', 'history', 'debug'].forEach(a => {
                shani[a] = Parser.params(node, node.getAttribute('shani-' + a));
            });
        };
        const collectActions = node => {
            const events = Utils.splitEvents(node), map = new Map();
            for (const e in events) {
                const ps = Parser.toArray(events[e], SEP_EVT_SELECTOR), selector = ps[1];
                const parts = Parser.toArray(ps[0], SEP_EVT_ACTION);
                const fnparams = parts[1] || parts[0];
                const evtparams = parts.length > 1 ? parts[0] : null;
                const pos = fnparams.search(SEP_ACTION);
                const fn = pos > 0 ? fnparams.slice(0, pos) : fnparams;
                const paramstr = pos > 0 ? fnparams.slice(pos + 1) : null;
                map.set(e, Utils.object({
                    fn: fn.trim().toLowerCase(), paramstr, selector,
                    ep: Parser.params(node, evtparams)
                }));
            }
            return map;
        };
        const onConnect = shani => clearTimeout(shani.timeoutId);

        /**
         * Send HTTP request
         */
        const sendReq = (shani, method, obj) => {
            shani.sync = false;
            let em = shani.emitter;
            const target = obj.targets, params = Parser.params(em, obj.paramstr);
            params.mode ||= 'replace';
            const timeout = shani.http.timeout;
            if (timeout) {
                shani.timeoutId = setTimeout(() => Utils.trigger(shani, 'timeout'), Utils.time2ms(timeout));
            }
            if (shani.http.scheme === 'sse') {
                return HttpClient.sse(shani, target, params, onConnect);
            }
            if ('scheme' in shani.http) {
                return HttpClient.wsocket(shani, target, params, onConnect);
            }
            if (em.tagName === 'FORM') {
                em = em.querySelector('fieldset') || em;
            }
            HttpClient.http(shani, shani.http.method || method, request => {
                Utils.setNodeValue(em, 'disabled', true);
                Utils.trigger(shani, 'httpstart', {request});
            }, () => {
                onConnect(shani);
                Utils.setNodeValue(em, 'disabled', false);
                Utils.trigger(shani, 'httpend');
            }, resp => onSuccessReq(shani, target, resp, params), err => {
                const status = err.name === 'AbortError' ? 408 : 400;
                if (shani.poll.limit !== null) {
                    shani.poll.limit++;
                }
                const resp = Utils.object({headers: new Headers(), status, body: ''});
                onSuccessReq(shani, target, resp, params);
            });
        };
        const onSuccessReq = (shani, target, resp, params) => {
            const text = Utils.code2text(resp.status);
            Utils.trigger(shani, '' + resp.status, resp);
            Utils.trigger(shani, text, resp);
            HTML.processResponse(shani, target, resp, params);
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
        const compute = (lval, nv, sign) => {
            const rval = (nv.endsWith('%') ? lval * 0.01 : 1) * parseFloat(nv);
            switch (sign) {
                case '+':
                    return lval + rval;
                case '-':
                    return lval - rval;
                case '*':
                    return lval * rval;
                case '/':
                    return lval / rval;
                case '%':
                    return lval % rval;
                case '^':
                    return Math.pow(lval, rval);
                default:
                    throw new Error('valid math operators are: +-*/%^');
            }
        };
        const parseNumber = (val, allowPercent) => {
            val ||=  '0';
            const num = val.replace(/[^\d%.-]/g, '');
            if (/^-?\d+(\.\d+)?%?$/.test(num)) {
                return allowPercent ? num : parseFloat(num);
            }
            throw new Error('Invalid number "' + val + '"');
        };
        /**
         * Move this element to a specified position, to another destination.
         * If a position is not given then the element is placed to the end.
         */
        const moveNode = (srcNode, target, params, clone) => {
            const index = parseInt(params.pos), len = target.children.length + 1;
            const offset = index > 0 ? index - 1 : index + len;
            if (Math.abs(index) <= len && index !== 0) {
                const n = clone ? srcNode.cloneNode(true) : srcNode;
                target.insertBefore(n, target.children[offset]);
                !clone || clone(n);
            }
        };
        Obj.prototype = {
            /**
             * Read content from server.
             */
            read(obj) {
                if (this.history === true) {
                    history.pushState(null, '', this.url);
                }
                sendReq(this, 'GET', obj);
            },
            /**
             * Write content to server
             */
            write(obj) {
                sendReq(this, 'POST', obj);
            },
            trigger(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    for (const key in p) {
                        target.dispatchEvent(new Event(key, {bubbles: true}));
                    }
                });
            },
            /**
             * Remove node from DOM
             */
            close(obj) {
                if (obj.selector) {
                    const selector = Utils.resolveVariable(this.emitter, obj.selector);
                    const parent = Utils.getParentNode(this.emitter, selector);
                    if (parent) {
                        return Utils.removeNode(parent);
                    }
                    obj.targets.forEach(Utils.removeNode);
                }
            },
            print(obj) {
                if (window.print instanceof Function) {
                    obj.targets.forEach(target => {
                        const p = Parser.params(target, obj.paramstr);
                        const cover = getCover(obj.targets, 'size:' + (p.size || 'auto'));
                        window.print();
                        cover.remove();
                    });
                }
            },
            /**
             * Offline search
             */
            search(obj) {
                const text = this.emitter.value.trim().toLowerCase();
                obj.targets.forEach(target => {
                    for (const row of target.children) {
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
            nodecopyto(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    moveNode(this.emitter, target, p, node => {
                        node.querySelectorAll('[id]').forEach(el => {
                            const id = Utils.getId();
                            node.querySelectorAll('[for="' + el.id + '"]').forEach(label => label.for = id);
                            el.id = id;
                        });
                    });
                });
            },
            nodemoveto(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    moveNode(this.emitter, target, p);
                });
            },
            cssadd(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    for (const key in p) {
                        target.classList.add(key);
                    }
                });
            },
            cssrmv(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    for (const key in p) {
                        target.classList.remove(key);
                    }
                });
            },
            cssreplace(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    for (const key in p) {
                        target.classList.replace(key, p[key]);
                    }
                });
            },
            csstoggle(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    for (const key in p) {
                        if (key === p[key]) {
                            target.classList.toggle(key);
                        } else if (target.classList.contains(key)) {
                            target.classList.replace(key, p[key]);
                        } else if (target.classList.contains(p[key])) {
                            target.classList.replace(p[key], key);
                        }
                    }
                });
            },
            cssexists(obj) {
                for (const node of obj.targets) {
                    const p = Parser.params(node, obj.paramstr);
                    for (const key in p) {
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
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    for (const key in p) {
                        Utils.removeNodeKey(target, key);
                    }
                });
            },
            propexists(obj) {
                for (const node of obj.targets) {
                    const p = Parser.params(node, obj.paramstr);
                    for (const k in p) {
                        if (!Utils.nodeKeyExists(node, k)) {
                            return false;
                        }
                    }
                }
                return true;
            },
            propbind(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    for (const k in p) {
                        Parser.bindProperty(target, k, p[k]);
                    }
                });
            },
            numbercalc(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    const lval = parseNumber(p.lvalue), rval = parseNumber(p.rvalue, true);
                    const result = compute(lval, rval, p.operator) || 0;
                    Utils.setNodeValue(target, p.output, result);
                });
            },
            numberaccumulate(obj) {
                const p = Parser.params(this.emitter, obj.paramstr);
                let result = parseFloat(p.initial) || 0;
                obj.targets.forEach(target => {
                    const param = Parser.params(target, obj.paramstr);
                    const value = parseNumber(param.input, true);
                    result = compute(result, value, param.operator);
                });
                Utils.setNodeValue(this.emitter, p.output, result);
            },
            numberformat(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    const result = parseNumber(p.input).toLocaleString(undefined, {
                        maximumFractionDigits: p.maxdecimals || 2,
                        minimumFractionDigits: p.mindecimals || 0
                    });
                    Utils.setNodeValue(target, p.output, result);
                });
            },
            affix(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    const prefix = p.prefix || '', suffix = p.suffix || '';
                    Utils.setNodeValue(target, p.output, prefix + p.input + suffix);
                });
            },
            transform(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    const result = Utils.calludf(p.transformer, [p.input, target]);
                    Utils.setNodeValue(target, p.output, result);
                });
            },
            saveas(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr), a = doc.createElement('a');
                    const type = p.type || obj.data.headers.get('content-type');
                    a.href = URL.createObjectURL(new Blob([obj.data.body], {type}));
                    a.download = p.name;
                    a.click();
                    URL.revokeObjectURL(a.href);
                });
            },
            /**
             * Create HTML modal element
             */
            modalcreate(obj) {
                obj.targets.forEach(target => {
                    Utils.trigger(this, 'ui-modal', {specs: Parser.params(target, obj.paramstr)});
                });
            },
            loadercreate(obj) {
                obj.targets.forEach(target => {
                    Utils.trigger(this, 'ui-loader', {specs: Parser.params(target, obj.paramstr), wrapper: obj.targets});
                });
            },
            loaderrmv(obj) {
                Utils.trigger(this, 'ui-loader-rmv', {wrapper: obj.targets});
            },
            /**
             * Cancel ongoing HTTP connection
             */
            abortconn(obj) {
                obj.targets.forEach(target => {
                    const p = Parser.params(target, obj.paramstr);
                    if (!p.name) {
                        for (const key in Utils.connection) {
                            Utils.closeConn(key);
                        }
                    } else {
                        Utils.closeConn(p.name);
                    }
                });
            }
        };
        return {
            create(node, event) {
                if (!Utils.getNodeValue(node, 'disabled')) {
                    const shani = new Obj(node, event);
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
                Shani.create(node, e);
            }
        };
        const getTargetNode = (node, evt) => {
            if (node) {
                if (Utils.eventExists(node, evt)) {
                    return node;
                }
                return getTargetNode(Utils.getParentNode(node, '[shani-on]'), evt);
            }
            return null;
        };
        const addListener = node => {
            const events = Utils.splitEvents(node);
            for (const e in events) {
                doc.addEventListener(e, listen);
                if (e === 'load') {
                    node.dispatchEvent(new Event(e, {bubbles: true}));
                } else if (e === 'demand') {
                    Observers.intersect(node);
                }
            }
        };
        const setUserAttributes = root => {
            for (let sel of UDF.attr) {
                if (root.matches(sel[0])) {
                    addAttributes(root, sel[1]);
                }
                const nodes = root.querySelectorAll(sel[0]);
                nodes.forEach(node => addAttributes(node, sel[1]));
            }
        };
        const addAttributes = (node, values) => {
            for (const key in values) {
                let val = Utils.getNodeValue(node, key);
                if (val === undefined) {
                    val = values[key];
                } else if (['shani-http', 'shani-cache', 'shani-headers'].includes(key)) {
                    val = mergeParams(val, values[key], SEP_PARAM);
                } else if (key === 'shani-on') {
                    val = mergeEvents(val, values[key]);
                }
                Utils.setNodeValue(node, key, val);
            }
        };
        const mergeParams = (params1, params2, sep) => {
            const p1 = Parser.toArray(params1, sep), p2 = Parser.toArray(params2, sep);
            p1.forEach(v => p2.includes(v) || p2.push(v));
            return p2.join(sep);
        };
        const mergeEvents = (evt1, evt2) => {
            const p2 = Parser.events(evt2);
            Object.assign(p2, Parser.events(evt1));
            let str = '';
            for (const k in p2) {
                str += SEP_EVENT + k + p2[k];
            }
            return str.slice(SEP_EVENT.length);
        };
        return root => {
            setUserAttributes(root);
            addListener(root);
            root.querySelectorAll('[shani-on]').forEach(addListener);
        };
    })();
    const SEP_EVT_ACTION = '::', SEP_EVENT = ';', SEP_EVT_SELECTOR = '>>', SEP_ACTION = /\s/;
    const SEP_PARAM = '&', SEP_KEY_VAL = ':', SEP_VAR = '@', SEP_NEG = '!';
    const Utils = (() => {
        /**
         * Timer for a delayed actions
         * @type Map
         */
        const TIMER = new Map();
        const MEMO = Object.setPrototypeOf({}, null);
        const prepareCall = (shani, action, data, evt) => {
            !isSyncEvent(shani, evt) || clearTimeout(TIMER.get(shani.emitter));
            TIMER.delete(shani.emitter);
            callNext(shani, action, data);
            doc.dispatchEvent(new CustomEvent('shani:on:' + evt, {detail: data}));
            if (isSyncEvent(shani, evt)) {
                TIMER.set(shani.emitter, recall(shani, data, shani.event.type));
            }
        };
        const shouldSchedule = shani => {
            const connected = shani.emitter.isConnected;
            const underLimit = shani.poll.steps && (shani.poll.limit === null || (--shani.poll.limit) > 0);
            return connected && underLimit;
        };
        const recall = (shani, data, evt) => {
            if (shouldSchedule(shani)) {
                const action = shani.actions.get(evt);
                return setTimeout(prepareCall, shani.poll.steps, shani, action, data, evt);
            }
        };
        const isSyncEvent = (shani, evt) => evt === 'httpend' || (shani.sync && evt === shani.event.type);
        const callNext = (shani, action, data) => {
            const cb = action ? UDF.map.get(action.fn) || shani[action.fn] : null;
            if (cb instanceof Function) {
                const targets = action.selector ? Utils.getCachedNodes(action.selector) : [shani.emitter];
                const p = Utils.object({
                    paramstr: action.paramstr, evtparams: action.ep,
                    selector: action.selector, targets, data
                });
                shani.debug !== true || console.log(p);
                cb.call(shani, p) === false || Utils.trigger(shani, action.fn, data);
            }
        };
        const flipValue = val => typeof val === 'boolean' ? !val : '';
        return{
            removeNode(node) {
                node.style.opacity = 0;
                node.addEventListener('transitionend', () => node.remove());
            },
            getId() {
                return Date.now().toString(36);
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
            },
            getSubtype(header) {
                if (header) {
                    const subtype = header.slice(header.indexOf('/') + 1).split(';')[0];
                    const plusPos = subtype.indexOf('+');
                    return plusPos < 0 ? subtype : subtype.slice(plusPos + 1);
                }
                return null;
            },
            time2ms(time) {
                if (/^\s*\d+(\.\d+)?[smhdy]\s*$/.test(time)) {
                    const TIME_UNITS = {
                        s: 1, m: 60, h: 3600, d: 24 * 3600, y: 24 * 3600 * 365
                    };
                    time = time.trim();
                    const unit = time.slice(-1).toLowerCase();
                    const val = parseFloat(time.slice(0, -1));
                    return Math.round(TIME_UNITS[unit] * val * 1000);
                }
                throw new Error('Invalid duration ' + time);
            },
            trigger(shani, evt, data = {}) {
                const action = shani.actions.get(evt);
                data = Utils.object(data);
                data.shani = shani;
                if (action) {
                    const p = action.ep;
                    if (p.steps) {
                        shani.poll.steps = Utils.time2ms(p.steps);
                        shani.poll.limit = parseInt(p.limit) || null;
                    }
                    if (p.delay) {
                        clearTimeout(TIMER.get(shani.emitter));
                        const id = setTimeout(prepareCall, Utils.time2ms(p.delay), shani, action, data, evt);
                        return TIMER.set(shani.emitter, id);
                    }
                }
                prepareCall(shani, action, data, evt);
            },
            resolveVariable(node, str) {
                if (typeof str === 'string') {
                    if (str.startsWith(SEP_VAR)) {
                        const key = str.slice(SEP_VAR.length), flip = key.charAt(0) === SEP_NEG;
                        const value = Utils.getNodeValue(node, flip ? key.slice(SEP_NEG.length) : key);
                        return Utils.resolveVariable(node, flip ? flipValue(value) : value);
                    }
                    return str.charAt(0) === '\\' ? str.slice(1) : str;
                }
                return str;
            },
            removeNodeKey(node, key) {
                if (key in node) {
                    const type = typeof node[key];
                    node[key] = type === 'boolean' ? false : type === 'number' ? 0 : '';
                } else {
                    node.removeAttribute(key);
                }
            },
            object(o) {
                return Object.setPrototypeOf(o || {}, null);
            },
            setNodeValue(node, key, val) {
                if (key in node) {
                    node[key] = val;
                } else if (val === false) {
                    node.removeAttribute(key);
                } else {
                    node.setAttribute(key, val === true ? key : val);
                }
            },
            calludf(name, args, thisArg) {
                const v = UDF.map.get(name);
                return v instanceof Function ? v.apply(thisArg, args) : v;
            },
            getNodeValue(node, key) {
                if (typeof key === 'string') {
                    let val = key in node ? node[key] : node.hasAttribute(key) ? node.getAttribute(key) : Utils.calludf(key, node);
                    return key === val || (val === 'true' || val === 'false' ? false : val);
                }
                return key;
            },
            nodeKeyExists(node, key) {
                return  key in node || node.hasAttribute(key);
            },
            splitEvents(node) {
                return Parser.events(node.getAttribute('shani-on'));
            },
            eventExists(node, evt) {
                const events = Utils.splitEvents(node);
                for (const e in events) {
                    if (e === evt) {
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
            getCachedNodes(key) {
                if (!MEMO[key] || Array.from(MEMO[key]).some(n => !n.isConnected)) {
                    MEMO[key] = doc.querySelectorAll(key);
                }
                return MEMO[key];
            }
        };
    })();
    const Parser = (() => {
        const splitPair = (str, sep, def = null) => {
            const pos = str.indexOf(sep);
            return Utils.object({
                k: pos > 0 ? str.slice(0, pos) : def,
                v: pos > 0 ? str.slice(pos + sep.length).trim() : def
            });
        };
        const isPlaceHolder = str => {//selector@prop
            return typeof str === 'string' && str.indexOf(SEP_VAR) > 0
                    && str.indexOf(SEP_KEY_VAL) < 0 && str.charAt(0) !== '\\';
        };
        const getEventFromString = (str, idx) => {
            const name = str.slice(0, idx);
            const idx2 = name.search(SEP_ACTION);
            return name.slice(0, idx2 > 0 ? idx2 : idx).trim();
        };
        return {
            params(node, str) {
                const obj = Utils.object();
                if (typeof str === 'string') {
                    const pairs = Parser.toArray(str, SEP_PARAM);
                    pairs.forEach(p => {
                        const pair = splitPair(p, SEP_KEY_VAL, p);
                        if (pair.k === p && pair.k.indexOf(SEP_VAR) > -1) {//@prop, #id@prop
                            const value = Utils.resolveVariable(node, pair.k);
                            Object.assign(obj, Parser.params(node, Parser.variable(value)));
                        } else {
                            obj[pair.k] = Parser.variable(Utils.resolveVariable(node, pair.v));
                        }
                    });
                }
                return obj;
            },
            variable(str, cb) {
                if (!isPlaceHolder(str)) {
                    return cb ? cb(str) : str;
                }
                const pair = splitPair(str, SEP_VAR), val = SEP_VAR + pair.v;
                if (cb) {
                    return Utils.getCachedNodes(pair.k).forEach(r => {
                        const value = Parser.variable(Utils.resolveVariable(r, val));
                        cb(value, r);
                    });
                }
                const value = Utils.resolveVariable(doc.querySelector(pair.k), val);
                return Parser.variable(value);
            },
            events(str) {
                const arr = Parser.toArray(str, SEP_EVENT), obj = Utils.object();
                for (const e of arr) {
                    const idx = e.indexOf(SEP_EVT_ACTION);
                    if (idx > 0) {
                        const name = getEventFromString(e, idx);
                        obj[name] = e.slice(name.length).trim();
                    } else if (isPlaceHolder(e)) {
                        const val = Parser.variable(e);
                        Object.assign(obj, Parser.events(val));
                    } else {
                        throw new Error('Invalid event string: ' + e);
                    }
                }
                return obj;
            },
            toArray(str, sep) {
                return str ? str.split(sep).map(s => s.trim()).filter(a => a.length !== 0) : [];
            },
            bindProperty(node, prop, val) {
                const value = Parser.variable(val), pair = splitPair(prop, SEP_VAR, prop);
                const sources = isPlaceHolder(prop) ? Utils.getCachedNodes(pair.k) : [node];
                const key = pair.v.startsWith(SEP_VAR) ? pair.v.slice(SEP_VAR.length) : pair.v;
                sources.forEach(source => Utils.setNodeValue(source, key, value));
            }
        };
    })();
    const HttpClient = (() => {
        const createHttpPayload = (shani, method) => {
            const fd = Convertor.input2form(shani.emitter);
            const payload = Utils.object({
                url: shani.http.url, data: null, headers: shani.headers
            });
            if (fd) {
                if (method.toUpperCase() === 'GET') {
                    const mark = shani.http.url.indexOf('?') < 0 ? '?' : '&';
                    payload.url = shani.http.url + mark + Convertor.urlencoded(fd);
                } else {
                    const type = Utils.getSubtype(payload.headers.get('content-type'));
                    payload.data = Convertor.form2(fd, type);
                }
            }
            return payload;
        };
        const createWSocketPayload = shani => {
            const payload = Utils.object({url: shani.http.url, data: null, headers: shani.headers});
            const formdata = Convertor.input2form(shani.emitter);
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
                const p = shani.cache;
                if (p.age) {
                    req.cacheAge = Utils.time2ms(p.age);
                    req.cacheName = p.name || 'pubcache';
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
            sse(shani, target, params, onConnect) {
                const name = shani.http.conn || 'sse';
                Utils.connection[name] = new EventSource(shani.http.url, {
                    withCredentials: shani.http.credentials === 'include'
                });
                const on = (e, cb) => Utils.connection[name].addEventListener(e, cb);
                on('message', e => {
                    const resp = Utils.object({
                        body: e.data || '', headers: new Headers({'content-type': 'text/html'})
                    });
                    HTML.processResponse(shani, target, resp, params);
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
            wsocket(shani, target, params, onConnect) {
                const host = shani.http.url.contains('://') ? '' : shani.http.scheme + '://' + location.host;
                const name = shani.http.conn || 'ws';
                Utils.connection[name] = new WebSocket(host + shani.http.url);
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
                    HTML.processResponse(shani, target, resp, params);
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
        const selectNode = (children, activeChild, cssClass) => {
            for (const child of children) {
                child.classList.remove(cssClass);
            }
            activeChild.classList.add(cssClass);
        };
        const Carousel = (() => {
            const rotateItems = (carousel, cb) => {
                const children = carousel.querySelectorAll('.carousel-body>*');
                const currentActive = carousel.querySelector('.carousel-body>.active');
                const currentIdx = Array.from(children).indexOf(currentActive);
                const nextIdx = cb(children.length, currentIdx);
                selectNode(children, children[nextIdx], 'active');
            };
            const rotate = () => {
                Utils.getCachedNodes('.carousel').forEach(node => {
                    if (node.getAttribute('ui-attr') === 'auto') {
                        rotateItems(node, (total, idx) => (idx + 1) % total);
                    }
                });
                setTimeout(rotate, 5000);
            };
            doc.addEventListener('click', e => {
                const cls = e.target.classList;
                if (cls && cls.contains('carousel-next')) {
                    // Calculate next index: cycle to 0 if at end.
                    rotateItems(e.target.parentElement, (total, idx) => (idx + 1) % total);
                } else if (cls && cls.contains('carousel-prev')) {
                    // Calculate previous index: add total length to avoid negative modulus.
                    rotateItems(e.target.parentElement, (total, idx) => (idx - 1 + total) % total);
                }
            });
            setTimeout(rotate, 5000);
        })();
        const Selection = (() => {
            const getEmittingChild = (target, root) => {
                while (target !== root && target.parentElement !== root) {
                    target = target.parentElement;
                }
                return target;
            };
            doc.addEventListener('click', e => {
                const parent = Utils.getParentNode(e.target, '.accordion,.menubar');
                if (parent) {
                    const child = getEmittingChild(e.target, parent);
                    selectNode(parent.children, child, 'active');
                }
            });
        })();
        const Modal = (() => {
            const COVER = 'modal-background';
            const getCloseBtn = classList => {
                if (classList) {
                    const btn = doc.createElement('button');
                    btn.className = 'button button-times ' + classList;
                    Utils.setNodeValue(btn, 'type', 'button');
                    Utils.setNodeValue(btn, 'shani-on', 'click' + SEP_EVT_ACTION + 'close' + SEP_EVT_SELECTOR + '.' + COVER);
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
                const color = loader.specs.color, size = loader.specs.size, thickness = loader.specs.thickness;
                loader.wrapper.forEach(node => {
                    !color || node.style.setProperty('--loader-color', color);
                    !size || node.style.setProperty('--loader-size', size);
                    !thickness || node.style.setProperty('--loader-thickness', thickness);
                    node.classList.add(loader.specs.name);
                });
            };
            const rmvLoader = loader => {
                loader.wrapper.forEach(node => {
                    ['--loader-color', '--loader-size', '--loader-thickness'].forEach(p => node.style.removeProperty(p));
                    node.classList.remove('loader-spin', 'loader-bottom', 'loader-top');
                });
            };
            Shani.on('ui-loader', e => createLoader(e.detail));
            Shani.on('ui-loader-rmv', e => rmvLoader(e.detail));
        })();
    })();
})(document);