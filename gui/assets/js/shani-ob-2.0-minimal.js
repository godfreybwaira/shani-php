//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// It supports any action but contains none at all. Programmer has to define his own action and make them workable. //
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
(doc => {
    'use strict';
    const SEP_EVT_ACTION = '->', SEP_EVENT = ';', SEP_EVT_SELECTOR = '>>', SEP_ACTION = /\s/;
    const SEP_PARAM = '&', SEP_KEY_VAL = ':', SEP_VAR = '@', SEP_NEG = '!', SEP_LIST = ',';
    const Selectors = new Map(), START_EVENT = 'httpstart', END_EVENT = 'httpend', PARENT_SELECTOR = '&';

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
            TIME_UNITS: {
                s: 1, m: 60, h: 3600, d: 86400, w: 86400 * 7, q: 86400 * 91.25, y: 86400 * 365
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
            }
        };
    })();
})(document);