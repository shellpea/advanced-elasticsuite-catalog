!(function (e, t) {
    'object' == typeof exports && 'undefined' != typeof module
        ? (module.exports = t())
        : 'function' == typeof define && define.amd
        ? define(t)
        : ((e =
              'undefined' != typeof globalThis
                  ? globalThis
                  : e || self).Spruce = t())
})(this, function () {
    'undefined' != typeof globalThis
        ? globalThis
        : 'undefined' != typeof window
        ? window
        : 'undefined' != typeof global
        ? global
        : 'undefined' != typeof self && self
    var e = (function (e) {
        var t = { exports: {} }
        return e(t, t.exports), t.exports
    })(function (e, t) {
        e.exports = (function () {
            var e =
                /^v?(?:\d+)(\.(?:[x*]|\d+)(\.(?:[x*]|\d+)(\.(?:[x*]|\d+))?(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?)?)?$/i
            function t(e, t) {
                return -1 === e.indexOf(t) ? e.length : e.indexOf(t)
            }
            function r(e) {
                var r = e.replace(/^v/, '').replace(/\+.*$/, ''),
                    s = t(r, '-'),
                    i = r.substring(0, s).split('.')
                return i.push(r.substring(s + 1)), i
            }
            function s(e) {
                return isNaN(Number(e)) ? e : Number(e)
            }
            function i(t) {
                if ('string' != typeof t)
                    throw new TypeError('Invalid argument expected string')
                if (!e.test(t))
                    throw new Error(
                        "Invalid argument not valid semver ('" +
                            t +
                            "' received)",
                    )
            }
            function n(e, t) {
                ;[e, t].forEach(i)
                for (
                    var n = r(e), a = r(t), o = 0;
                    o < Math.max(n.length - 1, a.length - 1);
                    o++
                ) {
                    var h = parseInt(n[o] || 0, 10),
                        c = parseInt(a[o] || 0, 10)
                    if (h > c) return 1
                    if (c > h) return -1
                }
                var u = n[n.length - 1],
                    d = a[a.length - 1]
                if (u && d) {
                    var l = u.split('.').map(s),
                        p = d.split('.').map(s)
                    for (o = 0; o < Math.max(l.length, p.length); o++) {
                        if (
                            void 0 === l[o] ||
                            ('string' == typeof p[o] && 'number' == typeof l[o])
                        )
                            return -1
                        if (
                            void 0 === p[o] ||
                            ('string' == typeof l[o] && 'number' == typeof p[o])
                        )
                            return 1
                        if (l[o] > p[o]) return 1
                        if (p[o] > l[o]) return -1
                    }
                } else if (u || d) return u ? -1 : 1
                return 0
            }
            var a = ['>', '>=', '=', '<', '<='],
                o = {
                    '>': [1],
                    '>=': [0, 1],
                    '=': [0],
                    '<=': [-1, 0],
                    '<': [-1],
                }
            function h(e) {
                if ('string' != typeof e)
                    throw new TypeError(
                        'Invalid operator type, expected string but got ' +
                            typeof e,
                    )
                if (-1 === a.indexOf(e))
                    throw new TypeError(
                        'Invalid operator, expected one of ' + a.join('|'),
                    )
            }
            return (
                (n.validate = function (t) {
                    return 'string' == typeof t && e.test(t)
                }),
                (n.compare = function (e, t, r) {
                    h(r)
                    var s = n(e, t)
                    return o[r].indexOf(s) > -1
                }),
                n
            )
        })()
    })
    const t = (e) => null == e,
        r = (e) => Object.getPrototypeOf(e) === Object.prototype,
        s = (e) => Array.isArray(e),
        i = () =>
            !(
                !navigator.userAgent.includes('Node.js') &&
                !navigator.userAgent.includes('jsdom')
            ) ||
            (!!window.Alpine &&
                e.compare(window.Alpine.version, '2.7.0', '>=')),
        n = (e, i) => (
            Object.entries(e).forEach(([a, o]) => {
                t(o) || (!r(o) && !s(o)) || (e[a] = n(o, i))
            }),
            new Proxy(e, {
                get: (e, t, r) => i.get(e, t, r),
                set(e, a, o, h) {
                    t(o) || (!r(o) && !s(o)) || (o = n(o, i))
                    let c = e[a]
                    return (
                        (e[a] = o),
                        t(c) ||
                            t(c.__watchers) ||
                            (e[a].__watchers = c.__watchers),
                        i.set(e, a, e[a], h),
                        !0
                    )
                },
            })
        ),
        a = {
            stores: {},
            persistenceDriver: window.localStorage,
            persisted: [],
            persistedDrivers: {},
            subscribers: [],
            pendingWatchers: {},
            disableReactivity: !1,
            startingCallbacks: [],
            startedCallbacks: [],
            hasStarted: !1,
            start() {
                this.startingCallbacks.forEach((e) => e()),
                    this.attach(),
                    (this.stores = n(this.stores, {
                        get: (e, t, r) =>
                            Object.is(r, this.stores) &&
                            ['get', 'set', 'toggle', 'call', 'clear'].includes(
                                t,
                            )
                                ? this[t].bind(this)
                                : Reflect.get(e, t, r),
                        set: (e, t, r, s) => {
                            if (!this.disableReactivity) {
                                this.updateSubscribers(),
                                    this.runWatchers(e, t, r, s),
                                    (this.disableReactivity = !0)
                                try {
                                    this.persisted.forEach(
                                        this.updateLocalStorage.bind(this),
                                    )
                                } catch (e) {}
                                this.disableReactivity = !1
                            }
                        },
                    })),
                    (this.hasStarted = !0),
                    (this.disableReactivity = !0),
                    Object.entries(this.pendingWatchers).forEach(([e, t]) => {
                        t.forEach((t) => this.watch(e, t))
                    }),
                    (this.disableReactivity = !1),
                    this.startedCallbacks.forEach((e) => e())
            },
            starting(e) {
                this.startingCallbacks.push(e)
            },
            started(e) {
                this.startedCallbacks.push(e)
            },
            attach() {
                if (!i())
                    throw new Error(
                        '[Spruce] You must be using Alpine >= 2.5.0 to use Spruce.',
                    )
                const e = this
                window.Alpine.addMagicProperty(
                    'store',
                    (t) => (e.subscribe(t), e.stores),
                )
            },
            store(e, t, r = !1) {
                'function' == typeof t && (t = t())
                const s = this.isValidDriver(r)
                if (!0 === r || s)
                    try {
                        ;(this.stores[e] = this.retrieveFromLocalStorage(
                            e,
                            ((e) => {
                                let t = {}
                                return (
                                    Object.entries(e)
                                        .filter(
                                            ([e, t]) => 'function' == typeof t,
                                        )
                                        .forEach(([e, r]) => (t[e] = r)),
                                    t
                                )
                            })(t),
                            s ? r : void 0,
                        )),
                            s && (this.persistedDrivers[e] = r),
                            this.persisted.includes(e) || this.persisted.push(e)
                    } catch (e) {}
                return this.stores[e] || (this.stores[e] = t), this.stores[e]
            },
            reset(e, t) {
                void 0 !== this.stores[e] && (this.stores[e] = t)
            },
            delete(e, t = !0) {
                return (
                    void 0 !== this.stores[e] &&
                    (delete this.stores[e], t && this.updateSubscribers(), !0)
                )
            },
            deleteAll() {
                const e = Object.keys(this.stores).map((e) =>
                    this.delete(e, !1),
                )
                return this.updateSubscribers(), !e.some((e) => !e)
            },
            subscribe(e) {
                return (
                    this.subscribers.includes(e) || this.subscribers.push(e),
                    this.stores
                )
            },
            updateSubscribers() {
                this.subscribers
                    .filter((e) => !!e.__x)
                    .forEach((e) => {
                        e.__x.updateElements(e)
                    })
            },
            retrieveFromLocalStorage(e, t = {}, r) {
                let s = this.persistenceDriver
                void 0 !== r && (this.guardAgainstInvalidDrivers(r), (s = r))
                const i = s.getItem(`__spruce:${e}`)
                if (!i) return null
                let n = JSON.parse(i)
                return (
                    'object' == typeof n &&
                        ((n = Object.assign(t, n)),
                        delete n.__watchers,
                        delete n.__key_name),
                    n
                )
            },
            updateLocalStorage(e) {
                this.store(e)
                ;(this.persistedDrivers[e] || this.persistenceDriver).setItem(
                    `__spruce:${e}`,
                    JSON.stringify(this.store(e)),
                )
            },
            get(e, t = this.stores) {
                return e.split('.').reduce((e, t) => e[t], t)
            },
            set(e, t, r = this.stores) {
                return (
                    s(e) || (e = e.split('.')),
                    1 === e.length
                        ? (r[e[0]] = t)
                        : (r[e[0]] || (r[e[0]] = {}),
                          this.set(e.slice(1), t, r[e[0]]))
                )
            },
            toggle(e) {
                return this.set(e, !this.get(e))
            },
            call(e, ...t) {
                return this.get(e)(...t)
            },
            clear(e) {
                return this.persistenceDriver.removeItem(`__spruce:${e}`)
            },
            watch(e, i) {
                if (!this.hasStarted)
                    return (
                        this.pendingWatchers[e] ||
                            (this.pendingWatchers[e] = []),
                        this.pendingWatchers[e].push(i),
                        [() => this.unwatch(e, i)]
                    )
                const n = e.split('.'),
                    a = n.reduce((e, i) => {
                        const n = e[i]
                        return t(n) || (!r(n) && !s(n)) ? e : n
                    }, this.stores),
                    o = Object.is(a, this.get(e)) ? '__self' : n[n.length - 1]
                return (
                    a.hasOwnProperty('__watchers') ||
                        Object.defineProperty(a, '__watchers', {
                            enumerable: !1,
                            value: new Map(),
                            configurable: !0,
                        }),
                    a.__watchers.has(o) || a.__watchers.set(o, new Set()),
                    a.__watchers.get(o).add(i),
                    [() => this.unwatch(e, i)]
                )
            },
            unwatch(e, i) {
                const n = e.split('.'),
                    a = n.reduce((e, i) => {
                        const n = e[i]
                        return t(n) || (!r(n) && !s(n)) ? e : n
                    }, this.stores),
                    o = Object.is(a, this.get(e)) ? '__self' : n[n.length - 1],
                    h = a.__watchers
                h.has(o) && h.get(o).delete(i)
            },
            watchers(e) {
                const i = e.split('.'),
                    n = i.reduce((e, i) => {
                        const n = e[i]
                        return t(n) || (!r(n) && !s(n)) ? e : n
                    }, this.stores),
                    a = Object.is(n, this.get(e)) ? '__self' : i[i.length - 1]
                return n.__watchers ? n.__watchers.get(a) : {}
            },
            runWatchers(e, t, r) {
                e.__watchers &&
                    (e.__watchers.has(t) &&
                        e.__watchers.get(t).forEach((e) => e(r)),
                    e.__watchers.has('__self') &&
                        e.__watchers.get('__self').forEach((e) => e(r, t)))
            },
            persistUsing(e) {
                this.persisted.length > 0 &&
                    console.warn(
                        '[Spruce] You have already initialised a persisted store. Changing the driver may cause issues.',
                    ),
                    this.guardAgainstInvalidDrivers(e),
                    (this.persistenceDriver = e)
            },
            guardAgainstInvalidDrivers(e) {
                if ('function' != typeof e.getItem)
                    throw new Error(
                        '[Spruce] The persistence driver must have a `getItem(key)` method.',
                    )
                if ('function' != typeof e.setItem)
                    throw new Error(
                        '[Spruce] The persistence driver must have a `setItem(key, value)` method.',
                    )
                if ('function' != typeof e.removeItem)
                    throw new Error(
                        '[Spruce] The persistence driver must have a `removeItem(name)` method.',
                    )
            },
            isValidDriver(e) {
                try {
                    this.guardAgainstInvalidDrivers(e)
                } catch (e) {
                    return !1
                }
                return !0
            },
        }
    window.Spruce = a
    const o =
        window.deferLoadingAlpine ||
        function (e) {
            e()
        }
    return (
        (window.deferLoadingAlpine = function (e) {
            window.Spruce.start(), o(e)
        }),
        a
    )
})
