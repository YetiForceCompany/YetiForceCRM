(function (Chat) {
            'use strict';

            Chat = Chat && Chat.hasOwnProperty('default') ? Chat['default'] : Chat;

            var global$1 = (typeof global !== "undefined" ? global :
                        typeof self !== "undefined" ? self :
                        typeof window !== "undefined" ? window : {});

            // shim for using process in browser
            // based off https://github.com/defunctzombie/node-process/blob/master/browser.js

            function defaultSetTimout() {
                throw new Error('setTimeout has not been defined');
            }
            function defaultClearTimeout () {
                throw new Error('clearTimeout has not been defined');
            }
            var cachedSetTimeout = defaultSetTimout;
            var cachedClearTimeout = defaultClearTimeout;
            if (typeof global$1.setTimeout === 'function') {
                cachedSetTimeout = setTimeout;
            }
            if (typeof global$1.clearTimeout === 'function') {
                cachedClearTimeout = clearTimeout;
            }

            function runTimeout(fun) {
                if (cachedSetTimeout === setTimeout) {
                    //normal enviroments in sane situations
                    return setTimeout(fun, 0);
                }
                // if setTimeout wasn't available but was latter defined
                if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
                    cachedSetTimeout = setTimeout;
                    return setTimeout(fun, 0);
                }
                try {
                    // when when somebody has screwed with setTimeout but no I.E. maddness
                    return cachedSetTimeout(fun, 0);
                } catch(e){
                    try {
                        // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
                        return cachedSetTimeout.call(null, fun, 0);
                    } catch(e){
                        // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
                        return cachedSetTimeout.call(this, fun, 0);
                    }
                }


            }
            function runClearTimeout(marker) {
                if (cachedClearTimeout === clearTimeout) {
                    //normal enviroments in sane situations
                    return clearTimeout(marker);
                }
                // if clearTimeout wasn't available but was latter defined
                if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
                    cachedClearTimeout = clearTimeout;
                    return clearTimeout(marker);
                }
                try {
                    // when when somebody has screwed with setTimeout but no I.E. maddness
                    return cachedClearTimeout(marker);
                } catch (e){
                    try {
                        // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
                        return cachedClearTimeout.call(null, marker);
                    } catch (e){
                        // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
                        // Some versions of I.E. have different rules for clearTimeout vs setTimeout
                        return cachedClearTimeout.call(this, marker);
                    }
                }



            }
            var queue = [];
            var draining = false;
            var currentQueue;
            var queueIndex = -1;

            function cleanUpNextTick() {
                if (!draining || !currentQueue) {
                    return;
                }
                draining = false;
                if (currentQueue.length) {
                    queue = currentQueue.concat(queue);
                } else {
                    queueIndex = -1;
                }
                if (queue.length) {
                    drainQueue();
                }
            }

            function drainQueue() {
                if (draining) {
                    return;
                }
                var timeout = runTimeout(cleanUpNextTick);
                draining = true;

                var len = queue.length;
                while(len) {
                    currentQueue = queue;
                    queue = [];
                    while (++queueIndex < len) {
                        if (currentQueue) {
                            currentQueue[queueIndex].run();
                        }
                    }
                    queueIndex = -1;
                    len = queue.length;
                }
                currentQueue = null;
                draining = false;
                runClearTimeout(timeout);
            }
            function nextTick(fun) {
                var args = new Array(arguments.length - 1);
                if (arguments.length > 1) {
                    for (var i = 1; i < arguments.length; i++) {
                        args[i - 1] = arguments[i];
                    }
                }
                queue.push(new Item(fun, args));
                if (queue.length === 1 && !draining) {
                    runTimeout(drainQueue);
                }
            }
            // v8 likes predictible objects
            function Item(fun, array) {
                this.fun = fun;
                this.array = array;
            }
            Item.prototype.run = function () {
                this.fun.apply(null, this.array);
            };
            var title = 'browser';
            var platform = 'browser';
            var browser = true;
            var env = {};
            var argv = [];
            var version = ''; // empty string to avoid regexp issues
            var versions = {};
            var release = {};
            var config = {};

            function noop() {}

            var on = noop;
            var addListener = noop;
            var once = noop;
            var off = noop;
            var removeListener = noop;
            var removeAllListeners = noop;
            var emit = noop;

            function binding(name) {
                throw new Error('process.binding is not supported');
            }

            function cwd () { return '/' }
            function chdir (dir) {
                throw new Error('process.chdir is not supported');
            }function umask() { return 0; }

            // from https://github.com/kumavis/browser-process-hrtime/blob/master/index.js
            var performance = global$1.performance || {};
            var performanceNow =
              performance.now        ||
              performance.mozNow     ||
              performance.msNow      ||
              performance.oNow       ||
              performance.webkitNow  ||
              function(){ return (new Date()).getTime() };

            // generate timestamp or delta
            // see http://nodejs.org/api/process.html#process_process_hrtime
            function hrtime(previousTimestamp){
              var clocktime = performanceNow.call(performance)*1e-3;
              var seconds = Math.floor(clocktime);
              var nanoseconds = Math.floor((clocktime%1)*1e9);
              if (previousTimestamp) {
                seconds = seconds - previousTimestamp[0];
                nanoseconds = nanoseconds - previousTimestamp[1];
                if (nanoseconds<0) {
                  seconds--;
                  nanoseconds += 1e9;
                }
              }
              return [seconds,nanoseconds]
            }

            var startTime = new Date();
            function uptime() {
              var currentTime = new Date();
              var dif = currentTime - startTime;
              return dif / 1000;
            }

            var process = {
              nextTick: nextTick,
              title: title,
              browser: browser,
              env: env,
              argv: argv,
              version: version,
              versions: versions,
              on: on,
              addListener: addListener,
              once: once,
              off: off,
              removeListener: removeListener,
              removeAllListeners: removeAllListeners,
              emit: emit,
              binding: binding,
              cwd: cwd,
              chdir: chdir,
              umask: umask,
              hrtime: hrtime,
              platform: platform,
              release: release,
              config: config,
              uptime: uptime
            };

            /**
             * vuex v3.1.1
             * (c) 2019 Evan You
             * @license MIT
             */
            function applyMixin (Vue) {
              var version = Number(Vue.version.split('.')[0]);

              if (version >= 2) {
                Vue.mixin({ beforeCreate: vuexInit });
              } else {
                // override init and inject vuex init procedure
                // for 1.x backwards compatibility.
                var _init = Vue.prototype._init;
                Vue.prototype._init = function (options) {
                  if ( options === void 0 ) options = {};

                  options.init = options.init
                    ? [vuexInit].concat(options.init)
                    : vuexInit;
                  _init.call(this, options);
                };
              }

              /**
               * Vuex init hook, injected into each instances init hooks list.
               */

              function vuexInit () {
                var options = this.$options;
                // store injection
                if (options.store) {
                  this.$store = typeof options.store === 'function'
                    ? options.store()
                    : options.store;
                } else if (options.parent && options.parent.$store) {
                  this.$store = options.parent.$store;
                }
              }
            }

            var target = typeof window !== 'undefined'
              ? window
              : typeof global$1 !== 'undefined'
                ? global$1
                : {};
            var devtoolHook = target.__VUE_DEVTOOLS_GLOBAL_HOOK__;

            function devtoolPlugin (store) {
              if (!devtoolHook) { return }

              store._devtoolHook = devtoolHook;

              devtoolHook.emit('vuex:init', store);

              devtoolHook.on('vuex:travel-to-state', function (targetState) {
                store.replaceState(targetState);
              });

              store.subscribe(function (mutation, state) {
                devtoolHook.emit('vuex:mutation', mutation, state);
              });
            }

            /**
             * Get the first item that pass the test
             * by second argument function
             *
             * @param {Array} list
             * @param {Function} f
             * @return {*}
             */

            /**
             * forEach for object
             */
            function forEachValue (obj, fn) {
              Object.keys(obj).forEach(function (key) { return fn(obj[key], key); });
            }

            function isObject (obj) {
              return obj !== null && typeof obj === 'object'
            }

            function isPromise (val) {
              return val && typeof val.then === 'function'
            }

            function assert (condition, msg) {
              if (!condition) { throw new Error(("[vuex] " + msg)) }
            }

            function partial (fn, arg) {
              return function () {
                return fn(arg)
              }
            }

            // Base data struct for store's module, package with some attribute and method
            var Module = function Module (rawModule, runtime) {
              this.runtime = runtime;
              // Store some children item
              this._children = Object.create(null);
              // Store the origin module object which passed by programmer
              this._rawModule = rawModule;
              var rawState = rawModule.state;

              // Store the origin module's state
              this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
            };

            var prototypeAccessors = { namespaced: { configurable: true } };

            prototypeAccessors.namespaced.get = function () {
              return !!this._rawModule.namespaced
            };

            Module.prototype.addChild = function addChild (key, module) {
              this._children[key] = module;
            };

            Module.prototype.removeChild = function removeChild (key) {
              delete this._children[key];
            };

            Module.prototype.getChild = function getChild (key) {
              return this._children[key]
            };

            Module.prototype.update = function update (rawModule) {
              this._rawModule.namespaced = rawModule.namespaced;
              if (rawModule.actions) {
                this._rawModule.actions = rawModule.actions;
              }
              if (rawModule.mutations) {
                this._rawModule.mutations = rawModule.mutations;
              }
              if (rawModule.getters) {
                this._rawModule.getters = rawModule.getters;
              }
            };

            Module.prototype.forEachChild = function forEachChild (fn) {
              forEachValue(this._children, fn);
            };

            Module.prototype.forEachGetter = function forEachGetter (fn) {
              if (this._rawModule.getters) {
                forEachValue(this._rawModule.getters, fn);
              }
            };

            Module.prototype.forEachAction = function forEachAction (fn) {
              if (this._rawModule.actions) {
                forEachValue(this._rawModule.actions, fn);
              }
            };

            Module.prototype.forEachMutation = function forEachMutation (fn) {
              if (this._rawModule.mutations) {
                forEachValue(this._rawModule.mutations, fn);
              }
            };

            Object.defineProperties( Module.prototype, prototypeAccessors );

            var ModuleCollection = function ModuleCollection (rawRootModule) {
              // register root module (Vuex.Store options)
              this.register([], rawRootModule, false);
            };

            ModuleCollection.prototype.get = function get (path) {
              return path.reduce(function (module, key) {
                return module.getChild(key)
              }, this.root)
            };

            ModuleCollection.prototype.getNamespace = function getNamespace (path) {
              var module = this.root;
              return path.reduce(function (namespace, key) {
                module = module.getChild(key);
                return namespace + (module.namespaced ? key + '/' : '')
              }, '')
            };

            ModuleCollection.prototype.update = function update$1 (rawRootModule) {
              update([], this.root, rawRootModule);
            };

            ModuleCollection.prototype.register = function register (path, rawModule, runtime) {
                var this$1 = this;
                if ( runtime === void 0 ) runtime = true;

              {
                assertRawModule(path, rawModule);
              }

              var newModule = new Module(rawModule, runtime);
              if (path.length === 0) {
                this.root = newModule;
              } else {
                var parent = this.get(path.slice(0, -1));
                parent.addChild(path[path.length - 1], newModule);
              }

              // register nested modules
              if (rawModule.modules) {
                forEachValue(rawModule.modules, function (rawChildModule, key) {
                  this$1.register(path.concat(key), rawChildModule, runtime);
                });
              }
            };

            ModuleCollection.prototype.unregister = function unregister (path) {
              var parent = this.get(path.slice(0, -1));
              var key = path[path.length - 1];
              if (!parent.getChild(key).runtime) { return }

              parent.removeChild(key);
            };

            function update (path, targetModule, newModule) {
              {
                assertRawModule(path, newModule);
              }

              // update target module
              targetModule.update(newModule);

              // update nested modules
              if (newModule.modules) {
                for (var key in newModule.modules) {
                  if (!targetModule.getChild(key)) {
                    {
                      console.warn(
                        "[vuex] trying to add a new module '" + key + "' on hot reloading, " +
                        'manual reload is needed'
                      );
                    }
                    return
                  }
                  update(
                    path.concat(key),
                    targetModule.getChild(key),
                    newModule.modules[key]
                  );
                }
              }
            }

            var functionAssert = {
              assert: function (value) { return typeof value === 'function'; },
              expected: 'function'
            };

            var objectAssert = {
              assert: function (value) { return typeof value === 'function' ||
                (typeof value === 'object' && typeof value.handler === 'function'); },
              expected: 'function or object with "handler" function'
            };

            var assertTypes = {
              getters: functionAssert,
              mutations: functionAssert,
              actions: objectAssert
            };

            function assertRawModule (path, rawModule) {
              Object.keys(assertTypes).forEach(function (key) {
                if (!rawModule[key]) { return }

                var assertOptions = assertTypes[key];

                forEachValue(rawModule[key], function (value, type) {
                  assert(
                    assertOptions.assert(value),
                    makeAssertionMessage(path, key, type, value, assertOptions.expected)
                  );
                });
              });
            }

            function makeAssertionMessage (path, key, type, value, expected) {
              var buf = key + " should be " + expected + " but \"" + key + "." + type + "\"";
              if (path.length > 0) {
                buf += " in module \"" + (path.join('.')) + "\"";
              }
              buf += " is " + (JSON.stringify(value)) + ".";
              return buf
            }

            var Vue$1; // bind on install

            var Store = function Store (options) {
              var this$1 = this;
              if ( options === void 0 ) options = {};

              // Auto install if it is not done yet and `window` has `Vue`.
              // To allow users to avoid auto-installation in some cases,
              // this code should be placed here. See #731
              if (!Vue$1 && typeof window !== 'undefined' && window.Vue) {
                install(window.Vue);
              }

              {
                assert(Vue$1, "must call Vue.use(Vuex) before creating a store instance.");
                assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
                assert(this instanceof Store, "store must be called with the new operator.");
              }

              var plugins = options.plugins; if ( plugins === void 0 ) plugins = [];
              var strict = options.strict; if ( strict === void 0 ) strict = false;

              // store internal state
              this._committing = false;
              this._actions = Object.create(null);
              this._actionSubscribers = [];
              this._mutations = Object.create(null);
              this._wrappedGetters = Object.create(null);
              this._modules = new ModuleCollection(options);
              this._modulesNamespaceMap = Object.create(null);
              this._subscribers = [];
              this._watcherVM = new Vue$1();

              // bind commit and dispatch to self
              var store = this;
              var ref = this;
              var dispatch = ref.dispatch;
              var commit = ref.commit;
              this.dispatch = function boundDispatch (type, payload) {
                return dispatch.call(store, type, payload)
              };
              this.commit = function boundCommit (type, payload, options) {
                return commit.call(store, type, payload, options)
              };

              // strict mode
              this.strict = strict;

              var state = this._modules.root.state;

              // init root module.
              // this also recursively registers all sub-modules
              // and collects all module getters inside this._wrappedGetters
              installModule(this, state, [], this._modules.root);

              // initialize the store vm, which is responsible for the reactivity
              // (also registers _wrappedGetters as computed properties)
              resetStoreVM(this, state);

              // apply plugins
              plugins.forEach(function (plugin) { return plugin(this$1); });

              var useDevtools = options.devtools !== undefined ? options.devtools : Vue$1.config.devtools;
              if (useDevtools) {
                devtoolPlugin(this);
              }
            };

            var prototypeAccessors$1 = { state: { configurable: true } };

            prototypeAccessors$1.state.get = function () {
              return this._vm._data.$$state
            };

            prototypeAccessors$1.state.set = function (v) {
              {
                assert(false, "use store.replaceState() to explicit replace store state.");
              }
            };

            Store.prototype.commit = function commit (_type, _payload, _options) {
                var this$1 = this;

              // check object-style commit
              var ref = unifyObjectStyle(_type, _payload, _options);
                var type = ref.type;
                var payload = ref.payload;
                var options = ref.options;

              var mutation = { type: type, payload: payload };
              var entry = this._mutations[type];
              if (!entry) {
                {
                  console.error(("[vuex] unknown mutation type: " + type));
                }
                return
              }
              this._withCommit(function () {
                entry.forEach(function commitIterator (handler) {
                  handler(payload);
                });
              });
              this._subscribers.forEach(function (sub) { return sub(mutation, this$1.state); });

              if (
                
                options && options.silent
              ) {
                console.warn(
                  "[vuex] mutation type: " + type + ". Silent option has been removed. " +
                  'Use the filter functionality in the vue-devtools'
                );
              }
            };

            Store.prototype.dispatch = function dispatch (_type, _payload) {
                var this$1 = this;

              // check object-style dispatch
              var ref = unifyObjectStyle(_type, _payload);
                var type = ref.type;
                var payload = ref.payload;

              var action = { type: type, payload: payload };
              var entry = this._actions[type];
              if (!entry) {
                {
                  console.error(("[vuex] unknown action type: " + type));
                }
                return
              }

              try {
                this._actionSubscribers
                  .filter(function (sub) { return sub.before; })
                  .forEach(function (sub) { return sub.before(action, this$1.state); });
              } catch (e) {
                {
                  console.warn("[vuex] error in before action subscribers: ");
                  console.error(e);
                }
              }

              var result = entry.length > 1
                ? Promise.all(entry.map(function (handler) { return handler(payload); }))
                : entry[0](payload);

              return result.then(function (res) {
                try {
                  this$1._actionSubscribers
                    .filter(function (sub) { return sub.after; })
                    .forEach(function (sub) { return sub.after(action, this$1.state); });
                } catch (e) {
                  {
                    console.warn("[vuex] error in after action subscribers: ");
                    console.error(e);
                  }
                }
                return res
              })
            };

            Store.prototype.subscribe = function subscribe (fn) {
              return genericSubscribe(fn, this._subscribers)
            };

            Store.prototype.subscribeAction = function subscribeAction (fn) {
              var subs = typeof fn === 'function' ? { before: fn } : fn;
              return genericSubscribe(subs, this._actionSubscribers)
            };

            Store.prototype.watch = function watch (getter, cb, options) {
                var this$1 = this;

              {
                assert(typeof getter === 'function', "store.watch only accepts a function.");
              }
              return this._watcherVM.$watch(function () { return getter(this$1.state, this$1.getters); }, cb, options)
            };

            Store.prototype.replaceState = function replaceState (state) {
                var this$1 = this;

              this._withCommit(function () {
                this$1._vm._data.$$state = state;
              });
            };

            Store.prototype.registerModule = function registerModule (path, rawModule, options) {
                if ( options === void 0 ) options = {};

              if (typeof path === 'string') { path = [path]; }

              {
                assert(Array.isArray(path), "module path must be a string or an Array.");
                assert(path.length > 0, 'cannot register the root module by using registerModule.');
              }

              this._modules.register(path, rawModule);
              installModule(this, this.state, path, this._modules.get(path), options.preserveState);
              // reset store to update getters...
              resetStoreVM(this, this.state);
            };

            Store.prototype.unregisterModule = function unregisterModule (path) {
                var this$1 = this;

              if (typeof path === 'string') { path = [path]; }

              {
                assert(Array.isArray(path), "module path must be a string or an Array.");
              }

              this._modules.unregister(path);
              this._withCommit(function () {
                var parentState = getNestedState(this$1.state, path.slice(0, -1));
                Vue$1.delete(parentState, path[path.length - 1]);
              });
              resetStore(this);
            };

            Store.prototype.hotUpdate = function hotUpdate (newOptions) {
              this._modules.update(newOptions);
              resetStore(this, true);
            };

            Store.prototype._withCommit = function _withCommit (fn) {
              var committing = this._committing;
              this._committing = true;
              fn();
              this._committing = committing;
            };

            Object.defineProperties( Store.prototype, prototypeAccessors$1 );

            function genericSubscribe (fn, subs) {
              if (subs.indexOf(fn) < 0) {
                subs.push(fn);
              }
              return function () {
                var i = subs.indexOf(fn);
                if (i > -1) {
                  subs.splice(i, 1);
                }
              }
            }

            function resetStore (store, hot) {
              store._actions = Object.create(null);
              store._mutations = Object.create(null);
              store._wrappedGetters = Object.create(null);
              store._modulesNamespaceMap = Object.create(null);
              var state = store.state;
              // init all modules
              installModule(store, state, [], store._modules.root, true);
              // reset vm
              resetStoreVM(store, state, hot);
            }

            function resetStoreVM (store, state, hot) {
              var oldVm = store._vm;

              // bind store public getters
              store.getters = {};
              var wrappedGetters = store._wrappedGetters;
              var computed = {};
              forEachValue(wrappedGetters, function (fn, key) {
                // use computed to leverage its lazy-caching mechanism
                // direct inline function use will lead to closure preserving oldVm.
                // using partial to return function with only arguments preserved in closure enviroment.
                computed[key] = partial(fn, store);
                Object.defineProperty(store.getters, key, {
                  get: function () { return store._vm[key]; },
                  enumerable: true // for local getters
                });
              });

              // use a Vue instance to store the state tree
              // suppress warnings just in case the user has added
              // some funky global mixins
              var silent = Vue$1.config.silent;
              Vue$1.config.silent = true;
              store._vm = new Vue$1({
                data: {
                  $$state: state
                },
                computed: computed
              });
              Vue$1.config.silent = silent;

              // enable strict mode for new vm
              if (store.strict) {
                enableStrictMode(store);
              }

              if (oldVm) {
                if (hot) {
                  // dispatch changes in all subscribed watchers
                  // to force getter re-evaluation for hot reloading.
                  store._withCommit(function () {
                    oldVm._data.$$state = null;
                  });
                }
                Vue$1.nextTick(function () { return oldVm.$destroy(); });
              }
            }

            function installModule (store, rootState, path, module, hot) {
              var isRoot = !path.length;
              var namespace = store._modules.getNamespace(path);

              // register in namespace map
              if (module.namespaced) {
                store._modulesNamespaceMap[namespace] = module;
              }

              // set state
              if (!isRoot && !hot) {
                var parentState = getNestedState(rootState, path.slice(0, -1));
                var moduleName = path[path.length - 1];
                store._withCommit(function () {
                  Vue$1.set(parentState, moduleName, module.state);
                });
              }

              var local = module.context = makeLocalContext(store, namespace, path);

              module.forEachMutation(function (mutation, key) {
                var namespacedType = namespace + key;
                registerMutation(store, namespacedType, mutation, local);
              });

              module.forEachAction(function (action, key) {
                var type = action.root ? key : namespace + key;
                var handler = action.handler || action;
                registerAction(store, type, handler, local);
              });

              module.forEachGetter(function (getter, key) {
                var namespacedType = namespace + key;
                registerGetter(store, namespacedType, getter, local);
              });

              module.forEachChild(function (child, key) {
                installModule(store, rootState, path.concat(key), child, hot);
              });
            }

            /**
             * make localized dispatch, commit, getters and state
             * if there is no namespace, just use root ones
             */
            function makeLocalContext (store, namespace, path) {
              var noNamespace = namespace === '';

              var local = {
                dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
                  var args = unifyObjectStyle(_type, _payload, _options);
                  var payload = args.payload;
                  var options = args.options;
                  var type = args.type;

                  if (!options || !options.root) {
                    type = namespace + type;
                    if ( !store._actions[type]) {
                      console.error(("[vuex] unknown local action type: " + (args.type) + ", global type: " + type));
                      return
                    }
                  }

                  return store.dispatch(type, payload)
                },

                commit: noNamespace ? store.commit : function (_type, _payload, _options) {
                  var args = unifyObjectStyle(_type, _payload, _options);
                  var payload = args.payload;
                  var options = args.options;
                  var type = args.type;

                  if (!options || !options.root) {
                    type = namespace + type;
                    if ( !store._mutations[type]) {
                      console.error(("[vuex] unknown local mutation type: " + (args.type) + ", global type: " + type));
                      return
                    }
                  }

                  store.commit(type, payload, options);
                }
              };

              // getters and state object must be gotten lazily
              // because they will be changed by vm update
              Object.defineProperties(local, {
                getters: {
                  get: noNamespace
                    ? function () { return store.getters; }
                    : function () { return makeLocalGetters(store, namespace); }
                },
                state: {
                  get: function () { return getNestedState(store.state, path); }
                }
              });

              return local
            }

            function makeLocalGetters (store, namespace) {
              var gettersProxy = {};

              var splitPos = namespace.length;
              Object.keys(store.getters).forEach(function (type) {
                // skip if the target getter is not match this namespace
                if (type.slice(0, splitPos) !== namespace) { return }

                // extract local getter type
                var localType = type.slice(splitPos);

                // Add a port to the getters proxy.
                // Define as getter property because
                // we do not want to evaluate the getters in this time.
                Object.defineProperty(gettersProxy, localType, {
                  get: function () { return store.getters[type]; },
                  enumerable: true
                });
              });

              return gettersProxy
            }

            function registerMutation (store, type, handler, local) {
              var entry = store._mutations[type] || (store._mutations[type] = []);
              entry.push(function wrappedMutationHandler (payload) {
                handler.call(store, local.state, payload);
              });
            }

            function registerAction (store, type, handler, local) {
              var entry = store._actions[type] || (store._actions[type] = []);
              entry.push(function wrappedActionHandler (payload, cb) {
                var res = handler.call(store, {
                  dispatch: local.dispatch,
                  commit: local.commit,
                  getters: local.getters,
                  state: local.state,
                  rootGetters: store.getters,
                  rootState: store.state
                }, payload, cb);
                if (!isPromise(res)) {
                  res = Promise.resolve(res);
                }
                if (store._devtoolHook) {
                  return res.catch(function (err) {
                    store._devtoolHook.emit('vuex:error', err);
                    throw err
                  })
                } else {
                  return res
                }
              });
            }

            function registerGetter (store, type, rawGetter, local) {
              if (store._wrappedGetters[type]) {
                {
                  console.error(("[vuex] duplicate getter key: " + type));
                }
                return
              }
              store._wrappedGetters[type] = function wrappedGetter (store) {
                return rawGetter(
                  local.state, // local state
                  local.getters, // local getters
                  store.state, // root state
                  store.getters // root getters
                )
              };
            }

            function enableStrictMode (store) {
              store._vm.$watch(function () { return this._data.$$state }, function () {
                {
                  assert(store._committing, "do not mutate vuex store state outside mutation handlers.");
                }
              }, { deep: true, sync: true });
            }

            function getNestedState (state, path) {
              return path.length
                ? path.reduce(function (state, key) { return state[key]; }, state)
                : state
            }

            function unifyObjectStyle (type, payload, options) {
              if (isObject(type) && type.type) {
                options = payload;
                payload = type;
                type = type.type;
              }

              {
                assert(typeof type === 'string', ("expects string as the type, but found " + (typeof type) + "."));
              }

              return { type: type, payload: payload, options: options }
            }

            function install (_Vue) {
              if (Vue$1 && _Vue === Vue$1) {
                {
                  console.error(
                    '[vuex] already installed. Vue.use(Vuex) should be called only once.'
                  );
                }
                return
              }
              Vue$1 = _Vue;
              applyMixin(Vue$1);
            }

            /**
             * Reduce the code which written in Vue.js for getting the state.
             * @param {String} [namespace] - Module's namespace
             * @param {Object|Array} states # Object's item can be a function which accept state and getters for param, you can do something for state and getters in it.
             * @param {Object}
             */
            var mapState = normalizeNamespace(function (namespace, states) {
              var res = {};
              normalizeMap(states).forEach(function (ref) {
                var key = ref.key;
                var val = ref.val;

                res[key] = function mappedState () {
                  var state = this.$store.state;
                  var getters = this.$store.getters;
                  if (namespace) {
                    var module = getModuleByNamespace(this.$store, 'mapState', namespace);
                    if (!module) {
                      return
                    }
                    state = module.context.state;
                    getters = module.context.getters;
                  }
                  return typeof val === 'function'
                    ? val.call(this, state, getters)
                    : state[val]
                };
                // mark vuex getter for devtools
                res[key].vuex = true;
              });
              return res
            });

            /**
             * Reduce the code which written in Vue.js for committing the mutation
             * @param {String} [namespace] - Module's namespace
             * @param {Object|Array} mutations # Object's item can be a function which accept `commit` function as the first param, it can accept anthor params. You can commit mutation and do any other things in this function. specially, You need to pass anthor params from the mapped function.
             * @return {Object}
             */
            var mapMutations = normalizeNamespace(function (namespace, mutations) {
              var res = {};
              normalizeMap(mutations).forEach(function (ref) {
                var key = ref.key;
                var val = ref.val;

                res[key] = function mappedMutation () {
                  var args = [], len = arguments.length;
                  while ( len-- ) args[ len ] = arguments[ len ];

                  // Get the commit method from store
                  var commit = this.$store.commit;
                  if (namespace) {
                    var module = getModuleByNamespace(this.$store, 'mapMutations', namespace);
                    if (!module) {
                      return
                    }
                    commit = module.context.commit;
                  }
                  return typeof val === 'function'
                    ? val.apply(this, [commit].concat(args))
                    : commit.apply(this.$store, [val].concat(args))
                };
              });
              return res
            });

            /**
             * Reduce the code which written in Vue.js for getting the getters
             * @param {String} [namespace] - Module's namespace
             * @param {Object|Array} getters
             * @return {Object}
             */
            var mapGetters = normalizeNamespace(function (namespace, getters) {
              var res = {};
              normalizeMap(getters).forEach(function (ref) {
                var key = ref.key;
                var val = ref.val;

                // The namespace has been mutated by normalizeNamespace
                val = namespace + val;
                res[key] = function mappedGetter () {
                  if (namespace && !getModuleByNamespace(this.$store, 'mapGetters', namespace)) {
                    return
                  }
                  if ( !(val in this.$store.getters)) {
                    console.error(("[vuex] unknown getter: " + val));
                    return
                  }
                  return this.$store.getters[val]
                };
                // mark vuex getter for devtools
                res[key].vuex = true;
              });
              return res
            });

            /**
             * Reduce the code which written in Vue.js for dispatch the action
             * @param {String} [namespace] - Module's namespace
             * @param {Object|Array} actions # Object's item can be a function which accept `dispatch` function as the first param, it can accept anthor params. You can dispatch action and do any other things in this function. specially, You need to pass anthor params from the mapped function.
             * @return {Object}
             */
            var mapActions = normalizeNamespace(function (namespace, actions) {
              var res = {};
              normalizeMap(actions).forEach(function (ref) {
                var key = ref.key;
                var val = ref.val;

                res[key] = function mappedAction () {
                  var args = [], len = arguments.length;
                  while ( len-- ) args[ len ] = arguments[ len ];

                  // get dispatch function from store
                  var dispatch = this.$store.dispatch;
                  if (namespace) {
                    var module = getModuleByNamespace(this.$store, 'mapActions', namespace);
                    if (!module) {
                      return
                    }
                    dispatch = module.context.dispatch;
                  }
                  return typeof val === 'function'
                    ? val.apply(this, [dispatch].concat(args))
                    : dispatch.apply(this.$store, [val].concat(args))
                };
              });
              return res
            });

            /**
             * Rebinding namespace param for mapXXX function in special scoped, and return them by simple object
             * @param {String} namespace
             * @return {Object}
             */
            var createNamespacedHelpers = function (namespace) { return ({
              mapState: mapState.bind(null, namespace),
              mapGetters: mapGetters.bind(null, namespace),
              mapMutations: mapMutations.bind(null, namespace),
              mapActions: mapActions.bind(null, namespace)
            }); };

            /**
             * Normalize the map
             * normalizeMap([1, 2, 3]) => [ { key: 1, val: 1 }, { key: 2, val: 2 }, { key: 3, val: 3 } ]
             * normalizeMap({a: 1, b: 2, c: 3}) => [ { key: 'a', val: 1 }, { key: 'b', val: 2 }, { key: 'c', val: 3 } ]
             * @param {Array|Object} map
             * @return {Object}
             */
            function normalizeMap (map) {
              return Array.isArray(map)
                ? map.map(function (key) { return ({ key: key, val: key }); })
                : Object.keys(map).map(function (key) { return ({ key: key, val: map[key] }); })
            }

            /**
             * Return a function expect two param contains namespace and map. it will normalize the namespace and then the param's function will handle the new namespace and the map.
             * @param {Function} fn
             * @return {Function}
             */
            function normalizeNamespace (fn) {
              return function (namespace, map) {
                if (typeof namespace !== 'string') {
                  map = namespace;
                  namespace = '';
                } else if (namespace.charAt(namespace.length - 1) !== '/') {
                  namespace += '/';
                }
                return fn(namespace, map)
              }
            }

            /**
             * Search a special module from store by namespace. if module not exist, print error message.
             * @param {Object} store
             * @param {String} helper
             * @param {String} namespace
             * @return {Object}
             */
            function getModuleByNamespace (store, helper, namespace) {
              var module = store._modulesNamespaceMap[namespace];
              if ( !module) {
                console.error(("[vuex] module namespace not found in " + helper + "(): " + namespace));
              }
              return module
            }

            var index_esm = {
              Store: Store,
              install: install,
              version: '3.1.1',
              mapState: mapState,
              mapMutations: mapMutations,
              mapGetters: mapGetters,
              mapActions: mapActions,
              createNamespacedHelpers: createNamespacedHelpers
            };

            //
            const {
              mapGetters: mapGetters$1,
              mapActions: mapActions$1
            } = createNamespacedHelpers('Chat');
            var script = {
              name: 'Modal',
              components: {
                Chat
              },

              data() {
                return {
                  iconSize: '.75rem',
                  placeholder: 'Wyszukaj wiadomość',
                  visible: false,
                  groupFooter: 'Grupa',
                  roomFooter: 'Pokój',
                  left: true,
                  right: true,
                  tabHistory: 'ulubiony',
                  tabHistoryShow: false,
                  submitting: false,
                  moduleName: 'Chat',
                  dense: false
                };
              },

              computed: {
                dialog: {
                  get() {
                    return this.$store.getters['Chat/dialog'];
                  },

                  set(isOpen) {
                    this.setDialog(isOpen);
                  }

                },
                ...mapGetters$1(['maximizedDialog'])
              },
              methods: { ...mapActions$1(['setDialog'])
              }
            };

            function normalizeComponent(template, style, script, scopeId, isFunctionalTemplate, moduleIdentifier
            /* server only */
            , shadowMode, createInjector, createInjectorSSR, createInjectorShadow) {
              if (typeof shadowMode !== 'boolean') {
                createInjectorSSR = createInjector;
                createInjector = shadowMode;
                shadowMode = false;
              } // Vue.extend constructor export interop.


              var options = typeof script === 'function' ? script.options : script; // render functions

              if (template && template.render) {
                options.render = template.render;
                options.staticRenderFns = template.staticRenderFns;
                options._compiled = true; // functional template

                if (isFunctionalTemplate) {
                  options.functional = true;
                }
              } // scopedId


              if (scopeId) {
                options._scopeId = scopeId;
              }

              var hook;

              if (moduleIdentifier) {
                // server build
                hook = function hook(context) {
                  // 2.3 injection
                  context = context || // cached call
                  this.$vnode && this.$vnode.ssrContext || // stateful
                  this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext; // functional
                  // 2.2 with runInNewContext: true

                  if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
                    context = __VUE_SSR_CONTEXT__;
                  } // inject component styles


                  if (style) {
                    style.call(this, createInjectorSSR(context));
                  } // register component module identifier for async chunk inference


                  if (context && context._registeredComponents) {
                    context._registeredComponents.add(moduleIdentifier);
                  }
                }; // used by ssr in case component is cached and beforeCreate
                // never gets called


                options._ssrRegister = hook;
              } else if (style) {
                hook = shadowMode ? function () {
                  style.call(this, createInjectorShadow(this.$root.$options.shadowRoot));
                } : function (context) {
                  style.call(this, createInjector(context));
                };
              }

              if (hook) {
                if (options.functional) {
                  // register for functional component in vue file
                  var originalRender = options.render;

                  options.render = function renderWithStyleInjection(h, context) {
                    hook.call(context);
                    return originalRender(h, context);
                  };
                } else {
                  // inject component registration as beforeCreate hook
                  var existing = options.beforeCreate;
                  options.beforeCreate = existing ? [].concat(existing, hook) : [hook];
                }
              }

              return script;
            }

            var normalizeComponent_1 = normalizeComponent;

            var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\\b/.test(navigator.userAgent.toLowerCase());
            function createInjector(context) {
              return function (id, style) {
                return addStyle(id, style);
              };
            }
            var HEAD = document.head || document.getElementsByTagName('head')[0];
            var styles = {};

            function addStyle(id, css) {
              var group = isOldIE ? css.media || 'default' : id;
              var style = styles[group] || (styles[group] = {
                ids: new Set(),
                styles: []
              });

              if (!style.ids.has(id)) {
                style.ids.add(id);
                var code = css.source;

                if (css.map) {
                  // https://developer.chrome.com/devtools/docs/javascript-debugging
                  // this makes source maps inside style tags work properly in Chrome
                  code += '\n/*# sourceURL=' + css.map.sources[0] + ' */'; // http://stackoverflow.com/a/26603875

                  code += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(css.map)))) + ' */';
                }

                if (!style.element) {
                  style.element = document.createElement('style');
                  style.element.type = 'text/css';
                  if (css.media) style.element.setAttribute('media', css.media);
                  HEAD.appendChild(style.element);
                }

                if ('styleSheet' in style.element) {
                  style.styles.push(code);
                  style.element.styleSheet.cssText = style.styles.filter(Boolean).join('\n');
                } else {
                  var index = style.ids.size - 1;
                  var textNode = document.createTextNode(code);
                  var nodes = style.element.childNodes;
                  if (nodes[index]) style.element.removeChild(nodes[index]);
                  if (nodes.length) style.element.insertBefore(textNode, nodes[index]);else style.element.appendChild(textNode);
                }
              }
            }

            var browser$1 = createInjector;

            /* script */
            const __vue_script__ = script;

            /* template */
            var __vue_render__ = function() {
              var _vm = this;
              var _h = _vm.$createElement;
              var _c = _vm._self._c || _h;
              return _c(
                "div",
                { staticClass: "inline-block" },
                [
                  _c("q-btn", {
                    attrs: {
                      round: "",
                      size: _vm.iconSize,
                      flat: "",
                      icon: "mdi-forum-outline"
                    },
                    on: {
                      click: function($event) {
                        _vm.dialog = true;
                      }
                    }
                  }),
                  _vm._v(" "),
                  _c(
                    "q-dialog",
                    {
                      attrs: {
                        persistent: "",
                        maximized: _vm.maximizedDialog,
                        "transition-show": "slide-up",
                        "transition-hide": "slide-down"
                      },
                      model: {
                        value: _vm.dialog,
                        callback: function($$v) {
                          _vm.dialog = $$v;
                        },
                        expression: "dialog"
                      }
                    },
                    [_c("chat", { attrs: { container: "" } })],
                    1
                  )
                ],
                1
              )
            };
            var __vue_staticRenderFns__ = [];
            __vue_render__._withStripped = true;

              /* style */
              const __vue_inject_styles__ = function (inject) {
                if (!inject) return
                inject("data-v-307c9fe8_0", { source: "", map: undefined, media: undefined });
            Object.defineProperty(this, "$style", { value: {} });

              };
              /* scoped */
              const __vue_scope_id__ = undefined;
              /* module identifier */
              const __vue_module_identifier__ = undefined;
              /* functional template */
              const __vue_is_functional_template__ = false;
              /* style inject SSR */
              

              
              var ChatDialog = normalizeComponent_1(
                { render: __vue_render__, staticRenderFns: __vue_staticRenderFns__ },
                __vue_inject_styles__,
                __vue_script__,
                __vue_scope_id__,
                __vue_is_functional_template__,
                __vue_module_identifier__,
                browser$1,
                undefined
              );

            Vue.use(index_esm);
            const debug = process.env.NODE_ENV !== 'production';
            window.vuexStore = new index_esm.Store({
              strict: debug
            });
            var store = window.vuexStore;

            /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
            var getters = {
              dialog(state) {
                return state.dialog;
              },

              maximizedDialog(state) {
                return state.maximizedDialog;
              },

              leftPanel(state) {
                return state.leftPanel;
              },

              rightPanel(state) {
                return state.rightPanel;
              }

            };

            /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
            var mutations = {
              dialog(state, idOpen) {
                state.dialog = idOpen;
              },

              maximizedDialog(state, isMax) {
                state.maximizedDialog = isMax;
              },

              leftPanel(state, isOpen) {
                state.leftPanel = isOpen;
              },

              rightPanel(state, isOpen) {
                state.rightPanel = isOpen;
              }

            };

            /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
            var actions = {
              setDialog({
                commit
              }, isOpen) {
                commit('dialog', isOpen);
              },

              maximizedDialog({
                commit
              }, isMax) {
                commit('maximizedDialog', isMax);
              },

              toggleLeftPanel({
                commit,
                rootGetters
              }) {
                commit('leftPanel', !rootGetters['leftPanel']);
              },

              toggleRightPanel({
                commit,
                rootGetters
              }) {
                commit('rightPanel', !rootGetters['rightPanel']);
              }

            };

            /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
            var state = {
              dialog: true,
              maximizedDialog: false,
              leftPanel: true,
              rightPanel: true
            };

            /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
            var moduleStore = {
              namespaced: true,
              getters,
              actions,
              mutations,
              state
            };

            store.registerModule('Chat', moduleStore);
            window.ChatVueComponent = {
              component: ChatDialog,

              mount(config) {
                ChatDialog.state = config.state;
                return new Vue({
                  store,
                  render: h => h(ChatDialog)
                }).$mount(config.el);
              }

            };

}(Chat));
//# sourceMappingURL=Chat.vue.js.map
