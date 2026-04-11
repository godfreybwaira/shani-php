(doc => {
    'use strict';
    const SEP_EVT_ACTION = '->', SEP_EVENT = ';', SEP_EVT_SELECTOR = '>>', SEP_ACTION = /\s/;
    const SEP_PARAM = '&', SEP_KEY_VAL = ':', SEP_VAR = '@', SEP_NEG = '!', SEP_LIST = ',';
    const Selectors = new Map(), START_EVENT = 'httpstart', END_EVENT = 'httpend', PARENT_SELECTOR = '&';
    const ERROR_EVENT = 'error', INFO_EVENT = 'info', OFFLINE_EVENT = 'offline', REDIRECT_EVENT = 'redirect';
    const TIMEOUT_EVENT = 'timeout', DATA_EVENT = 'data', SUCCESS_EVENT = 'success';

    doc.addEventListener('DOMContentLoaded', () => {
        if (!window.Shani) {
            window.Shani = Utils.object({
                select: (selector, obj) => Selectors.set(selector, Utils.object(obj)),
                selectors: name => name === undefined ? Selectors : Selectors.get(name),
                define: Action.add,
                definitions: Action.asList,
                on: (event, cb) => doc.addEventListener('shani:on:' + event, cb)
            });
            Object.freeze(window.Shani);
            doc.dispatchEvent(new Event('shani:init'));
        }
        Shanify(doc.body);
        Observers.mutate(doc.body);
    });
    const Action = (acts => {
        return {
            add(name, value, replace) {
                const n = name.toLowerCase();
                if (!(n in acts) || replace) {
                    acts[n] = value;
                } else {
                    console.warn(name + ' already exists.');
                }
            },
            get: name => acts[name],
            asList(phrase) {
                const keys = Object.keys(acts);
                return phrase === undefined ? keys : keys.filter(v => v.includes(phrase));
            }
        };
    })(Object.create(null));
    const Observers = (() => {
        const runScript = node => {
            if (node.src.length > 0) {
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
        const demand = (changes, observer) => {
            for (let change of changes) {
                if (change.isIntersecting) {
                    change.target.dispatchEvent(new Event('demand', {bubbles: true}));
                    observer.disconnect();
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
            input2form(node, ts) {
                let fd = null;
                if (['SELECT', 'INPUT', 'TEXTAREA'].includes(node.tagName)) {
                    fd = new FormData();
                    if (!node.files) {
                        fd.append(node.name || 'value', node.value);
                    } else {
                        for (let f = 0; f < node.files.length; f++) {
                            fd.append(node.name || 'file[]', node.files[f]);
                        }
                    }
                } else {
                    node.tagName !== 'FORM' || (fd = new FormData(node));
                }
                return ts ? Utils.calludf(ts, [fd]) : fd;
            },
            form2json(fd) {
                const data = Utils.object(), keys = [];
                for (let input of fd) {
                    if (keys.includes(input[0])) {
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
                return '<?xml version="1.0"?>' + convert(toJson(data), 'root');
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
                    if (keys.includes(input[0])) {
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
    const HTMLResponse = (() => {
        const setInputData = (target, output, content, params) => {
            const value = Utils.getNodeValue(target, output);
            if (params.mode === 'prepend') {
                Utils.setNodeValue(target, output, content + value);
            } else if (params.mode === 'append') {
                Utils.setNodeValue(target, output, value + content);
            } else {
                insertData(target, content, params);
            }
        };
        const insertData = (target, content, params) => {
            const modes = {
                prepend: 'afterbegin', append: 'beforeend',
                before: 'beforebegin', after: 'afterend', swap: 'afterend'
            };
            const key = 'insertAdjacent' + (params.escape ? 'Text' : 'HTML');
            target[key](modes[params.mode], content);
            params.mode !== 'swap' || target.remove();
        };
        const handleDataInsertion = (target, resp, params) => {
            const body = params.outputf ? Utils.calludf(params.outputf, [resp, target]) : resp.body || '';
            if (body !== undefined) {
                const isInput = ['INPUT', 'TEXTAREA'].includes(target.tagName);
                const output = params.output || isInput ? 'value' : params.escape ? 'textContent' : 'innerHTML';
                let content = body instanceof Element ? body.outerHTML : typeof body === 'object' ? JSON.stringify(body, null, 4) : body;
                if (params.wrapper) {
                    const wrapper = doc.createElement(params.wrapper);
                    Utils.setNodeValue(wrapper, output, content);
                    content = wrapper.outerHTML;
                }
                if (params.mode === 'replace') {
                    return Utils.setNodeValue(target, output, content);
                }
                isInput ? setInputData(target, output, content, params) : insertData(target, content, params);
            }
        };
        return(shani, targets, response, params) => {
            Utils.trigger(shani, DATA_EVENT, response);
            params.mode === 'discard' || targets.forEach(node => handleDataInsertion(node, response, params));
        };
    })();
    const Shanify = (() => {
        const getObject = (node, event) => {
            const shani = Utils.object({
                event,
                sync: true,
                emitter: node,
                poll: Utils.object(),
                actions: collectActions(node)
            });
            ['history', 'debug'].forEach(a => {
                shani[a] = Utils.resolveVariable(node, node.getAttribute('shani-' + a));
            });
            ['headers', 'cache', 'http'].forEach(a => {
                shani[a] = Parser.params(node, node.getAttribute('shani-' + a));
            });
            shani.headers = new Headers(shani.headers);
            return shani;
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
        const listen = e => {
            const node = getTargetNode(e.target.closest('[shani-on]'), e.type);
            if (node && !Utils.getNodeValue(node, 'disabled')) {
                if (['A', 'AREA', 'FORM'].includes(node.tagName)) {
                    e.preventDefault();
                }
                const shani = getObject(node, e);
                Utils.trigger(shani, e.type);
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
            for (let sel of Selectors) {
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
                } else if (key === 'shani-on') {
                    val = mergeEvents(val, values[key]);
                } else {
                    val = mergeParams(val, values[key], SEP_PARAM);
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
            if (root.tagName !== 'TEMPLATE') {
                setUserAttributes(root);
                addListener(root);
                root.querySelectorAll('[shani-on]').forEach(addListener);
            }
        };
    })();
    const Utils = (() => {
        /**
         * Timer for a delayed actions
         * @type Map
         */
        const TIMER = new Map();
        const MEMO = Object.create(null);
        const prepareCall = (shani, action, data, evt) => {
            const sure = isSyncEvent(shani, evt);
            !sure || clearTimeout(TIMER.get(shani.emitter));
            TIMER.delete(shani.emitter);
            callNext(shani, action, data, evt);
            doc.dispatchEvent(new CustomEvent('shani:on:' + evt, {detail: Utils.object({shani, data})}));
            !sure || TIMER.set(shani.emitter, recall(shani, data, shani.event.type));
        };
        const shouldSchedule = shani => {
            const underLimit = shani.poll.steps && (shani.poll.limit === null || (--shani.poll.limit) >= 0);
            return underLimit && shani.emitter.isConnected;
        };
        const recall = (shani, data, evt) => {
            const sp = shani.poll;
            if (shouldSchedule(shani)) {
                const action = shani.actions.get(evt);
                return setTimeout(prepareCall, sp.steps, shani, action, data, evt);
            } else if (sp.onend && sp.steps) {
                return setTimeout(() => Utils.trigger(shani, sp.onend, data), sp.steps);
            }
        };
        const getElement = (selector, emitter) => {
            if (selector) {
                if (selector === PARENT_SELECTOR) {
                    return [emitter.parentElement];
                }
                if (selector.startsWith(PARENT_SELECTOR)) {
                    return [Utils.getParentNode(emitter, selector.slice(PARENT_SELECTOR.length))];
                }
                return  Utils.getCachedNodes(selector);
            }
            return [emitter];
        };
        const isSyncEvent = (shani, evt) => evt === END_EVENT || (shani.sync && evt === shani.event.type);
        const callNext = (shani, action, data, evt) => {
            const cb = action ? Action.get(action.fn) : null;
            if (cb instanceof Function) {
                const evtName = action.ep.name || action.fn;
                if (evtName !== evt) {
                    const targets = getElement(action.selector, shani.emitter);
                    const p = Utils.object({
                        paramstr: action.paramstr, selector: action.selector, targets, data
                    });
                    shani.debug !== true || console.log(p);
                    cb.call(shani, p) === false || Utils.trigger(shani, evtName, data);
                } else {
                    console.warn('Operation stopped, event name ' + evtName + ' creates a loop with action name.');
                }
            }
        };
        const flipValue = val => typeof val === 'boolean' ? !val : '';
        const cast = val => val === 'true' || (val === 'false' ? false : val);
        return{
            date2ms(date) {
                if (typeof date === 'number') {
                    return date;
                }
                const value = Date.parse(date);
                return !isNaN(value) && /[-./]/.test(date) ? value : null;
            },
            traverse(obj, cb) {
                obj.targets.forEach(node => {
                    const p = Parser.params(node, obj.paramstr);
                    cb(p, node);
                });
            },
            walk(obj, cb) {
                Utils.traverse(obj, (params, node) => {
                    for (const key in params) {
                        cb(node, key, params[key]);
                    }
                });
            },
            getId: () => Math.random().toString(36).slice(2),
            code2text(code) {
                if (code > 199 && code < 300) {
                    return SUCCESS_EVENT;
                }
                if (code > 299 && code < 400) {
                    return REDIRECT_EVENT;
                }
                if (code > 399 && code < 500) {
                    return ERROR_EVENT;
                }
                return code < 200 ? INFO_EVENT : OFFLINE_EVENT;
            },
            connection: Object.create(null),
            TIME_UNITS: {
                s: 1, m: 60, h: 3600, d: 86400, w: 86400 * 7, q: 86400 * 91.25, y: 86400 * 365
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
                if (/^\s*-?\d+(\.\d+)?[smhdwqy]\s*$/.test(time)) {
                    time = time.trim();
                    const unit = time.slice(-1).toLowerCase();
                    const val = parseFloat(time.slice(0, -1));
                    return Math.round(Utils.TIME_UNITS[unit] * val * 1000);
                }
                throw new Error('Invalid duration ' + time);
            },
            trigger(shani, evt, data = {}) {
                const action = shani.actions.get(evt);
                data = Utils.object(data);
                if (action) {
                    const p = action.ep;
                    if (p.steps) {
                        shani.poll.steps = Utils.time2ms(p.steps);
                        shani.poll.limit = parseInt(p.limit) || null;
                        shani.poll.onend = p.onend || null;
                    }
                    if (p.delay) {
                        clearTimeout(TIMER.get(shani.emitter));
                        const id = setTimeout(() => {
                            !p.onstart || Utils.trigger(shani, p.onstart, data);
                            prepareCall(shani, action, data, evt);
                        }, Utils.time2ms(p.delay));
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
                    return str.charAt(0) === '\\' ? str.slice(1) : cast(str);
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
                const obj = Object.create(null);
                return !o ? obj : Object.assign(obj, o);
            },
            setNodeValue(node, key, val) {
                const v = val instanceof Element ? val.outerHTML : val;
                if (key in node) {
                    node[key] = v;
                } else if (v === false) {
                    node.removeAttribute(key);
                } else {
                    node.setAttribute(key, v === true ? key : v);
                }
            },
            calludf(name, args, thisArg) {
                const v = Action.get(name);
                return v instanceof Function ? v.apply(thisArg, args) : v;
            },
            getNodeValue(node, key) {
                if (typeof key === 'string') {
                    let val = key in node ? node[key] : node.hasAttribute(key) ? node.getAttribute(key) : Utils.calludf(key, [node]);
                    return key === val || cast(val);
                }
                return key;
            },
            nodeKeyExists: (node, key) => key in node || node.hasAttribute(key),
            splitEvents: node => Parser.events(node.getAttribute('shani-on')),
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
            },
            shuffle(rows) {
                // Fisher–Yates shuffle
                for (let i = rows.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [rows[i], rows[j]] = [rows[j], rows[i]];
                }
                return rows;
            },
            comparator(oprt) {
                const operators = {
                    eq: (n1, n2) => n1 === n2,
                    neq: (n1, n2) => n1 !== n2,
                    gt: (n1, n2) => n1 > n2,
                    gte: (n1, n2) => n1 >= n2,
                    lt: (n1, n2) => n1 < n2,
                    lte: (n1, n2) => n1 <= n2,
                    btw: (min, input, max) => min <= input && input <= max,
                    nbtw: (min, input, max) => min > input || max < input
                };
                if (!operators[oprt]) {
                    throw new Error('Invalid comparison operator: ' + oprt);
                }
                return operators[oprt];
            }
        };
    })();
    const Parser = (() => {
        const splitPair = (str, sep, def = null) => {
            const pos = str.indexOf(sep);
            return {
                k: pos > 0 ? str.slice(0, pos) : def,
                v: pos > 0 ? str.slice(pos + sep.length).trim() : def
            };
        };
        const isPlaceHolder = str => {//selector@prop
            return typeof str === 'string' && str.indexOf(SEP_VAR) > 0
                    && !str.includes(SEP_KEY_VAL) && str.charAt(0) !== '\\';
        };
        const getEventFromString = (str, idx) => {
            const name = str.slice(0, idx), idx2 = name.search(SEP_ACTION);
            return name.slice(0, idx2 > 0 ? idx2 : idx).trim();
        };
        return {
            params(node, str) {
                const obj = Utils.object();
                if (typeof str === 'string') {
                    const pairs = Parser.toArray(str, SEP_PARAM);
                    pairs.forEach(p => {
                        const pair = splitPair(p, SEP_KEY_VAL, p);
                        if (pair.k === p && pair.k.includes(SEP_VAR)) {//@prop, #id@prop
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
                return typeof str !== 'string' ? [] : str.split(sep).reduce((acc, s) => {
                    const a = s.trim();
                    a === '' || acc.push(a);
                    return acc;
                }, []);
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
        const createHttpPayload = (shani, params, method) => {
            const fd = Convertor.input2form(shani.emitter, params.inputf);
            const payload = Utils.object({
                url: shani.http.url, data: null, headers: shani.headers
            });
            if (fd) {
                if (method.toUpperCase() === 'GET') {
                    const mark = shani.http.url.includes('?') ? '&' : '?';
                    payload.url = shani.http.url + mark + Convertor.urlencoded(fd);
                } else {
                    const type = Utils.getSubtype(payload.headers.get('content-type'));
                    payload.data = Convertor.form2(fd, type);
                }
            }
            return payload;
        };
        const createWSocketPayload = (shani, params) => {
            const payload = Utils.object({url: shani.http.url, data: null, headers: shani.headers});
            const fd = Convertor.input2form(shani.emitter, params.inputf);
            if (fd) {
                const type = Utils.getSubtype(shani.headers.get('content-type'));
                payload.data = JSON.stringify({
                    headers: Convertor.map2json(shani.headers),
                    body: Convertor.form2(fd, type)
                });
            }
            return payload;
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
                shani.timeoutId = setTimeout(() => Utils.trigger(shani, TIMEOUT_EVENT), Utils.time2ms(timeout));
            }
            if (!('url' in shani.http)) {
                return sendWithoutUrl(shani, target, params);
            }
            if (shani.http.scheme === 'sse') {
                return sse(shani, target, params);
            }
            if ('scheme' in shani.http) {
                return wsocket(shani, target, params);
            }
            if (em.tagName === 'FORM') {
                em = em.querySelector('fieldset') || em;
            }
            http(shani, params, shani.http.method || method, request => {
                Utils.setNodeValue(em, 'disabled', true);
                Utils.trigger(shani, START_EVENT, {request});
            }, () => {
                onConnect(shani);
                Utils.setNodeValue(em, 'disabled', false);
                Utils.trigger(shani, END_EVENT);
            }, resp => onSuccessReq(shani, target, resp, params), err => {
                const status = err.name === 'AbortError' ? 408 : 400;
                if (!isNaN(shani.poll.limit)) {
                    shani.poll.limit++;
                }
                const resp = Utils.object({headers: new Headers(), status, body: ''});
                onSuccessReq(shani, target, resp, params);
            });
        };
        const onSuccessReq = (shani, targets, resp, params) => {
            const text = Utils.code2text(resp.status);
            Utils.trigger(shani, '' + resp.status, resp);
            Utils.trigger(shani, text, resp);
            HTMLResponse(shani, targets, resp, params);
            if (text === 'redirect') {
                const url = resp.headers.get('location');
                url === '#' ? location.reload() : location = url;
            }
        };
        const sendWithoutUrl = (shani, target, params) => {
            const resp = Utils.object({
                status: 200, text: 'OK', body: null, headers: new Headers()
            });
            onSuccessReq(shani, target, resp, params);
        };
        const closeConn = name => {
            const cn = Utils.connection[name];
            !cn || (cn instanceof AbortController ? cn.abort() : cn.close());
        };
        const http = (shani, params, method, onStart, onEnd, onSuccess, onError) => {
            const payload = createHttpPayload(shani, params, method), req = Utils.object();
            const p = shani.cache;
            if (p.age) {
                req.cacheAge = Utils.time2ms(p.age);
                req.cacheName = p.name || 'pubcache';
            }
            req.cname = shani.http.name || 'http';
            req.options = Utils.object({
                headers: payload.headers,
                body: payload.data,
                method: method,
                credentials: shani.http.credentials,
                mode: shani.http.mode
            });
            onStart(req);
            FetchClient.send(payload.url, req, onSuccess, onError, onEnd);
        };
        const sse = (shani, targets, params) => {
            const name = shani.http.name || 'sse';
            Utils.connection[name] = new EventSource(shani.http.url, {
                withCredentials: shani.http.credentials === 'include'
            });
            const on = (e, cb) => Utils.connection[name].addEventListener(e, cb);
            on('message', e => {
                const resp = Utils.object({
                    body: e.data || '', headers: new Headers({'content-type': 'text/html'})
                });
                HTMLResponse(shani, targets, resp, params);
            });
            on('open', e => {
                onConnect(shani);
                Utils.trigger(shani, START_EVENT);
            });
            on('error', e => {
                onConnect(shani);
                Utils.trigger(shani, ERROR_EVENT);
            });
            on('close', e => Utils.trigger(shani, END_EVENT));
        };
        const wsocket = (shani, targets, params) => {
            const host = shani.http.url.contains('://') ? '' : shani.http.scheme + '://' + location.host;
            const name = shani.http.name || 'ws';
            Utils.connection[name] = new WebSocket(host + shani.http.url);
            const on = (e, cb) => Utils.connection[name].addEventListener(e, cb);
            on('open', e => {
                onConnect(shani);
                const payload = createWSocketPayload(shani, params);
                Utils.trigger(shani, START_EVENT, {request: payload});
                Utils.connection[name].send(payload.data || '');
            });
            on('error', e => {
                onConnect(shani);
                Utils.trigger(shani, ERROR_EVENT);
            });
            on('message', e => {
                const resp = Utils.object({body: e.data || '', headers: new Headers()});
                HTMLResponse(shani, targets, resp, params);
            });
            on('close', e => Utils.trigger(shani, END_EVENT));
        };
        /** ==============HTTP=============*/
        Action.add('http.pull', function (obj) {
            if (this.history === true) {
                history.pushState(null, '', this.url);
            }
            sendReq(this, 'GET', obj);
        });
        Action.add('http.push', function (obj) {
            sendReq(this, 'POST', obj);
        });
        Action.add('http.abort', obj => {
            Utils.traverse(obj, p => {
                if (p.name) {
                    return closeConn(p.name);
                }
                for (const key in Utils.connection) {
                    closeConn(key);
                }
            });
        });
    })();
    const FetchClient = (() => {
        const cacheResponse = (cache, req, response, url) => {
            if (req.cacheAge) {
                const res = response.clone(), headers = new Headers(res.headers);
                headers.set('x-expires', Date.now() + req.cacheAge);
                const cached = new Response(res.body, {
                    statusText: res.statusText,
                    status: res.status,
                    headers
                });
                cache.put(url, cached);
            }
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
            if (!Utils.connection[req.cname] || Utils.connection[req.cname].signal.aborted) {
                Utils.connection[req.cname] = new AbortController();
            }
            req.options.signal = Utils.connection[req.cname].signal;
            return fetch(url, req.options).then(responseHandler);
        };
        const fetchAndCache = (cache, url, req, type, onSuccess, onError) => {
            fetchWithRetry(url, req, res => {
                cacheResponse(cache, req, res, url);
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
    const _CSS = (() => {
        Action.add('class.add', obj => {
            Utils.walk(obj, (node, key) => node.classList.add(key));
        });
        Action.add('class.rmv', obj => {
            Utils.walk(obj, (node, key) => node.classList.remove(key));
        });
        Action.add('class.replace', obj => {
            Utils.walk(obj, (node, key, val) => node.classList.replace(key, val));
        });
        Action.add('class.toggle', obj => {
            Utils.walk(obj, (node, key) => node.classList.toggle(key));
        });
        Action.add('class.exists', obj => {
            for (const node of obj.targets) {
                const p = Parser.params(node, obj.paramstr);
                for (const key in p) {
                    if (!node.classList.contains(key)) {
                        return false;
                    }
                }
            }
            return true;
        });
    })();
    const _Props = (() => {
        Action.add('prop.rmv', obj => Utils.walk(obj, Utils.removeNodeKey));
        Action.add('prop.exists', obj => {
            for (const node of obj.targets) {
                const p = Parser.params(node, obj.paramstr);
                for (const k in p) {
                    if (!Utils.nodeKeyExists(node, k)) {
                        return false;
                    }
                }
            }
            return true;
        });
        Action.add('prop.toggle', obj => {
            Utils.walk(obj, (node, key, val) => {
                const oldval = Utils.getNodeValue(node, key);
                const arr = Parser.toArray(val, SEP_LIST);
                for (const v of arr) {
                    if (v !== oldval) {
                        return Utils.setNodeValue(node, key, v);
                    }
                }
            });
        });
        Action.add('prop.bind', obj => {
            Utils.walk(obj, (node, key, val) => Parser.bindProperty(node, key, val));
        });
    })();
    const _Others = (() => {
        Action.add('util.call', obj => {
            Utils.traverse(obj, (p, node) => {
                const result = Utils.calludf(p.fn, [p, node]);
                result === undefined || Utils.setNodeValue(node, p.output, result);
            });
        });
        Action.add('util.trigger', function (obj) {
            const data = Utils.object({shani: this, data: obj.data});
            Utils.walk(obj, (node, key) => node.dispatchEvent(new CustomEvent(key, {detail: data, bubbles: true})));
        });
        Action.add('file.saveas', obj => {
            Utils.traverse(obj, p => {
                const a = doc.createElement('a');
                const type = p.type || obj.data.headers.get('content-type');
                a.href = URL.createObjectURL(new Blob([obj.data.body], {type}));
                a.download = p.name;
                a.click();
                URL.revokeObjectURL(a.href);
            });
        });
        Action.add('str.insert', obj => {
            Utils.traverse(obj, (p, node) => {
                let value = p.input, pos = parseInt(p.pos) - 1;
                if (pos < value.length && value.charAt(pos) !== p.char) {
                    value = value.slice(0, pos) + p.char + value.slice(pos);
                }
                Utils.setNodeValue(node, p.output, value);
            });
        });
        Action.add('str.concat', obj => {
            Utils.traverse(obj, (p, node) => {
                const values = p.props.split(p.separator || SEP_LIST).map(s => Utils.getNodeValue(node, s.trim()));
                Utils.setNodeValue(node, p.output, values.join(p.char || ''));
            });
        });
        Action.add('str.affix', obj => {
            Utils.traverse(obj, (p, node) => {
                const prefix = p.prefix || '', suffix = p.suffix || '';
                Utils.setNodeValue(node, p.output, prefix + p.input + suffix);
            });
        });
        Action.add('str.compare', obj => {
            for (const node of obj.targets) {
                const p = Parser.params(node, obj.paramstr);
                const evaluator = Utils.comparator(p.operator);
                const result = String(p.lvalue).localeCompare(String(p.rvalue));
                if (!evaluator(result, 0)) {
                    return false;
                }
            }
            return true;
        });
    })();
    const _Node = (() => {
        const moveNode = (target, parent, paramstr) => {
            const p = Parser.params(target, paramstr);
            const pos = parseInt(p.pos), kids = parent.children;
            const offset = pos > 0 ? pos - 1 : pos < 0 ? pos + 1 + kids.length : Math.floor(kids.length / 2);
            parent.insertBefore(target, kids[offset]);
        };
        const str2number = str => {
            const date = Utils.date2ms(str);
            if (date) {
                return date;
            }
            const value = str.replace(/[^\d.-]/g, ''), num = parseFloat(value);
            if (!isNaN(num) && value !== '') {
                return num;
            }
            return str.toLowerCase();
        };
        Action.add('node.rmv', obj => obj.targets.forEach(node => node.remove()));
        Action.add('node.empty', obj => {
            obj.targets.forEach(node => {
                while (node.lastChild) {
                    node.lastChild.remove();
                }
            });
        });
        Action.add('node.copy', function (obj) {
            Utils.traverse(obj, (p, node) => moveNode(this.emitter.cloneNode(true), node, obj.paramstr));
        });
        Action.add('node.move', function (obj) {
            Utils.traverse(obj, (p, node) => moveNode(this.emitter, node, obj.paramstr));
        });
        Action.add('node.replace', function (obj) {
            Utils.traverse(obj, (p, node) => node.replaceWith(this.emitter));
        });
        Action.add('node.swap', function (obj) {
            Utils.traverse(obj, (p, node) => {
                const placeholder = doc.createTextNode('');
                this.emitter.replaceWith(placeholder);
                node.replaceWith(this.emitter);
                placeholder.replaceWith(node);
            });
        });
        Action.add('node.walk', function (obj) {
            const me = this.emitter, p = Parser.params(me, obj.paramstr);
            const parent = me.parentNode;
            if (p.direction === 'prev') {
                const neighbor = me.previousElementSibling;
                neighbor ? parent.insertBefore(me, neighbor) : parent.appendChild(me);
            } else {
                const neighbor = me.nextElementSibling;
                const next = neighbor ? neighbor.nextElementSibling : parent.firstChild;
                parent.insertBefore(me, next);
            }
        });
        Action.add('node.shuffle', obj => {
            Utils.traverse(obj, (p, node) => {
                const rows = Utils.shuffle(Array.from(node.children));
                const df = doc.createDocumentFragment();
                rows.forEach(row => df.appendChild(row));
                node.appendChild(df);
            });
        });
        Action.add('node.sort', function (obj) {
            const rows = [];
            Utils.traverse(obj, (p, node) => {
                rows.push({
                    node: Utils.getParentNode(node, p.row || 'tr'), value: p.input.trim()
                });
            });
            const p = Parser.params(this.emitter, obj.paramstr), asc = p.order === 'asc';
            rows.sort((r1, r2) => {
                const v1 = str2number(r1.value), v2 = str2number(r2.value);
                if (typeof v1 === 'number' && typeof v2 === 'number') {
                    return asc ? v1 - v2 : v2 - v1;
                }
                return asc ? String(v1).localeCompare(String(v2)) : String(v2).localeCompare(String(v1));
            });
            const tbody = rows[0].node.parentElement;
            const df = doc.createDocumentFragment();
            rows.forEach(row => df.appendChild(row.node));
            tbody.appendChild(df);
        });
    })();
    const _Number = (() => {
        const compute = (lval, nv, sign) => {
            const rval = (typeof nv === 'string' && nv.endsWith('%') ? lval * 0.01 : 1) * parseFloat(nv);
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
                    throw new Error('Valid math operators are: +-*/%^');
            }
        };
        const parseNumber = (val, allowPercent) => {
            if (typeof val === 'number') {
                return val;
            }
            val ||=  '0';
            const num = val.replace(/[^\d%.-]/g, '');
            if (/^-?\d+(\.\d+)?%?$/.test(num)) {
                return allowPercent ? num : parseFloat(num);
            }
            throw new Error('Invalid number "' + val + '"');
        };
        const compare = (obj, cb, defval) => {
            for (const node of obj.targets) {
                const p = Parser.params(node, obj.paramstr);
                const evaluator = Utils.comparator(p.operator);
                const lval = cb(p.lvalue || defval), rval = cb(p.rvalue || defval);
                if (['btw', 'nbtw'].includes(p.operator)) {
                    if (!evaluator(lval, cb(p.input), rval)) {
                        return false;
                    }
                } else if (!evaluator(lval, rval)) {
                    return false;
                }
            }
            return true;
        };
        const timestamp2unit = (ts, unit) => Math.floor(ts / (Utils.TIME_UNITS[unit] * 1000));
        Action.add('number.calc', obj => {
            Utils.traverse(obj, (p, node) => {
                const lval = parseNumber(p.lvalue), rval = parseNumber(p.rvalue, true);
                const result = compute(lval, rval, p.operator) || 0;
                Utils.setNodeValue(node, p.output, result);
            });
        });
        Action.add('number.accumulate', function (obj) {
            const p = Parser.params(this.emitter, obj.paramstr);
            let result = parseNumber(p.initial) || 0;
            Utils.traverse(obj, param => {
                const value = parseNumber(param.input, true);
                result = compute(result, value, param.operator);
            });
            Utils.setNodeValue(this.emitter, p.output, result);
        });
        Action.add('number.format', obj => {
            Utils.traverse(obj, (p, node) => {
                const result = parseNumber(p.input).toLocaleString(undefined, {
                    maximumFractionDigits: p.maxdecimals || 2,
                    minimumFractionDigits: p.mindecimals || 0
                });
                Utils.setNodeValue(node, p.output, result);
            });
        });
        Action.add('number.compare', obj => compare(obj, parseNumber));
        Action.add('date.compare', obj => compare(obj, Utils.date2ms, Date.now()));
        Action.add('date.diff', obj => {
            const now = Date.now();
            Utils.traverse(obj, (p, node) => {
                const lvalue = Utils.date2ms(p.lvalue || now), rvalue = Utils.date2ms(p.rvalue || now);
                Utils.setNodeValue(node, p.output, timestamp2unit(lvalue - rvalue, p.unit || 'd'));
            });
        });
        Action.add('date.now', Date.now);
        Action.add('date.calc', obj => {
            const now = Date.now();
            Utils.traverse(obj, (p, node) => {
                const input = Utils.date2ms(p.input || now), interval = Utils.time2ms(p.interval);
                const result = compute(input, interval, p.operator) || 0;
                Utils.setNodeValue(node, p.output, new Date(result).toISOString());
            });
        });
    })();
    const _Random = (() => {
        const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
        const randDate = (min, max) => {
            const timestamp = min.getTime() + Math.random() * (max.getTime() - min.getTime());
            return new Date(timestamp);
        };
        Action.add('random.int', obj => {
            Utils.traverse(obj, (p, node) => {
                const result = randInt(Math.ceil(p.min), Math.floor(p.max));
                Utils.setNodeValue(node, p.output, result);
            });
        });
        Action.add('random.float', obj => {
            Utils.traverse(obj, (p, node) => {
                const min = parseFloat(p.min), max = parseFloat(p.max);
                const result = Math.random() * (max - min) + min;
                Utils.setNodeValue(node, p.output, result);
            });
        });
        Action.add('random.date', obj => {
            Utils.traverse(obj, (p, node) => {
                const min = new Date(Utils.date2ms(p.min)), max = new Date(Utils.date2ms(p.max));
                const date = randDate(min, max).toISOString();
                Utils.setNodeValue(node, p.output, date.slice(0, date.indexOf('T')));
            });
        });
        Action.add('random.time', obj => {
            Utils.traverse(obj, (p, node) => {
                const tmin = p.min.split(':'), tmax = p.max.split(':'), today = new Date();
                const min = new Date(today.setHours(parseInt(tmin[0]), parseInt(tmin[1]), parseInt(tmin[2]) || 0));
                const max = new Date(today.setHours(parseInt(tmax[0]), parseInt(tmax[1]), parseInt(tmax[2]) || 0));
                const time = randDate(min, max).toLocaleTimeString(undefined, {hour12: p.hour12});
                Utils.setNodeValue(node, p.output, time);
            });
        });
        Action.add('random.str', obj => {
            Utils.traverse(obj, (p, node) => {
                let str = '';
                const min = parseInt(p.min), max = parseInt(p.max), limit = randInt(min, max);
                for (let i = 0; i < limit; i++) {
                    const idx = randInt(0, p.values.length - 1);
                    str += p.values.charAt(idx);
                }
                Utils.setNodeValue(node, p.output, str);
            });
        });
        Action.add('random.value', obj => {
            Utils.traverse(obj, (p, node) => {
                const sep = p.separator || SEP_LIST, values = p.values.split(sep);
                const idx = randInt(0, values.length - 1);
                Utils.setNodeValue(node, p.output, values[idx].trim());
            });
        });
        Action.add('random.shuffle', obj => {
            Utils.traverse(obj, (p, node) => {
                const sep = p.separator || SEP_LIST, values = Utils.shuffle(p.values.split(sep));
                Utils.setNodeValue(node, p.output, values.join(sep));
            });
        });
    })();
    const _UI = (() => {
        const Carousel = (() => {
            const rotateItems = (params, node) => {
                const cls = params['active-class'];
                !params.speed || node.parentElement.style.setProperty('--speed', params.speed);
                const kids = node.parentElement.querySelector(params['children-wrapper']).children;
                const cb = callbacks[params.direction];
                for (let i in kids) {
                    if (kids[i].classList.contains(cls)) {
                        const nextIdx = cb(kids.length, i);
                        return selectNode(kids[i], kids[nextIdx], cls);
                    }
                }
            };
            const selectNode = (currNode, nextNode, cssClass) => {
                if (currNode !== nextNode) {
                    currNode.classList.remove(cssClass);
                    nextNode.classList.add(cssClass);
                }
            };
            const callbacks = {
                next: (total, idx) => (idx + 1) % total,
                prev: (total, idx) => (idx - 1 + total) % total
            };
            Action.add('ui.carousel', obj => Utils.traverse(obj, rotateItems));
            Action.add('ui.select', function (obj) {
                const node = this.event.detail.shani.emitter;
                const children = this.emitter.children;
                if (Array.from(children).includes(node)) {
                    const p = Parser.params(this.emitter, obj.paramstr), cls = p['active-class'];
                    for (let i in children) {
                        if (children[i].classList.contains(cls)) {
                            return selectNode(children[i], node, cls);
                        }
                    }
                }
            });
        })();
        const Modal = (() => {
            const COVER = 'modal-background';
            const getCloseBtn = classList => {
                if (classList) {
                    const btn = doc.createElement('button');
                    btn.type = 'button';
                    btn.innerHTML = '&times;';
                    btn.className = 'button button-times ' + classList;
                    Utils.setNodeValue(btn, 'shani-on', 'click' + SEP_EVT_ACTION + 'ui.close' + SEP_EVT_SELECTOR + '.' + COVER);
                    return btn;
                }
            };
            Action.add('ui.modal', obj => {
                Utils.traverse(obj, p => {
                    const mdbg = doc.createElement('div'), modal = doc.createElement('div');
                    const wrapper = doc.createElement('div');
                    wrapper.id = p.id;
                    wrapper.className = 'full-size';
                    if ('close-btn' in p) {
                        const btn = getCloseBtn(p['close-btn']);
                        btn.style.margin = 'var(--spacing)';
                        modal.appendChild(btn);
                    }
                    modal.className = p.classes;
                    modal.appendChild(wrapper);
                    mdbg.className = COVER;
                    mdbg.appendChild(modal);
                    doc.body.appendChild(mdbg);
                });
            });
        })();
        const getCover = (target, pageSize, fontSize) => {
            const id = Utils.getId(), style = doc.createElement('style');
            let s = '#' + id + '{width:100%;min-height:100%;padding:1rem;overflow-y:auto;';
            s += 'font-size:' + (fontSize || 100) + '%}body>:not(#' + id + '){display:none}';
            s += '@media print{#' + id + '{padding:12mm;print-color-adjust:exact;' + pageSize + '}}';
            s += '@page{margin:0;page-break-after:always;break-after:page}';
            style.type = 'text/css';
            style.textContent = s;
            const cover = doc.createElement('div');
            const df = doc.createDocumentFragment();
            df.appendChild(style);
            cover.id = id;
            for (const t of target) {
                df.appendChild(t.cloneNode(true));
            }
            cover.appendChild(df);
            doc.body.insertBefore(cover, doc.body.firstChild);
            return cover;
        };
        Action.add('ui.close', function (obj) {
            if (obj.selector) {
                const selector = Utils.resolveVariable(this.emitter, obj.selector);
                const parent = Utils.getParentNode(this.emitter, selector);
                parent ? parent.remove() : obj.targets.forEach(node => node.remove());
            }
        });
        Action.add('ui.print', function (obj) {
            if (window.print instanceof Function) {
                const p = Parser.params(this.emitter, obj.paramstr), title = doc.title;
                const cover = getCover(obj.targets, 'size:' + (p.size || 'auto'));
                doc.title = p.title || title;
                window.print();
                doc.title = title;
                cover.remove();
            }
        });
        Action.add('input.search', function (obj) {
            const text = this.emitter.value.trim().toLowerCase();
            obj.targets.forEach(node => {
                for (const row of node.children) {
                    row.style.display = row.textContent.toLowerCase().includes(text) ? null : 'none';
                }
            });
        });
        Action.add('ui.fscreen', obj => {
            if (doc.fullscreenEnabled) {
                const cover = getCover(obj.targets, '', 135);
                doc.documentElement.requestFullscreen().then(() => {
                    doc.addEventListener('fullscreenchange', () => {
                        doc.fullscreenElement || cover.remove();
                    });
                }).catch(() => cover.remove());
            }
        });
        Action.add('ui.copy', obj => {
            if (navigator.clipboard) {
                Utils.traverse(obj, p => navigator.clipboard.writeText(p.input.trim()).catch(e => null));
            }
        });
        Action.add('ui.loader', obj => {
            Utils.traverse(obj, (p, node) => {
                !p.color || node.style.setProperty('--loader-color', p.color);
                !p.size || node.style.setProperty('--loader-size', p.size);
                !p.thickness || node.style.setProperty('--loader-thickness', p.thickness);
                node.classList.add(p.name);
            });
        });
        Action.add('ui.loader.rmv', obj => {
            const props = ['--loader-color', '--loader-size', '--loader-thickness'];
            Utils.traverse(obj, (p, node) => {
                props.forEach(pr => node.style.removeProperty(pr));
                node.classList.remove(p.name);
            });
        });
    })();
})(document);