(function () {
  'use strict';

  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  var script = {
    name: 'Icon',
    props: {
      icon: {
        type: String,
        required: true
      },
      size: {
        type: String,
        required: false
      }
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

  var browser = createInjector;

  /* script */
  const __vue_script__ = script;

  /* template */
  var __vue_render__ = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "div",
      [
        /^mdi|^fa/.test(_vm.icon)
          ? _c("q-icon", { attrs: { name: _vm.icon, size: _vm.size, dense: "" } })
          : _vm.icon.includes("/")
          ? _c(
              "q-avatar",
              { attrs: { size: _vm.size } },
              [_c("q-img", { attrs: { src: _vm.icon } })],
              1
            )
          : _c("q-icon", {
              class: [_vm.icon, "q-icon"],
              style: { "font-size": _vm.size }
            })
      ],
      1
    )
  };
  var __vue_staticRenderFns__ = [];
  __vue_render__._withStripped = true;

    /* style */
    const __vue_inject_styles__ = function (inject) {
      if (!inject) return
      inject("data-v-8becc55e_0", { source: "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__ = "data-v-8becc55e";
    /* module identifier */
    const __vue_module_identifier__ = undefined;
    /* functional template */
    const __vue_is_functional_template__ = false;
    /* style inject SSR */
    

    
    var Icon = normalizeComponent_1(
      { render: __vue_render__, staticRenderFns: __vue_staticRenderFns__ },
      __vue_inject_styles__,
      __vue_script__,
      __vue_scope_id__,
      __vue_is_functional_template__,
      __vue_module_identifier__,
      browser,
      undefined
    );

  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  var script$1 = {
    name: 'IconInfo',
    props: {
      customOptions: {
        type: Object,
        default: () => {
          return {};
        }
      }
    },

    data() {
      return {
        searchInfoShow: false,
        options: {
          iconSize: 'inherit',
          tooltipFont: '14px',
          backgroundClass: 'bg-primary'
        },
        tooltipId: `tooltip-id-${Quasar.utils.uid()}`
      };
    },

    created() {
      this.options = Object.assign(this.options, this.customOptions);
      document.addEventListener('click', e => {
        if (this.searchInfoShow && !e.target.offsetParent.classList.contains(this.tooltipId) && !e.target.classList.contains(this.tooltipId)) {
          this.searchInfoShow = false;
        }
      });
    }

  };

  /* script */
  const __vue_script__$1 = script$1;

  /* template */
  var __vue_render__$1 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "div",
      { staticClass: "flex" },
      [
        _c("q-icon", {
          class: ["cursor-pointer", _vm.tooltipId],
          style: "font-size: " + _vm.options.iconSize + ";",
          attrs: {
            name: _vm.searchInfoShow
              ? "mdi-information"
              : "mdi-information-outline"
          },
          on: {
            click: function($event) {
              _vm.searchInfoShow = !_vm.searchInfoShow;
            }
          }
        }),
        _vm._v(" "),
        _c(
          "div",
          [
            _c(
              "q-tooltip",
              {
                attrs: {
                  "content-style": "font-size: " + _vm.options.tooltipFont,
                  "content-class": [
                    _vm.options.backgroundClass,
                    _vm.tooltipId,
                    "all-pointer-events"
                  ]
                },
                model: {
                  value: _vm.searchInfoShow,
                  callback: function($$v) {
                    _vm.searchInfoShow = $$v;
                  },
                  expression: "searchInfoShow"
                }
              },
              [_vm._t("default")],
              2
            )
          ],
          1
        )
      ],
      1
    )
  };
  var __vue_staticRenderFns__$1 = [];
  __vue_render__$1._withStripped = true;

    /* style */
    const __vue_inject_styles__$1 = function (inject) {
      if (!inject) return
      inject("data-v-4ca201da_0", { source: "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$1 = "data-v-4ca201da";
    /* module identifier */
    const __vue_module_identifier__$1 = undefined;
    /* functional template */
    const __vue_is_functional_template__$1 = false;
    /* style inject SSR */
    

    
    var IconInfo = normalizeComponent_1(
      { render: __vue_render__$1, staticRenderFns: __vue_staticRenderFns__$1 },
      __vue_inject_styles__$1,
      __vue_script__$1,
      __vue_scope_id__$1,
      __vue_is_functional_template__$1,
      __vue_module_identifier__$1,
      browser,
      undefined
    );

  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  var script$2 = {
    name: 'ColumnsGrid',
    props: {
      columnBlocks: {
        type: Array,
        required: true
      }
    }
  };

  /* script */
  const __vue_script__$2 = script$2;

  /* template */
  var __vue_render__$2 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "div",
      {
        staticClass: "columns-grid__container",
        style: {
          "-webkit-column-count": _vm.columnBlocks.length,
          "-moz-column-count": _vm.columnBlocks.length,
          "column-count": _vm.columnBlocks.length
        }
      },
      [
        _vm._l(_vm.columnBlocks, function(relatedBlock) {
          return [
            _c(
              "div",
              { key: relatedBlock, staticClass: "columns-grid__block" },
              [_vm._t("default", null, { relatedBlock: relatedBlock })],
              2
            )
          ]
        })
      ],
      2
    )
  };
  var __vue_staticRenderFns__$2 = [];
  __vue_render__$2._withStripped = true;

    /* style */
    const __vue_inject_styles__$2 = function (inject) {
      if (!inject) return
      inject("data-v-0036c5dd_0", { source: "\n.columns-grid__container[data-v-0036c5dd] {\r\n  -webkit-column-width: 27rem;\r\n  -moz-column-width: 27rem;\r\n  column-width: 27rem;\n}\n.columns-grid__block[data-v-0036c5dd] {\r\n  width: 100%;\r\n  padding-bottom: 16px;\r\n  -webkit-column-break-inside: avoid;\r\n  page-break-inside: avoid;\r\n  break-inside: avoid-column;\n}\r\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$2 = "data-v-0036c5dd";
    /* module identifier */
    const __vue_module_identifier__$2 = undefined;
    /* functional template */
    const __vue_is_functional_template__$2 = false;
    /* style inject SSR */
    

    
    var ColumnsGrid = normalizeComponent_1(
      { render: __vue_render__$2, staticRenderFns: __vue_staticRenderFns__$2 },
      __vue_inject_styles__$2,
      __vue_script__$2,
      __vue_scope_id__$2,
      __vue_is_functional_template__$2,
      __vue_module_identifier__$2,
      browser,
      undefined
    );

  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //
  var script$3 = {
    name: 'Carousel',

    data() {
      return {
        slide: 0,
        height: '90vh',
        report: 0,
        fullscreen: false
      };
    },

    props: {
      record: {
        type: Object,
        required: true
      }
    },
    watch: {
      fullscreen: function (val) {
        if (val) {
          this.$q.fullscreen.request();
        } else {
          this.$q.fullscreen.exit();
        }
      },

      '$q.fullscreen.isActive'(val) {
        this.fullscreen = val;
      }

    },
    methods: {
      onTransition(size) {
        const scrollbarWidth = 17;
        $(this.$refs.carousel.$el).find('img').css('max-width', $(this.$refs.carousel.$el).width() - scrollbarWidth);
      }

    }
  };

  /* script */
  const __vue_script__$3 = script$3;

  /* template */
  var __vue_render__$3 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "div",
      [
        _c(
          "q-carousel",
          {
            ref: "carousel",
            class: [
              "quasar-reset shadow-1 rounded-borders",
              !_vm.fullscreen ? "carousel-height" : ""
            ],
            attrs: {
              "transition-prev": "scale",
              "transition-next": "scale",
              swipeable: "",
              animated: "",
              "control-color": "black",
              navigation: "",
              padding: "",
              arrows: "",
              fullscreen: _vm.fullscreen
            },
            on: {
              "update:fullscreen": function($event) {
                _vm.fullscreen = $event;
              },
              transition: _vm.onTransition
            },
            scopedSlots: _vm._u([
              {
                key: "control",
                fn: function() {
                  return [
                    _c(
                      "q-carousel-control",
                      { attrs: { position: "bottom-right", offset: [18, 18] } },
                      [
                        _c("q-btn", {
                          attrs: {
                            push: "",
                            round: "",
                            dense: "",
                            color: "white",
                            "text-color": "primary",
                            icon: _vm.fullscreen
                              ? "mdi-fullscreen-exit"
                              : "mdi-fullscreen"
                          },
                          on: {
                            click: function($event) {
                              _vm.fullscreen = !_vm.fullscreen;
                            }
                          }
                        })
                      ],
                      1
                    )
                  ]
                },
                proxy: true
              }
            ]),
            model: {
              value: _vm.slide,
              callback: function($$v) {
                _vm.slide = $$v;
              },
              expression: "slide"
            }
          },
          _vm._l(_vm.record.content, function(slide, index) {
            return _c(
              "q-carousel-slide",
              {
                key: index,
                staticClass: "column no-wrap flex-center",
                attrs: { name: index, fullscreen: _vm.fullscreen },
                on: {
                  "update:fullscreen": function($event) {
                    _vm.fullscreen = $event;
                  }
                }
              },
              [
                _c("div", {
                  staticClass: "full-height",
                  domProps: { innerHTML: _vm._s(slide) }
                })
              ]
            )
          }),
          1
        )
      ],
      1
    )
  };
  var __vue_staticRenderFns__$3 = [];
  __vue_render__$3._withStripped = true;

    /* style */
    const __vue_inject_styles__$3 = function (inject) {
      if (!inject) return
      inject("data-v-68991888_0", { source: "\n.carousel-height[data-v-68991888] {\r\n  height: max-content;\r\n  min-height: calc(100vh - 31.14px);\n}\r\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$3 = "data-v-68991888";
    /* module identifier */
    const __vue_module_identifier__$3 = undefined;
    /* functional template */
    const __vue_is_functional_template__$3 = false;
    /* style inject SSR */
    

    
    var Carousel = normalizeComponent_1(
      { render: __vue_render__$3, staticRenderFns: __vue_staticRenderFns__$3 },
      __vue_inject_styles__$3,
      __vue_script__$3,
      __vue_scope_id__$3,
      __vue_is_functional_template__$3,
      __vue_module_identifier__$3,
      browser,
      undefined
    );

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
  var browser$1 = true;
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
    browser: browser$1,
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
  } = createNamespacedHelpers('KnowledgeBase');
  var script$4 = {
    name: 'ArticlesList',
    components: {
      Icon
    },
    props: {
      data: {
        type: Array,
        default: []
      },
      title: {
        type: String,
        default: ''
      }
    },

    data() {
      return {
        pagination: {
          rowsPerPage: 0
        },
        columns: [{
          name: 'desc',
          required: true,
          label: 'Title',
          align: 'left',
          field: row => row.subject,
          format: val => `${val}`,
          sortable: true
        }, {
          name: 'short_time',
          align: 'center',
          label: 'Short time',
          field: 'short_time',
          sortable: true
        }, {
          name: 'introduction',
          align: 'center',
          label: 'Introduction',
          field: 'introduction',
          sortable: true
        }]
      };
    },

    computed: { ...mapGetters$1(['tree', 'iconSize', 'moduleName']),

      hasData() {
        return this.data.length;
      }

    },
    methods: { ...mapActions$1(['fetchRecord']),

      onClickRecord(id) {
        this.fetchRecord(id).then(() => {
          this.$emit('onClickRecord', id);
        });
      }

    }
  };

  /* script */
  const __vue_script__$4 = script$4;

  /* template */
  var __vue_render__$4 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "div",
      { staticClass: "KnowledgeBase__ArticlesList" },
      [
        _c("q-table", {
          attrs: {
            data: _vm.data,
            columns: _vm.columns,
            "row-key": "subject",
            grid: "",
            "hide-header": "",
            pagination: _vm.pagination,
            title: _vm.title
          },
          on: {
            "update:pagination": function($event) {
              _vm.pagination = $event;
            }
          },
          scopedSlots: _vm._u([
            {
              key: "item",
              fn: function(props) {
                return [
                  _c(
                    "q-list",
                    {
                      staticClass: "full-width",
                      attrs: { padding: "" },
                      on: {
                        click: function($event) {
                          $event.preventDefault();
                          return _vm.onClickRecord(props.row.id)
                        }
                      }
                    },
                    [
                      _c(
                        "q-item",
                        { attrs: { clickable: "" } },
                        [
                          _c(
                            "q-item-section",
                            { attrs: { avatar: "" } },
                            [_c("q-icon", { attrs: { name: "mdi-text" } })],
                            1
                          ),
                          _vm._v(" "),
                          _c(
                            "q-item-section",
                            [
                              _c(
                                "q-item-label",
                                { staticClass: "text-primary" },
                                [
                                  _c(
                                    "a",
                                    {
                                      staticClass: "js-popover-tooltip--record",
                                      attrs: {
                                        href:
                                          "index.php?module=" +
                                          _vm.moduleName +
                                          "&view=Detail&record=" +
                                          props.row.id
                                      }
                                    },
                                    [
                                      _vm._v(
                                        "\n                " +
                                          _vm._s(props.row.subject) +
                                          "\n              "
                                      )
                                    ]
                                  )
                                ]
                              ),
                              _vm._v(" "),
                              _c(
                                "q-item-label",
                                {
                                  staticClass: "flex items-center",
                                  attrs: { overline: "" }
                                },
                                [
                                  _c(
                                    "q-breadcrumbs",
                                    {
                                      staticClass: "mr-2 text-grey-8",
                                      attrs: { "active-color": "grey-8" }
                                    },
                                    [
                                      _vm._l(
                                        _vm.tree.categories[props.row.category]
                                          .parentTree,
                                        function(category) {
                                          return _c(
                                            "q-breadcrumbs-el",
                                            {
                                              key:
                                                _vm.tree.categories[category]
                                                  .label
                                            },
                                            [
                                              _vm.tree.categories[category].icon
                                                ? _c("icon", {
                                                    staticClass: "q-mr-sm",
                                                    attrs: {
                                                      size: _vm.iconSize,
                                                      icon:
                                                        _vm.tree.categories[
                                                          category
                                                        ].icon
                                                    }
                                                  })
                                                : _vm._e(),
                                              _vm._v(
                                                "\n                  " +
                                                  _vm._s(
                                                    _vm.tree.categories[category]
                                                      .label
                                                  ) +
                                                  "\n                "
                                              )
                                            ],
                                            1
                                          )
                                        }
                                      ),
                                      _vm._v(" "),
                                      _c("q-tooltip", [
                                        _vm._v(
                                          "\n                  " +
                                            _vm._s(
                                              _vm.translate("JS_KB_CATEGORY")
                                            ) +
                                            "\n                "
                                        )
                                      ])
                                    ],
                                    2
                                  ),
                                  _vm._v(
                                    "\n              | " +
                                      _vm._s(_vm.translate("JS_KB_AUTHORED_BY")) +
                                      ":\n              "
                                  ),
                                  _c("span", {
                                    staticClass: "q-ml-sm",
                                    domProps: {
                                      innerHTML: _vm._s(
                                        props.row.assigned_user_id
                                      )
                                    }
                                  })
                                ],
                                1
                              ),
                              _vm._v(" "),
                              _c("q-item-label", { attrs: { caption: "" } }, [
                                _vm._v(_vm._s(props.row.introduction))
                              ])
                            ],
                            1
                          ),
                          _vm._v(" "),
                          _c(
                            "q-item-section",
                            { attrs: { side: "", top: "" } },
                            [
                              _c("q-item-label", { attrs: { caption: "" } }, [
                                _vm._v(_vm._s(props.row.short_time))
                              ]),
                              _vm._v(" "),
                              _c("q-tooltip", [
                                _vm._v(
                                  "\n              " +
                                    _vm._s(props.row.full_time) +
                                    "\n            "
                                )
                              ])
                            ],
                            1
                          )
                        ],
                        1
                      )
                    ],
                    1
                  )
                ]
              }
            },
            {
              key: "bottom",
              fn: function(props) {
                return undefined
              }
            }
          ])
        }),
        _vm._v(" "),
        _c(
          "div",
          {
            class: [
              "flex items-center q-px-lg q-py-sm",
              _vm.hasData ? "hidden" : ""
            ]
          },
          [
            _c("q-icon", {
              staticClass: "q-mr-sm",
              attrs: { name: "mdi-alert-outline" }
            }),
            _vm._v(
              "\n    " + _vm._s(_vm.translate("JS_NO_RESULTS_FOUND")) + "\n  "
            )
          ],
          1
        )
      ],
      1
    )
  };
  var __vue_staticRenderFns__$4 = [];
  __vue_render__$4._withStripped = true;

    /* style */
    const __vue_inject_styles__$4 = function (inject) {
      if (!inject) return
      inject("data-v-3707a725_0", { source: "\n.KnowledgeBase__ArticlesList .q-table__bottom {\r\n  display: none !important;\n}\r\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$4 = undefined;
    /* module identifier */
    const __vue_module_identifier__$4 = undefined;
    /* functional template */
    const __vue_is_functional_template__$4 = false;
    /* style inject SSR */
    

    
    var ArticlesList = normalizeComponent_1(
      { render: __vue_render__$4, staticRenderFns: __vue_staticRenderFns__$4 },
      __vue_inject_styles__$4,
      __vue_script__$4,
      __vue_scope_id__$4,
      __vue_is_functional_template__$4,
      __vue_module_identifier__$4,
      browser,
      undefined
    );

  function unwrapExports (x) {
  	return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, 'default') ? x['default'] : x;
  }

  function createCommonjsModule(fn, module) {
  	return module = { exports: {} }, fn(module, module.exports), module.exports;
  }

  var dist = createCommonjsModule(function (module, exports) {
  !function(t,i){module.exports=i();}(window,function(){return function(t){function i(n){if(e[n])return e[n].exports;var o=e[n]={i:n,l:!1,exports:{}};return t[n].call(o.exports,o,o.exports,i),o.l=!0,o.exports}var e={};return i.m=t,i.c=e,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{configurable:!1,enumerable:!0,get:n});},i.r=function(t){Object.defineProperty(t,"__esModule",{value:!0});},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,i){return Object.prototype.hasOwnProperty.call(t,i)},i.p="",i(i.s=44)}({0:function(t,i,e){var n=e(16);"string"==typeof n&&(n=[[t.i,n,""]]);var o={};o.transform=void 0;e(5)(n,o);n.locals&&(t.exports=n.locals);},1:function(t,i,e){Object.defineProperty(i,"__esModule",{value:!0});var n={y:{t:"top",m:"marginTop",b:"bottom"},x:{l:"left",m:"marginLeft",r:"right"}};i.default={name:"vue-drag-resize",props:{parentScaleX:{type:Number,default:1},parentScaleY:{type:Number,default:1},isActive:{type:Boolean,default:!1},preventActiveBehavior:{type:Boolean,default:!1},isDraggable:{type:Boolean,default:!0},isResizable:{type:Boolean,default:!0},aspectRatio:{type:Boolean,default:!1},parentLimitation:{type:Boolean,default:!1},parentW:{type:Number,default:0,validator:function(t){return t>=0}},parentH:{type:Number,default:0,validator:function(t){return t>=0}},w:{type:Number,default:100,validator:function(t){return t>0}},h:{type:Number,default:100,validator:function(t){return t>0}},minw:{type:Number,default:50,validator:function(t){return t>0}},minh:{type:Number,default:50,validator:function(t){return t>0}},x:{type:Number,default:0,validator:function(t){return "number"==typeof t}},y:{type:Number,default:0,validator:function(t){return "number"==typeof t}},z:{type:[String,Number],default:"auto",validator:function(t){return "string"==typeof t?"auto"===t:t>=0}},dragHandle:{type:String,default:null},dragCancel:{type:String,default:null},sticks:{type:Array,default:function(){return ["tl","tm","tr","mr","br","bm","bl","ml"]}},axis:{type:String,default:"both",validator:function(t){return -1!==["x","y","both","none"].indexOf(t)}}},data:function(){return {active:this.isActive,rawWidth:this.w,rawHeight:this.h,rawLeft:this.x,rawTop:this.y,rawRight:null,rawBottom:null,zIndex:this.z,aspectFactor:this.w/this.h,parentWidth:null,parentHeight:null,left:this.x,top:this.y,right:null,bottom:null,minWidth:this.minw,minHeight:this.minh}},created:function(){this.stickDrag=!1,this.bodyDrag=!1,this.stickAxis=null,this.stickStartPos={mouseX:0,mouseY:0,x:0,y:0,w:0,h:0},this.limits={minLeft:null,maxLeft:null,minRight:null,maxRight:null,minTop:null,maxTop:null,minBottom:null,maxBottom:null},this.currentStick=[];},mounted:function(){if(this.parentElement=this.$el.parentNode,this.parentWidth=this.parentW?this.parentW:this.parentElement.clientWidth,this.parentHeight=this.parentH?this.parentH:this.parentElement.clientHeight,this.rawRight=this.parentWidth-this.rawWidth-this.rawLeft,this.rawBottom=this.parentHeight-this.rawHeight-this.rawTop,document.documentElement.addEventListener("mousemove",this.move),document.documentElement.addEventListener("mouseup",this.up),document.documentElement.addEventListener("mouseleave",this.up),document.documentElement.addEventListener("mousedown",this.deselect),document.documentElement.addEventListener("touchmove",this.move,!0),document.documentElement.addEventListener("touchend touchcancel",this.up,!0),document.documentElement.addEventListener("touchstart",this.up,!0),this.dragHandle){var t=Array.prototype.slice.call(this.$el.querySelectorAll(this.dragHandle));for(var i in t)t[i].setAttribute("data-drag-handle",this._uid);}if(this.dragCancel){var e=Array.prototype.slice.call(this.$el.querySelectorAll(this.dragCancel));for(var n in e)e[n].setAttribute("data-drag-cancel",this._uid);}},beforeDestroy:function(){document.documentElement.removeEventListener("mousemove",this.move),document.documentElement.removeEventListener("mouseup",this.up),document.documentElement.removeEventListener("mouseleave",this.up),document.documentElement.removeEventListener("mousedown",this.deselect),document.documentElement.removeEventListener("touchmove",this.move,!0),document.documentElement.removeEventListener("touchend touchcancel",this.up,!0),document.documentElement.removeEventListener("touchstart",this.up,!0);},methods:{deselect:function(){this.preventActiveBehavior||(this.active=!1);},move:function(t){(this.stickDrag||this.bodyDrag)&&(t.stopPropagation(),this.stickDrag&&this.stickMove(t),this.bodyDrag&&this.bodyMove(t));},up:function(t){this.stickDrag&&this.stickUp(t),this.bodyDrag&&this.bodyUp(t);},bodyDown:function(t){var i=t.target||t.srcElement;this.preventActiveBehavior||(this.active=!0),t.button&&0!==t.button||(this.$emit("clicked",t),this.isDraggable&&this.active&&(this.dragHandle&&i.getAttribute("data-drag-handle")!==this._uid.toString()||this.dragCancel&&i.getAttribute("data-drag-cancel")===this._uid.toString()||(this.bodyDrag=!0,this.stickStartPos.mouseX=t.pageX||t.touches[0].pageX,this.stickStartPos.mouseY=t.pageY||t.touches[0].pageY,this.stickStartPos.left=this.left,this.stickStartPos.right=this.right,this.stickStartPos.top=this.top,this.stickStartPos.bottom=this.bottom,this.parentLimitation&&(this.limits=this.calcDragLimitation()))));},calcDragLimitation:function(){var t=this.parentWidth,i=this.parentHeight;return {minLeft:0,maxLeft:t-this.width,minRight:0,maxRight:t-this.width,minTop:0,maxTop:i-this.height,minBottom:0,maxBottom:i-this.height}},bodyMove:function(t){var i=this.stickStartPos,e={x:("y"!==this.axis&&"none"!==this.axis?i.mouseX-(t.pageX||t.touches[0].pageX):0)/this.parentScaleX,y:("x"!==this.axis&&"none"!==this.axis?i.mouseY-(t.pageY||t.touches[0].pageY):0)/this.parentScaleY};this.rawTop=i.top-e.y,this.rawBottom=i.bottom+e.y,this.rawLeft=i.left-e.x,this.rawRight=i.right+e.x,this.$emit("dragging",this.rect);},bodyUp:function(){this.bodyDrag=!1,this.$emit("dragging",this.rect),this.$emit("dragstop",this.rect),this.stickStartPos={mouseX:0,mouseY:0,x:0,y:0,w:0,h:0},this.limits={minLeft:null,maxLeft:null,minRight:null,maxRight:null,minTop:null,maxTop:null,minBottom:null,maxBottom:null};},stickDown:function(t,i){if(this.isResizable&&this.active){switch(this.stickDrag=!0,this.stickStartPos.mouseX=i.pageX||i.touches[0].pageX,this.stickStartPos.mouseY=i.pageY||i.touches[0].pageY,this.stickStartPos.left=this.left,this.stickStartPos.right=this.right,this.stickStartPos.top=this.top,this.stickStartPos.bottom=this.bottom,this.currentStick=t.split(""),this.stickAxis=null,this.currentStick[0]){case"b":case"t":this.stickAxis="y";}switch(this.currentStick[1]){case"r":case"l":this.stickAxis="y"===this.stickAxis?"xy":"x";}this.limits=this.calcResizeLimitation();}},calcResizeLimitation:function(){var t=this.minWidth,i=this.minHeight,e=this.aspectFactor,n=this.width,o=this.height,s=this.bottom,r=this.top,a=this.left,h=this.right,c=this.stickAxis,u=this.parentLimitation?0:null;this.aspectRatio&&(t/i>e?i=t/e:t=e*i);var l={minLeft:u,maxLeft:a+(n-t),minRight:u,maxRight:h+(n-t),minTop:u,maxTop:r+(o-i),minBottom:u,maxBottom:s+(o-i)};if(this.aspectRatio){var m={minLeft:a-Math.min(r,s)*e*2,maxLeft:a+(o-i)/2*e*2,minRight:h-Math.min(r,s)*e*2,maxRight:h+(o-i)/2*e*2,minTop:r-Math.min(a,h)/e*2,maxTop:r+(n-t)/2/e*2,minBottom:s-Math.min(a,h)/e*2,maxBottom:s+(n-t)/2/e*2};"x"===c?l={minLeft:Math.max(l.minLeft,m.minLeft),maxLeft:Math.min(l.maxLeft,m.maxLeft),minRight:Math.max(l.minRight,m.minRight),maxRight:Math.min(l.maxRight,m.maxRight)}:"y"===c&&(l={minTop:Math.max(l.minTop,m.minTop),maxTop:Math.min(l.maxTop,m.maxTop),minBottom:Math.max(l.minBottom,m.minBottom),maxBottom:Math.min(l.maxBottom,m.maxBottom)});}return l},stickMove:function(t){var i=this.stickStartPos,e={x:(i.mouseX-(t.pageX||t.touches[0].pageX))/this.parentScaleX,y:(i.mouseY-(t.pageY||t.touches[0].pageY))/this.parentScaleY};switch(this.currentStick[0]){case"b":this.rawBottom=i.bottom+e.y;break;case"t":this.rawTop=i.top-e.y;}switch(this.currentStick[1]){case"r":this.rawRight=i.right+e.x;break;case"l":this.rawLeft=i.left-e.x;}this.$emit("resizing",this.rect);},stickUp:function(){this.stickDrag=!1,this.stickStartPos={mouseX:0,mouseY:0,x:0,y:0,w:0,h:0},this.limits={minLeft:null,maxLeft:null,minRight:null,maxRight:null,minTop:null,maxTop:null,minBottom:null,maxBottom:null},this.rawTop=this.top,this.rawBottom=this.bottom,this.rawLeft=this.left,this.rawRight=this.right,this.stickAxis=null,this.$emit("resizing",this.rect),this.$emit("resizestop",this.rect);},aspectRatioCorrection:function(){if(this.aspectRatio){var t=this.bottom,i=this.top,e=this.left,n=this.right,o=this.width,s=this.height,r=this.aspectFactor,a=this.currentStick;if(o/s>r){var h=r*s;"l"===a[1]?this.left=e+o-h:this.right=n+o-h;}else{var c=o/r;"t"===a[0]?this.top=i+s-c:this.bottom=t+s-c;}}}},computed:{style:function(){return {top:this.top+"px",left:this.left+"px",width:this.width+"px",height:this.height+"px",zIndex:this.zIndex}},vdrStick:function(){var t=this;return function(i){var e={width:8/t.parentScaleX+"px",height:8/t.parentScaleY+"px"};return e[n.y[i[0]]]=8/t.parentScaleX/-2+"px",e[n.x[i[1]]]=8/t.parentScaleX/-2+"px",e}},width:function(){return this.parentWidth-this.left-this.right},height:function(){return this.parentHeight-this.top-this.bottom},rect:function(){return {left:Math.round(this.left),top:Math.round(this.top),width:Math.round(this.width),height:Math.round(this.height)}}},watch:{rawLeft:function(t){var i=this.limits,e=this.stickAxis,n=this.aspectFactor,o=this.aspectRatio,s=this.left,r=this.bottom,a=this.top;if(null!==i.minLeft&&t<i.minLeft?t=i.minLeft:null!==i.maxLeft&&i.maxLeft<t&&(t=i.maxLeft),o&&"x"===e){var h=s-t;this.rawTop=a-h/n/2,this.rawBottom=r-h/n/2;}this.left=t;},rawRight:function(t){var i=this.limits,e=this.stickAxis,n=this.aspectFactor,o=this.aspectRatio,s=this.right,r=this.bottom,a=this.top;if(null!==i.minRight&&t<i.minRight?t=i.minRight:null!==i.maxRight&&i.maxRight<t&&(t=i.maxRight),o&&"x"===e){var h=s-t;this.rawTop=a-h/n/2,this.rawBottom=r-h/n/2;}this.right=t;},rawTop:function(t){var i=this.limits,e=this.stickAxis,n=this.aspectFactor,o=this.aspectRatio,s=this.right,r=this.left,a=this.top;if(null!==i.minTop&&t<i.minTop?t=i.minTop:null!==i.maxTop&&i.maxTop<t&&(t=i.maxTop),o&&"y"===e){var h=a-t;this.rawLeft=r-h*n/2,this.rawRight=s-h*n/2;}this.top=t;},rawBottom:function(t){var i=this.limits,e=this.stickAxis,n=this.aspectFactor,o=this.aspectRatio,s=this.right,r=this.left,a=this.bottom;if(null!==i.minBottom&&t<i.minBottom?t=i.minBottom:null!==i.maxBottom&&i.maxBottom<t&&(t=i.maxBottom),o&&"y"===e){var h=a-t;this.rawLeft=r-h*n/2,this.rawRight=s-h*n/2;}this.bottom=t;},width:function(){this.aspectRatioCorrection();},height:function(){this.aspectRatioCorrection();},active:function(t){t?this.$emit("activated"):this.$emit("deactivated");},isActive:function(t){this.active=t;},z:function(t){(t>=0||"auto"===t)&&(this.zIndex=t);},aspectRatio:function(t){t&&(this.aspectFactor=this.width/this.height);},minw:function(t){t>0&&t<=this.width&&(this.minWidth=t);},minh:function(t){t>0&&t<=this.height&&(this.minHeight=t);},x:function(){if(!this.stickDrag&&!this.bodyDrag){this.parentLimitation&&(this.limits=this.calcDragLimitation());var t=this.x-this.left;this.rawLeft=this.x,this.rawRight=this.right-t;}},y:function(){if(!this.stickDrag&&!this.bodyDrag){this.parentLimitation&&(this.limits=this.calcDragLimitation());var t=this.y-this.top;this.rawTop=this.y,this.rawBottom=this.bottom-t;}},w:function(){if(!this.stickDrag&&!this.bodyDrag){this.currentStick=["m","r"],this.stickAxis="x",this.parentLimitation&&(this.limits=this.calcResizeLimitation());var t=this.width-this.w;this.rawRight=this.right+t;}},h:function(){if(!this.stickDrag&&!this.bodyDrag){this.currentStick=["b","m"],this.stickAxis="y",this.parentLimitation&&(this.limits=this.calcResizeLimitation());var t=this.height-this.h;this.rawBottom=this.bottom+t;}},parentW:function(t){this.right=t-this.width-this.left,this.parentWidth=t;},parentH:function(t){this.bottom=t-this.height-this.top,this.parentHeight=t;}}};},15:function(t,i){t.exports=function(t){var i="undefined"!=typeof window&&window.location;if(!i)throw new Error("fixUrls requires window.location");if(!t||"string"!=typeof t)return t;var e=i.protocol+"//"+i.host,n=e+i.pathname.replace(/\/[^\/]*$/,"/");return t.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi,function(t,i){var o=i.trim().replace(/^"(.*)"$/,function(t,i){return i}).replace(/^'(.*)'$/,function(t,i){return i});if(/^(#|data:|http:\/\/|https:\/\/|file:\/\/\/)/i.test(o))return t;var s;return s=0===o.indexOf("//")?o:0===o.indexOf("/")?e+o:n+o.replace(/^\.\//,""),"url("+JSON.stringify(s)+")"})};},16:function(t,i,e){i=t.exports=e(6)(!1),i.push([t.i,'\n.vdr,.vdr.active:before{position:absolute;-webkit-box-sizing:border-box;box-sizing:border-box\n}\n.vdr.active:before{content:"";width:100%;height:100%;top:0;left:0;outline:1px dashed #d6d6d6\n}\n.vdr-stick{-webkit-box-sizing:border-box;box-sizing:border-box;position:absolute;font-size:1px;background:#fff;border:1px solid #6c6c6c;-webkit-box-shadow:0 0 2px #bbb;box-shadow:0 0 2px #bbb\n}\n.inactive .vdr-stick{display:none\n}\n.vdr-stick-br,.vdr-stick-tl{cursor:nwse-resize\n}\n.vdr-stick-bm,.vdr-stick-tm{left:50%;cursor:ns-resize\n}\n.vdr-stick-bl,.vdr-stick-tr{cursor:nesw-resize\n}\n.vdr-stick-ml,.vdr-stick-mr{top:50%;cursor:ew-resize\n}\n.vdr-stick.not-resizable{display:none\n}',""]);},17:function(t,i,e){var n=e(0),o=e.n(n);o.a;},18:function(t,i,e){e.r(i);var n=e(4),o=e(2);for(var s in o)"default"!==s&&function(t){e.d(i,t,function(){return o[t]});}(s);var r=(e(17),e(3)),a=Object(r.a)(o.default,n.a,n.b,!1,null,null,null);a.options.__file="src/components/vue-drag-resize.vue",i.default=a.exports;},2:function(t,i,e){e.r(i);var n=e(1),o=e.n(n);for(var s in n)"default"!==s&&function(t){e.d(i,t,function(){return n[t]});}(s);i.default=o.a;},3:function(t,i,e){function n(t,i,e,n,o,s,r,a){var h="function"==typeof t?t.options:t;i&&(h.render=i,h.staticRenderFns=e,h._compiled=!0),n&&(h.functional=!0),s&&(h._scopeId="data-v-"+s);var c;if(r?(c=function(t){t=t||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext,t||"undefined"==typeof __VUE_SSR_CONTEXT__||(t=__VUE_SSR_CONTEXT__),o&&o.call(this,t),t&&t._registeredComponents&&t._registeredComponents.add(r);},h._ssrRegister=c):o&&(c=a?function(){o.call(this,this.$root.$options.shadowRoot);}:o),c)if(h.functional){h._injectStyles=c;var u=h.render;h.render=function(t,i){return c.call(i),u(t,i)};}else{var l=h.beforeCreate;h.beforeCreate=l?[].concat(l,c):[c];}return {exports:t,options:h}}e.d(i,"a",function(){return n});},4:function(t,i,e){var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"vdr",class:t.active||t.isActive?"active":"inactive",style:t.style,on:{mousedown:function(i){i.stopPropagation(),i.preventDefault(),t.bodyDown(i);},touchstart:function(i){i.stopPropagation(),i.preventDefault(),t.bodyDown(i);}}},[t._t("default"),t._v(" "),t._l(t.sticks,function(i){return e("div",{staticClass:"vdr-stick",class:["vdr-stick-"+i,t.isResizable?"":"not-resizable"],style:t.vdrStick(i),on:{mousedown:function(e){e.stopPropagation(),e.preventDefault(),t.stickDown(i,e);},touchstart:function(e){e.stopPropagation(),e.preventDefault(),t.stickDown(i,e);}}})})],2)},o=[];n._withStripped=!0;e.d(i,"a",function(){return n}),e.d(i,"b",function(){return o});},44:function(t,i,e){function n(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(i,"__esModule",{value:!0});var o=e(18);Object.defineProperty(i,"default",{enumerable:!0,get:function(){return n(o).default}});},5:function(t,i,e){function n(t,i){for(var e=0;e<t.length;e++){var n=t[e],o=p[n.id];if(o){o.refs++;for(var s=0;s<o.parts.length;s++)o.parts[s](n.parts[s]);for(;s<n.parts.length;s++)o.parts.push(u(n.parts[s],i));}else{for(var r=[],s=0;s<n.parts.length;s++)r.push(u(n.parts[s],i));p[n.id]={id:n.id,refs:1,parts:r};}}}function o(t,i){for(var e=[],n={},o=0;o<t.length;o++){var s=t[o],r=i.base?s[0]+i.base:s[0],a=s[1],h=s[2],c=s[3],u={css:a,media:h,sourceMap:c};n[r]?n[r].parts.push(u):e.push(n[r]={id:r,parts:[u]});}return e}function s(t,i){var e=v(t.insertInto);if(!e)throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");var n=x[x.length-1];if("top"===t.insertAt)n?n.nextSibling?e.insertBefore(i,n.nextSibling):e.appendChild(i):e.insertBefore(i,e.firstChild),x.push(i);else{if("bottom"!==t.insertAt)throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");e.appendChild(i);}}function r(t){if(null===t.parentNode)return !1;t.parentNode.removeChild(t);var i=x.indexOf(t);i>=0&&x.splice(i,1);}function a(t){var i=document.createElement("style");return t.attrs.type="text/css",c(i,t.attrs),s(t,i),i}function h(t){var i=document.createElement("link");return t.attrs.type="text/css",t.attrs.rel="stylesheet",c(i,t.attrs),s(t,i),i}function c(t,i){Object.keys(i).forEach(function(e){t.setAttribute(e,i[e]);});}function u(t,i){var e,n,o,s;if(i.transform&&t.css){if(!(s=i.transform(t.css)))return function(){};t.css=s;}if(i.singleton){var c=b++;e=g||(g=a(i)),n=l.bind(null,e,c,!1),o=l.bind(null,e,c,!0);}else t.sourceMap&&"function"==typeof URL&&"function"==typeof URL.createObjectURL&&"function"==typeof URL.revokeObjectURL&&"function"==typeof Blob&&"function"==typeof btoa?(e=h(i),n=f.bind(null,e,i),o=function(){r(e),e.href&&URL.revokeObjectURL(e.href);}):(e=a(i),n=m.bind(null,e),o=function(){r(e);});return n(t),function(i){if(i){if(i.css===t.css&&i.media===t.media&&i.sourceMap===t.sourceMap)return;n(t=i);}else o();}}function l(t,i,e,n){var o=e?"":n.css;if(t.styleSheet)t.styleSheet.cssText=w(i,o);else{var s=document.createTextNode(o),r=t.childNodes;r[i]&&t.removeChild(r[i]),r.length?t.insertBefore(s,r[i]):t.appendChild(s);}}function m(t,i){var e=i.css,n=i.media;if(n&&t.setAttribute("media",n),t.styleSheet)t.styleSheet.cssText=e;else{for(;t.firstChild;)t.removeChild(t.firstChild);t.appendChild(document.createTextNode(e));}}function f(t,i,e){var n=e.css,o=e.sourceMap,s=void 0===i.convertToAbsoluteUrls&&o;(i.convertToAbsoluteUrls||s)&&(n=y(n)),o&&(n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(o))))+" */");var r=new Blob([n],{type:"text/css"}),a=t.href;t.href=URL.createObjectURL(r),a&&URL.revokeObjectURL(a);}var p={},d=function(t){var i;return function(){return void 0===i&&(i=t.apply(this,arguments)),i}}(function(){return window&&document&&document.all&&!window.atob}),v=function(t){var i={};return function(e){return void 0===i[e]&&(i[e]=t.call(this,e)),i[e]}}(function(t){return document.querySelector(t)}),g=null,b=0,x=[],y=e(15);t.exports=function(t,i){if("undefined"!=typeof DEBUG&&DEBUG&&"object"!=typeof document)throw new Error("The style-loader cannot be used in a non-browser environment");i=i||{},i.attrs="object"==typeof i.attrs?i.attrs:{},i.singleton||(i.singleton=d()),i.insertInto||(i.insertInto="head"),i.insertAt||(i.insertAt="bottom");var e=o(t,i);return n(e,i),function(t){for(var s=[],r=0;r<e.length;r++){var a=e[r],h=p[a.id];h.refs--,s.push(h);}if(t){n(o(t,i),i);}for(var r=0;r<s.length;r++){var h=s[r];if(0===h.refs){for(var c=0;c<h.parts.length;c++)h.parts[c]();delete p[h.id];}}}};var w=function(){var t=[];return function(i,e){return t[i]=e,t.filter(Boolean).join("\n")}}();},6:function(t,i){function e(t,i){var e=t[1]||"",o=t[3];if(!o)return e;if(i&&"function"==typeof btoa){var s=n(o);return [e].concat(o.sources.map(function(t){return "/*# sourceURL="+o.sourceRoot+t+" */"})).concat([s]).join("\n")}return [e].join("\n")}function n(t){return "/*# sourceMappingURL=data:application/json;charset=utf-8;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(t))))+" */"}t.exports=function(t){var i=[];return i.toString=function(){return this.map(function(i){var n=e(i,t);return i[2]?"@media "+i[2]+"{"+n+"}":n}).join("")},i.i=function(t,e){"string"==typeof t&&(t=[[null,t,""]]);for(var n={},o=0;o<this.length;o++){var s=this[o][0];"number"==typeof s&&(n[s]=!0);}for(o=0;o<t.length;o++){var r=t[o];"number"==typeof r[0]&&n[r[0]]||(e&&!r[2]?r[2]=e:e&&(r[2]="("+r[2]+") and ("+e+")"),i.push(r));}},i};}})});
  });

  var VueDragResize = unwrapExports(dist);
  var dist_1 = dist.VueDragResize;

  //
  const {
    mapGetters: mapGetters$2
  } = createNamespacedHelpers('KnowledgeBase');
  var script$5 = {
    name: 'DragResize',
    components: {
      VueDragResize
    },
    props: {
      maximized: {
        type: Boolean,
        required: true
      },
      coordinates: {
        type: Object,
        required: true
      }
    },

    data() {
      return {
        active: false
      };
    },

    methods: {
      resize(newRect) {
        this.$emit('onChangeCoordinates', {
          width: newRect.width,
          height: newRect.height,
          top: newRect.top,
          left: newRect.left
        });
      },

      onActivated() {
        $(this.$refs.resize.$el).find('.vdr-stick').addClass('mdi mdi-resize-bottom-right q-btn q-btn--dense q-btn--round q-icon contrast-50');
      },

      onFocusElement(event) {
        event.target.focus();
      }

    },

    mounted() {
      this.active = true;
    }

  };

  /* script */
  const __vue_script__$5 = script$5;

  /* template */
  var __vue_render__$5 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "div",
      [
        _vm.$q.platform.is.desktop
          ? _c(
              "vue-drag-resize",
              {
                ref: "resize",
                class: [
                  _vm.maximized ? "fit position-sticky" : "modal-mini",
                  "overflow-hidden"
                ],
                attrs: {
                  isActive: _vm.active,
                  isResizable: true,
                  isDraggable: !_vm.maximized,
                  dragHandle: ".js-drag",
                  sticks: ["br"],
                  x: _vm.coordinates.left,
                  y: _vm.coordinates.top,
                  w: _vm.coordinates.width,
                  h: _vm.coordinates.height
                },
                on: {
                  activated: _vm.onActivated,
                  resizing: _vm.resize,
                  dragging: _vm.resize
                }
              },
              [
                _c(
                  "div",
                  {
                    staticClass: "fit",
                    on: {
                      mousedown: function($event) {
                        return _vm.onFocusElement($event)
                      },
                      touchstart: function($event) {
                        return _vm.onFocusElement($event)
                      }
                    }
                  },
                  [_vm._t("default")],
                  2
                )
              ]
            )
          : _c("div", { staticClass: "fit" }, [_vm._t("default")], 2)
      ],
      1
    )
  };
  var __vue_staticRenderFns__$5 = [];
  __vue_render__$5._withStripped = true;

    /* style */
    const __vue_inject_styles__$5 = function (inject) {
      if (!inject) return
      inject("data-v-680433a6_0", { source: "\n.modal-mini {\r\n  max-height: unset !important;\r\n  max-width: unset !important;\n}\n.vdr-stick.q-icon:before {\r\n  font-size: 1.718em;\r\n  left: -5px;\r\n  position: relative;\r\n  bottom: 5px;\n}\n.vdr-stick.q-icon {\r\n  bottom: 9px !important;\r\n  right: 25px !important;\r\n  font-size: 14px;\r\n  background: none;\r\n  border: none;\r\n  box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12);\r\n  display: none;\r\n  cursor: nwse-resize !important;\r\n  position: absolute !important;\n}\n.vdr.active {\r\n  font-weight: unset;\n}\n.modal-mini .vdr-stick {\r\n  display: inline-flex;\n}\r\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$5 = undefined;
    /* module identifier */
    const __vue_module_identifier__$5 = undefined;
    /* functional template */
    const __vue_is_functional_template__$5 = false;
    /* style inject SSR */
    

    
    var DragResize = normalizeComponent_1(
      { render: __vue_render__$5, staticRenderFns: __vue_staticRenderFns__$5 },
      __vue_inject_styles__$5,
      __vue_script__$5,
      __vue_scope_id__$5,
      __vue_is_functional_template__$5,
      __vue_module_identifier__$5,
      browser,
      undefined
    );

  //
  const {
    mapGetters: mapGetters$3,
    mapActions: mapActions$2
  } = createNamespacedHelpers('KnowledgeBase');
  var script$6 = {
    name: 'ArticlePreviewContent',
    components: {
      Icon,
      Carousel,
      ArticlesList,
      ColumnsGrid
    },
    props: {
      height: {
        type: Number,
        default: 0
      },
      previewMaximized: {
        type: Boolean,
        true: 0
      }
    },
    computed: { ...mapGetters$3(['tree', 'record', 'iconSize']),

      relatedRecords() {
        if (this.record) {
          let arr = Object.keys(this.record.related.dynamic).map(key => {
            return this.record.related.dynamic[key].length !== 0 ? key : false;
          });
          return arr.filter(function (item) {
            return typeof item === 'string';
          });
        } else {
          return [];
        }
      },

      hasRelatedArticles() {
        return this.record ? this.record.related.base.Articles.length !== 0 : false;
      },

      hasRelatedComments() {
        return this.record ? this.record.related.base.ModComments.length !== 0 : false;
      }

    },
    watch: {
      previewMaximized() {
        this.$emit('onMaximizedToggle', this.previewMaximized);
      }

    },
    methods: { ...mapActions$2(['fetchCategories', 'fetchRecord', 'initState']),

      onResize(size) {
        if (this.$refs.content !== undefined) {
          $(this.$refs.content).find('img').css('max-width', size.width);
        }
      }

    }
  };

  /* script */
  const __vue_script__$6 = script$6;

  /* template */
  var __vue_render__$6 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "q-card",
      { staticClass: "KnowledgeBase__ArticlePreview fit" },
      [
        _c(
          "q-bar",
          {
            staticClass: "bg-yeti text-white dialog-header",
            attrs: { dark: "" }
          },
          [
            _c("div", { staticClass: "flex items-center" }, [
              _c(
                "div",
                { staticClass: "flex items-center no-wrap ellipsis q-mr-sm-sm" },
                [
                  _c("q-icon", {
                    staticClass: "q-mr-sm",
                    attrs: { name: "mdi-text" }
                  }),
                  _vm._v("\n        " + _vm._s(_vm.record.subject) + "\n      ")
                ],
                1
              ),
              _vm._v(" "),
              _c(
                "div",
                { staticClass: "flex items-center text-grey-4 small" },
                [
                  _c(
                    "div",
                    { staticClass: "flex items-center" },
                    [
                      _c("q-icon", {
                        attrs: { name: _vm.tree.topCategory.icon, size: "15px" }
                      }),
                      _vm._v(" "),
                      _c("q-icon", {
                        attrs: { size: "1.5em", name: "mdi-chevron-right" }
                      }),
                      _vm._v(" "),
                      _c("span", {
                        staticClass: "flex items-center",
                        domProps: { innerHTML: _vm._s(_vm.record.category) }
                      }),
                      _vm._v(" "),
                      _c("q-tooltip", [
                        _vm._v(
                          "\n            " +
                            _vm._s(_vm.translate("JS_KB_CATEGORY")) +
                            "\n          "
                        )
                      ])
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _c("q-separator", {
                    attrs: { dark: "", vertical: "", spaced: "" }
                  }),
                  _vm._v(" "),
                  _c(
                    "div",
                    [
                      _c("q-icon", {
                        attrs: { name: "mdi-calendar-clock", size: "15px" }
                      }),
                      _vm._v(
                        "\n          " +
                          _vm._s(_vm.record.short_createdtime) +
                          "\n          "
                      ),
                      _c("q-tooltip", [
                        _vm._v(
                          "\n            " +
                            _vm._s(
                              _vm.translate("JS_KB_CREATED") +
                                ": " +
                                _vm.record.full_createdtime
                            ) +
                            "\n          "
                        )
                      ])
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _vm.record.short_modifiedtime
                    ? [
                        _c("q-separator", {
                          attrs: { dark: "", vertical: "", spaced: "" }
                        }),
                        _vm._v(" "),
                        _c(
                          "div",
                          [
                            _c("q-icon", {
                              attrs: {
                                name: "mdi-square-edit-outline",
                                size: "15px"
                              }
                            }),
                            _vm._v(
                              "\n            " +
                                _vm._s(_vm.record.short_modifiedtime) +
                                "\n            "
                            ),
                            _c("q-tooltip", [
                              _vm._v(
                                "\n              " +
                                  _vm._s(
                                    _vm.translate("JS_KB_MODIFIED") +
                                      ": " +
                                      _vm.record.full_modifiedtime
                                  ) +
                                  "\n            "
                              )
                            ])
                          ],
                          1
                        )
                      ]
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.record.accountId
                    ? [
                        _c("q-separator", {
                          attrs: { dark: "", vertical: "", spaced: "" }
                        }),
                        _vm._v(" "),
                        _c("icon", {
                          attrs: { icon: "userIcon-Accounts", size: "15px" }
                        }),
                        _vm._v(" "),
                        _c(
                          "a",
                          {
                            staticClass:
                              "js-popover-tooltip--record ellipsis q-ml-xs text-grey-4",
                            attrs: {
                              href:
                                "index.php?module=Accounts&view=Detail&record=" +
                                _vm.record.accountId
                            }
                          },
                          [
                            _vm._v(
                              _vm._s(_vm.record.accountName) + "\n          "
                            )
                          ]
                        )
                      ]
                    : _vm._e()
                ],
                2
              )
            ]),
            _vm._v(" "),
            _c("q-space"),
            _vm._v(" "),
            _vm._t("header-right", [
              _vm.$q.platform.is.desktop
                ? [
                    _c(
                      "a",
                      {
                        directives: [
                          {
                            name: "show",
                            rawName: "v-show",
                            value: !_vm.previewMaximized,
                            expression: "!previewMaximized"
                          }
                        ],
                        staticClass:
                          "flex grabbable text-decoration-none text-white",
                        attrs: { href: "#" }
                      },
                      [
                        _c("q-icon", {
                          staticClass: "js-drag",
                          attrs: { name: "mdi-drag", size: "19px" }
                        })
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _c(
                      "q-btn",
                      {
                        attrs: {
                          dense: "",
                          flat: "",
                          icon: _vm.previewMaximized
                            ? "mdi-window-restore"
                            : "mdi-window-maximize"
                        },
                        on: {
                          click: function($event) {
                            _vm.previewMaximized = !_vm.previewMaximized;
                          }
                        }
                      },
                      [
                        _c("q-tooltip", [
                          _vm._v(
                            _vm._s(
                              _vm.previewMaximized
                                ? _vm.translate("JS_MINIMIZE")
                                : _vm.translate("JS_MAXIMIZE")
                            )
                          )
                        ])
                      ],
                      1
                    )
                  ]
                : _vm._e(),
              _vm._v(" "),
              _c(
                "q-btn",
                {
                  directives: [{ name: "close-popup", rawName: "v-close-popup" }],
                  attrs: { dense: "", flat: "", icon: "mdi-close" }
                },
                [_c("q-tooltip", [_vm._v(_vm._s(_vm.translate("JS_CLOSE")))])],
                1
              )
            ])
          ],
          2
        ),
        _vm._v(" "),
        _c(
          "q-card-section",
          {
            class: ["scroll", _vm.previewMaximized ? "modal-full-height" : ""],
            style: _vm.height ? { "max-height": _vm.height - 31.14 + "px" } : {}
          },
          [
            _c(
              "div",
              {
                directives: [
                  {
                    name: "show",
                    rawName: "v-show",
                    value: _vm.record.introduction,
                    expression: "record.introduction"
                  }
                ]
              },
              [
                _c("div", { staticClass: "text-subtitle2 text-bold" }, [
                  _vm._v(_vm._s(_vm.record.introduction))
                ])
              ]
            ),
            _vm._v(" "),
            _c(
              "div",
              {
                directives: [
                  {
                    name: "show",
                    rawName: "v-show",
                    value: _vm.record.content,
                    expression: "record.content"
                  }
                ]
              },
              [
                _c("q-resize-observer", { on: { resize: _vm.onResize } }),
                _vm._v(" "),
                _c(
                  "div",
                  { ref: "content" },
                  [
                    _vm.record.view === "PLL_PRESENTATION" &&
                    _vm.record.content.length > 1
                      ? _c("carousel", { attrs: { record: _vm.record } })
                      : _c(
                          "div",
                          [
                            _c("q-separator"),
                            _vm._v(" "),
                            _c("div", {
                              domProps: {
                                innerHTML: _vm._s(
                                  typeof _vm.record.content === "object"
                                    ? _vm.record.content[0]
                                    : _vm.record.content
                                )
                              }
                            })
                          ],
                          1
                        )
                  ],
                  1
                )
              ],
              1
            ),
            _vm._v(" "),
            _vm.hasRelatedComments
              ? _c(
                  "div",
                  [
                    _c("q-separator"),
                    _vm._v(" "),
                    _c("div", { staticClass: "q-pa-md q-table__title" }, [
                      _vm._v(_vm._s(_vm.translate("JS_KB_COMMENTS")))
                    ]),
                    _vm._v(" "),
                    _c(
                      "q-list",
                      { attrs: { padding: "" } },
                      _vm._l(_vm.record.related.base.ModComments, function(
                        relatedRecord,
                        relatedRecordId
                      ) {
                        return _c(
                          "q-item",
                          { key: relatedRecordId },
                          [
                            _c(
                              "q-item-section",
                              { attrs: { avatar: "", top: "" } },
                              [
                                _c(
                                  "q-avatar",
                                  { attrs: { size: "iconSize" } },
                                  [
                                    relatedRecord.avatar.url !== undefined
                                      ? _c("img", {
                                          attrs: { src: relatedRecord.avatar.url }
                                        })
                                      : _c("q-icon", {
                                          attrs: { name: "mdi-account" }
                                        })
                                  ],
                                  1
                                )
                              ],
                              1
                            ),
                            _vm._v(" "),
                            _c(
                              "q-item-section",
                              [
                                _c("q-item-label", [
                                  _c(
                                    "a",
                                    {
                                      staticClass: "js-popover-tooltip--record",
                                      attrs: {
                                        href:
                                          "index.php?module=Users&view=Detail&record=" +
                                          relatedRecord.userid
                                      }
                                    },
                                    [
                                      _vm._v(
                                        _vm._s(relatedRecord.userName) +
                                          "\n              "
                                      )
                                    ]
                                  )
                                ]),
                                _vm._v(" "),
                                _c("q-item-label", [
                                  _c("div", {
                                    domProps: {
                                      innerHTML: _vm._s(relatedRecord.comment)
                                    }
                                  })
                                ])
                              ],
                              1
                            ),
                            _vm._v(" "),
                            _c(
                              "q-item-section",
                              { attrs: { side: "", top: "" } },
                              [
                                _c("q-item-label", { attrs: { caption: "" } }, [
                                  _vm._v(_vm._s(relatedRecord.modifiedShort))
                                ]),
                                _vm._v(" "),
                                _c(
                                  "q-tooltip",
                                  {
                                    attrs: {
                                      anchor: "top middle",
                                      self: "center middle"
                                    }
                                  },
                                  [
                                    _vm._v(
                                      "\n              " +
                                        _vm._s(
                                          _vm.translate("JS_KB_MODIFIED") +
                                            ": " +
                                            relatedRecord.modifiedFull
                                        ) +
                                        "\n            "
                                    )
                                  ]
                                )
                              ],
                              1
                            )
                          ],
                          1
                        )
                      }),
                      1
                    )
                  ],
                  1
                )
              : _vm._e(),
            _vm._v(" "),
            _vm.hasRelatedArticles
              ? _c(
                  "div",
                  [
                    _c("q-separator"),
                    _vm._v(" "),
                    _vm.record.related
                      ? _c("articles-list", {
                          attrs: {
                            data: _vm.record.related.base.Articles,
                            title: _vm.translate("JS_KB_RELATED_ARTICLES")
                          }
                        })
                      : _vm._e()
                  ],
                  1
                )
              : _vm._e(),
            _vm._v(" "),
            _c(
              "div",
              {
                directives: [
                  {
                    name: "show",
                    rawName: "v-show",
                    value: _vm.relatedRecords.length,
                    expression: "relatedRecords.length"
                  }
                ]
              },
              [
                _c("q-separator"),
                _vm._v(" "),
                _c("div", { staticClass: "q-pa-md q-table__title" }, [
                  _vm._v(_vm._s(_vm.translate("JS_KB_RELATED_RECORDS")))
                ]),
                _vm._v(" "),
                _c("columns-grid", {
                  attrs: { columnBlocks: _vm.relatedRecords },
                  scopedSlots: _vm._u([
                    {
                      key: "default",
                      fn: function(slotProps) {
                        return [
                          _c(
                            "q-list",
                            { attrs: { bordered: "", padding: "", dense: "" } },
                            [
                              _c(
                                "q-item",
                                {
                                  staticClass: "text-black flex",
                                  attrs: { header: "", clickable: "" }
                                },
                                [
                                  _c("icon", {
                                    staticClass: "mr-2",
                                    attrs: {
                                      icon: "userIcon-" + slotProps.relatedBlock,
                                      size: _vm.iconSize
                                    }
                                  }),
                                  _vm._v(
                                    "\n              " +
                                      _vm._s(
                                        _vm.record.translations[
                                          slotProps.relatedBlock
                                        ]
                                      ) +
                                      "\n            "
                                  )
                                ],
                                1
                              ),
                              _vm._v(" "),
                              _vm._l(
                                _vm.record.related.dynamic[
                                  slotProps.relatedBlock
                                ],
                                function(relatedRecord, relatedRecordId) {
                                  return _c(
                                    "q-item",
                                    {
                                      directives: [
                                        { name: "ripple", rawName: "v-ripple" }
                                      ],
                                      key: relatedRecordId,
                                      staticClass: "text-subtitle2",
                                      attrs: { clickable: "" }
                                    },
                                    [
                                      _c(
                                        "q-item-section",
                                        {
                                          staticClass:
                                            "align-items-center flex-row no-wrap justify-content-start"
                                        },
                                        [
                                          _c(
                                            "a",
                                            {
                                              staticClass:
                                                "js-popover-tooltip--record ellipsis",
                                              attrs: {
                                                href:
                                                  "index.php?module=" +
                                                  slotProps.relatedBlock +
                                                  "&view=Detail&record=" +
                                                  relatedRecordId
                                              }
                                            },
                                            [
                                              _vm._v(
                                                "\n                  " +
                                                  _vm._s(relatedRecord) +
                                                  "\n                "
                                              )
                                            ]
                                          )
                                        ]
                                      )
                                    ],
                                    1
                                  )
                                }
                              )
                            ],
                            2
                          )
                        ]
                      }
                    }
                  ])
                })
              ],
              1
            )
          ]
        )
      ],
      1
    )
  };
  var __vue_staticRenderFns__$6 = [];
  __vue_render__$6._withStripped = true;

    /* style */
    const __vue_inject_styles__$6 = function (inject) {
      if (!inject) return
      inject("data-v-c6430416_0", { source: "\n.dialog-header {\r\n  padding-top: 3px !important;\r\n  padding-bottom: 3px !important;\r\n  height: unset !important;\n}\n.modal-full-height {\r\n  max-height: calc(100vh - 31.14px) !important;\n}\n.grabbable:hover {\r\n  cursor: move;\r\n  cursor: grab;\r\n  cursor: -moz-grab;\r\n  cursor: -webkit-grab;\n}\n.grabbable:active {\r\n  cursor: grabbing;\r\n  cursor: -moz-grabbing;\r\n  cursor: -webkit-grabbing;\n}\n.contrast-50 {\r\n  filter: contrast(50%);\n}\r\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$6 = undefined;
    /* module identifier */
    const __vue_module_identifier__$6 = undefined;
    /* functional template */
    const __vue_is_functional_template__$6 = false;
    /* style inject SSR */
    

    
    var ArticlePreviewContent = normalizeComponent_1(
      { render: __vue_render__$6, staticRenderFns: __vue_staticRenderFns__$6 },
      __vue_inject_styles__$6,
      __vue_script__$6,
      __vue_scope_id__$6,
      __vue_is_functional_template__$6,
      __vue_module_identifier__$6,
      browser,
      undefined
    );

  //
  const {
    mapGetters: mapGetters$4
  } = createNamespacedHelpers('KnowledgeBase');
  var script$7 = {
    name: 'ArticlePreview',
    components: {
      ArticlePreviewContent,
      DragResize
    },
    props: {
      isDragResize: {
        type: Boolean,
        default: true
      },
      maximizedOnly: {
        type: Boolean,
        default: false
      },
      previewDialog: {
        type: Boolean,
        default: false
      }
    },

    data() {
      return {
        coordinates: {
          width: Quasar.plugins.Screen.width - 100,
          height: Quasar.plugins.Screen.height - 100,
          top: 0,
          left: Quasar.plugins.Screen.width - (Quasar.plugins.Screen.width - 100 / 2)
        },
        previewMaximized: true
      };
    },

    watch: {
      previewDialog() {
        this.$emit('onDialogToggle', this.previewDialog);
      }

    },
    methods: {
      onChangeCoordinates: function (coordinates) {
        this.coordinates = coordinates;
      },

      onMaximizedToggle(val) {
        this.previewMaximized = val;
      }

    }
  };

  /* script */
  const __vue_script__$7 = script$7;

  /* template */
  var __vue_render__$7 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "q-dialog",
      {
        attrs: {
          maximized: _vm.maximizedOnly ? true : _vm.previewMaximized,
          "transition-show": "slide-up",
          "transition-hide": "slide-down",
          "content-class": "quasar-reset"
        },
        model: {
          value: _vm.previewDialog,
          callback: function($$v) {
            _vm.previewDialog = $$v;
          },
          expression: "previewDialog"
        }
      },
      [
        _vm.isDragResize
          ? _c(
              "drag-resize",
              {
                attrs: {
                  coordinates: _vm.coordinates,
                  maximized: _vm.previewMaximized
                },
                on: { onChangeCoordinates: _vm.onChangeCoordinates }
              },
              [
                _c("article-preview-content", {
                  attrs: {
                    height: _vm.coordinates.height,
                    previewMaximized: _vm.previewMaximized
                  },
                  on: { onMaximizedToggle: _vm.onMaximizedToggle }
                })
              ],
              1
            )
          : _c(
              "article-preview-content",
              [
                _c(
                  "template",
                  { slot: "header-right" },
                  [_vm._t("header-right")],
                  2
                )
              ],
              2
            )
      ],
      1
    )
  };
  var __vue_staticRenderFns__$7 = [];
  __vue_render__$7._withStripped = true;

    /* style */
    const __vue_inject_styles__$7 = function (inject) {
      if (!inject) return
      inject("data-v-4d136050_0", { source: "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$7 = undefined;
    /* module identifier */
    const __vue_module_identifier__$7 = undefined;
    /* functional template */
    const __vue_is_functional_template__$7 = false;
    /* style inject SSR */
    

    
    var ArticlePreview = normalizeComponent_1(
      { render: __vue_render__$7, staticRenderFns: __vue_staticRenderFns__$7 },
      __vue_inject_styles__$7,
      __vue_script__$7,
      __vue_scope_id__$7,
      __vue_is_functional_template__$7,
      __vue_module_identifier__$7,
      browser,
      undefined
    );

  //
  const {
    mapGetters: mapGetters$5
  } = createNamespacedHelpers('KnowledgeBase');
  var script$8 = {
    name: 'CategoriesList',
    components: {
      Icon
    },

    data() {
      return {
        show: true,
        animationIn: 'slideInLeft',
        animationOut: 'slideOutRight',
        animationChildClassIn: 'slideInRight',
        animationChildClassOut: 'slideOutLeft',
        animationParentClassIn: 'slideInLeft',
        animationParentClassOut: 'slideOutRight',
        activeCategoryDelayed: ''
      };
    },

    props: {
      activeCategory: {
        type: String,
        required: true
      },
      data: {
        type: Object,
        required: true
      }
    },
    computed: { ...mapGetters$5(['tree', 'iconSize', 'defaultTreeIcon'])
    },
    watch: {
      data() {
        this.activeCategoryDelayed = this.activeCategory;
        this.show = true;
      }

    },
    methods: {
      fetchParentCategoryData() {
        this.animationIn = this.animationParentClassIn;
        this.animationOut = this.animationParentClassOut;
        this.show = false;
        this.data.categories = [];
        let parentCategory = '';
        const parentTreeArray = this.tree.categories[this.activeCategory].parentTree;

        if (parentTreeArray.length !== 1) {
          parentCategory = parentTreeArray[parentTreeArray.length - 2];
        }

        this.$emit('fetchData', parentCategory);
      },

      fetchChildCategoryData(categoryValue) {
        this.animationIn = this.animationChildClassIn;
        this.animationOut = this.animationChildClassOut;
        this.show = false;
        this.data.categories = [];
        this.$emit('fetchData', categoryValue);
      }

    },

    mounted() {
      this.activeCategoryDelayed = this.activeCategory;
    }

  };

  /* script */
  const __vue_script__$8 = script$8;

  /* template */
  var __vue_render__$8 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "transition",
      {
        attrs: {
          "enter-active-class": "animated " + _vm.animationIn,
          "leave-active-class": "animated " + _vm.animationOut
        }
      },
      [
        _c(
          "q-list",
          {
            directives: [
              {
                name: "show",
                rawName: "v-show",
                value: _vm.show,
                expression: "show"
              }
            ]
          },
          [
            _c(
              "q-item",
              {
                directives: [
                  {
                    name: "show",
                    rawName: "v-show",
                    value: _vm.activeCategoryDelayed === "",
                    expression: "activeCategoryDelayed === ''"
                  }
                ],
                attrs: { active: "" }
              },
              [
                _c(
                  "q-item-section",
                  { attrs: { avatar: "" } },
                  [
                    _c("q-icon", {
                      attrs: {
                        name: _vm.tree.topCategory.icon,
                        size: _vm.iconSize
                      }
                    })
                  ],
                  1
                ),
                _vm._v(" "),
                _c("q-item-section", [
                  _vm._v(_vm._s(_vm.translate(_vm.tree.topCategory.label)))
                ])
              ],
              1
            ),
            _vm._v(" "),
            _vm.activeCategoryDelayed !== ""
              ? _c(
                  "q-item",
                  {
                    attrs: { clickable: "", active: "" },
                    on: {
                      click: function($event) {
                        return _vm.fetchParentCategoryData()
                      }
                    }
                  },
                  [
                    _c(
                      "q-item-section",
                      { attrs: { avatar: "" } },
                      [
                        _c("icon", {
                          attrs: {
                            size: _vm.iconSize,
                            icon:
                              _vm.tree.categories[_vm.activeCategoryDelayed]
                                .icon || _vm.defaultTreeIcon
                          }
                        })
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _c("q-item-section", [
                      _vm._v(
                        _vm._s(
                          _vm.tree.categories[_vm.activeCategoryDelayed].label
                        )
                      )
                    ]),
                    _vm._v(" "),
                    _c(
                      "q-item-section",
                      { attrs: { avatar: "" } },
                      [_c("q-icon", { attrs: { name: "mdi-chevron-left" } })],
                      1
                    )
                  ],
                  1
                )
              : _vm._e(),
            _vm._v(" "),
            _vm._l(_vm.data.categories, function(categoryValue, categoryKey) {
              return _c(
                "q-item",
                {
                  directives: [{ name: "ripple", rawName: "v-ripple" }],
                  key: categoryKey,
                  attrs: { clickable: "" },
                  on: {
                    click: function($event) {
                      return _vm.fetchChildCategoryData(categoryValue)
                    }
                  }
                },
                [
                  _c(
                    "q-item-section",
                    { attrs: { avatar: "" } },
                    [
                      _c("icon", {
                        attrs: {
                          size: _vm.iconSize,
                          icon:
                            _vm.tree.categories[categoryValue].icon ||
                            _vm.defaultTreeIcon
                        }
                      })
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _c("q-item-section", [
                    _vm._v(_vm._s(_vm.tree.categories[categoryValue].label))
                  ]),
                  _vm._v(" "),
                  _c(
                    "q-item-section",
                    { attrs: { avatar: "" } },
                    [_c("q-icon", { attrs: { name: "mdi-chevron-right" } })],
                    1
                  )
                ],
                1
              )
            })
          ],
          2
        )
      ],
      1
    )
  };
  var __vue_staticRenderFns__$8 = [];
  __vue_render__$8._withStripped = true;

    /* style */
    const __vue_inject_styles__$8 = function (inject) {
      if (!inject) return
      inject("data-v-56f4a4f2_0", { source: "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$8 = undefined;
    /* module identifier */
    const __vue_module_identifier__$8 = undefined;
    /* functional template */
    const __vue_is_functional_template__$8 = false;
    /* style inject SSR */
    

    
    var CategoriesList = normalizeComponent_1(
      { render: __vue_render__$8, staticRenderFns: __vue_staticRenderFns__$8 },
      __vue_inject_styles__$8,
      __vue_script__$8,
      __vue_scope_id__$8,
      __vue_is_functional_template__$8,
      __vue_module_identifier__$8,
      browser,
      undefined
    );

  //
  const {
    mapGetters: mapGetters$6,
    mapActions: mapActions$3
  } = createNamespacedHelpers('KnowledgeBase');
  var script$9 = {
    name: 'KnowledgeBase',
    components: {
      Icon,
      IconInfo,
      Carousel,
      ArticlesList,
      ArticlePreview,
      ColumnsGrid,
      CategoriesList
    },
    props: {
      coordinates: {
        type: Object,
        default: () => {
          return {
            width: 0,
            height: 0,
            top: 0,
            left: 0
          };
        }
      }
    },

    data() {
      return {
        drawerBehaviour: 'desktop',
        miniState: false,
        left: true,
        filter: '',
        accountSearch: '',
        categorySearch: false,
        searchData: false,
        activeCategory: '',
        activeAccount: '',
        previewDialog: false,
        tab: 'categories',
        showAccounts: false,
        data: {
          categories: [],
          records: [],
          featured: {}
        },
        accountsData: {
          categories: [],
          records: [],
          featured: {}
        },
        accounts: []
      };
    },

    computed: { ...mapGetters$6(['tree', 'record', 'iconSize', 'moduleName', 'maximized', 'defaultTreeIcon']),

      accountsList() {
        if (this.accountSearch === '') {
          return this.accounts;
        } else {
          return this.accounts.filter(account => {
            return account.name.toLowerCase().includes(this.accountSearch.toLowerCase());
          });
        }
      },

      selectedTabData() {
        return this.tab === 'categories' ? this.data : this.accountsData;
      },

      searchDataArray() {
        return this.searchData ? this.searchData : [];
      },

      featuredCategories() {
        if (typeof this.selectedTabData.featured.length === 'undefined' && this.selectedTabData.categories) {
          let arr = this.selectedTabData.categories.map(e => {
            return this.selectedTabData.featured[e] ? e : false;
          });
          return arr.filter(function (item) {
            return typeof item === 'string';
          });
        } else {
          return [];
        }
      },

      inputFocus: {
        set(val) {
          return false;
        },

        get() {
          return this.filter.length > 0;
        }

      }
    },
    methods: { ...mapActions$3(['fetchRecord', 'fetchCategories']),

      onTabChange(tabName) {
        if (this.accounts.length === 0 && tabName === 'accounts') {
          this.fetchAccounts();
        }
      },

      fetchAccounts() {
        const aDeferred = $.Deferred();
        const progressIndicatorElement = $.progressIndicator({
          blockInfo: {
            enabled: true
          }
        });
        return AppConnector.request({
          module: this.moduleName,
          action: 'KnowledgeBaseAjax',
          mode: 'getAccounts'
        }).done(data => {
          let listData = data.result;

          if (listData) {
            listData = Object.keys(listData).map(function (key) {
              return {
                name: listData[key],
                id: key
              };
            });
          }

          this.accounts = listData;
          progressIndicatorElement.progressIndicator({
            mode: 'hide'
          });
          aDeferred.resolve(listData);
        });
      },

      search() {
        if (this.filter.length >= 3) {
          this.debouncedSearch();
        } else {
          this.searchData = false;
        }
      },

      clearSearch() {
        this.filter = '';
        this.searchData = false;
      },

      fetchData(category = '', accountId = '') {
        const aDeferred = $.Deferred();

        if (category !== null) {
          this.activeCategory = category;
        }

        const progressIndicatorElement = $.progressIndicator({
          blockInfo: {
            enabled: true
          }
        });
        return AppConnector.request({
          module: this.moduleName,
          action: 'KnowledgeBaseAjax',
          mode: 'list',
          category: category,
          accountid: accountId
        }).done(data => {
          let listData = data.result;

          if (listData.showAccounts) {
            this.showAccounts = true;
          }

          if (listData.records) {
            listData.records = Object.keys(listData.records).map(function (key) {
              return { ...listData.records[key],
                id: key
              };
            });
          }

          if (accountId !== '') {
            this.accountsData = listData;
          } else {
            this.data = listData;
          }

          progressIndicatorElement.progressIndicator({
            mode: 'hide'
          });
          aDeferred.resolve(listData);
        });
      },

      openQuickCreateModal() {
        const headerInstance = new window.Vtiger_Header_Js();
        headerInstance.quickCreateModule(this.moduleName);
      },

      showArticlePreview(id) {
        this.fetchRecord(id).then(() => {
          this.previewDialog = true;
        });
      },

      toggleDrawer() {
        if (this.$q.platform.is.desktop && (!this.coordinates.width || this.coordinates.width > 700)) {
          this.miniState = !this.miniState;
        } else {
          this.left = !this.left;
        }
      },

      onDialogToggle(val) {
        this.previewDialog = val;
      }

    },

    async created() {
      await this.fetchCategories();
      await this.fetchData();
    },

    mounted() {
      const debounceDelay = 1000;
      this.debouncedSearch = Quasar.utils.debounce(() => {
        if (this.filter.length < 3) {
          return;
        }

        const aDeferred = $.Deferred();
        const progressIndicatorElement = $.progressIndicator({
          blockInfo: {
            enabled: true
          }
        });
        AppConnector.request({
          module: this.moduleName,
          action: 'KnowledgeBaseAjax',
          mode: 'search',
          value: this.filter,
          category: this.categorySearch ? this.activeCategory : ''
        }).done(data => {
          let listData = data.result;

          if (listData) {
            listData = Object.keys(listData).map(function (key) {
              return { ...listData[key],
                id: key
              };
            });
          }

          this.searchData = listData;
          aDeferred.resolve(listData);
          progressIndicatorElement.progressIndicator({
            mode: 'hide'
          });
          return listData;
        });
      }, debounceDelay);
    }

  };

  /* script */
  const __vue_script__$9 = script$9;

  /* template */
  var __vue_render__$9 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "div",
      { staticClass: "KnowledgeBase h-100" },
      [
        _c(
          "q-layout",
          {
            staticClass: "absolute",
            style:
              _vm.coordinates.height && !_vm.maximized
                ? { "max-height": _vm.coordinates.height - 31.14 + "px" }
                : {},
            attrs: { view: "hHh Lpr fFf", container: "" }
          },
          [
            _c(
              "q-header",
              { staticClass: "bg-white text-primary", attrs: { elevated: "" } },
              [
                _c(
                  "q-toolbar",
                  {
                    staticClass:
                      "flex-md-nowrap flex-wrap items-center q-gutter-x-md q-gutter-y-sm q-pl-md q-pr-none q-py-xs"
                  },
                  [
                    _c(
                      "div",
                      {
                        class: [
                          "flex items-center no-wrap flex-md-grow-1 q-mr-sm-sm",
                          _vm.searchData ? "invisible" : ""
                        ]
                      },
                      [
                        _c(
                          "q-btn",
                          {
                            attrs: {
                              dense: "",
                              round: "",
                              push: "",
                              icon: "mdi-menu"
                            },
                            on: {
                              click: function($event) {
                                return _vm.toggleDrawer()
                              }
                            }
                          },
                          [
                            _c("q-tooltip", [
                              _vm._v(
                                _vm._s(
                                  _vm.translate("JS_KB_TOGGLE_CATEGORY_MENU")
                                )
                              )
                            ])
                          ],
                          1
                        ),
                        _vm._v(" "),
                        _c(
                          "q-breadcrumbs",
                          {
                            directives: [
                              {
                                name: "show",
                                rawName: "v-show",
                                value: _vm.tab === "categories",
                                expression: "tab === 'categories'"
                              }
                            ],
                            staticClass: "ml-2",
                            scopedSlots: _vm._u([
                              {
                                key: "separator",
                                fn: function() {
                                  return [
                                    _c("q-icon", {
                                      attrs: {
                                        size: "1.5em",
                                        name: "mdi-chevron-right"
                                      }
                                    })
                                  ]
                                },
                                proxy: true
                              }
                            ])
                          },
                          [
                            _vm._v(" "),
                            _c("q-breadcrumbs-el", {
                              class: [
                                _vm.activeCategory === ""
                                  ? "text-black"
                                  : "cursor-pointer"
                              ],
                              attrs: {
                                icon: _vm.tree.topCategory.icon,
                                label: _vm.translate(_vm.tree.topCategory.label)
                              },
                              on: {
                                click: function($event) {
                                  _vm.activeCategory === "" ? "" : _vm.fetchData();
                                }
                              }
                            }),
                            _vm._v(" "),
                            _vm.activeCategory !== ""
                              ? _vm._l(
                                  _vm.tree.categories[_vm.activeCategory]
                                    .parentTree,
                                  function(category, index) {
                                    return _c(
                                      "q-breadcrumbs-el",
                                      {
                                        key: index,
                                        class: [
                                          index ===
                                          _vm.tree.categories[_vm.activeCategory]
                                            .parentTree.length -
                                            1
                                            ? "text-black"
                                            : "cursor-pointer"
                                        ],
                                        on: {
                                          click: function($event) {
                                            index ===
                                            _vm.tree.categories[
                                              _vm.activeCategory
                                            ].parentTree.length -
                                              1
                                              ? ""
                                              : _vm.fetchData(category);
                                          }
                                        }
                                      },
                                      [
                                        _vm.tree.categories[category].icon
                                          ? _c("icon", {
                                              staticClass: "q-mr-sm",
                                              attrs: {
                                                size: _vm.iconSize,
                                                icon:
                                                  _vm.tree.categories[category]
                                                    .icon
                                              }
                                            })
                                          : _vm._e(),
                                        _vm._v(
                                          "\n                " +
                                            _vm._s(
                                              _vm.tree.categories[category].label
                                            ) +
                                            "\n              "
                                        )
                                      ],
                                      1
                                    )
                                  }
                                )
                              : _vm._e()
                          ],
                          2
                        ),
                        _vm._v(" "),
                        _c(
                          "q-breadcrumbs",
                          {
                            directives: [
                              {
                                name: "show",
                                rawName: "v-show",
                                value: _vm.tab === "accounts",
                                expression: "tab === 'accounts'"
                              }
                            ],
                            staticClass: "ml-2"
                          },
                          [
                            _vm.activeAccount !== ""
                              ? _c(
                                  "q-breadcrumbs-el",
                                  { staticClass: "text-black" },
                                  [
                                    _c("icon", {
                                      staticClass: "q-mr-sm",
                                      attrs: {
                                        size: _vm.iconSize,
                                        icon: "userIcon-Accounts"
                                      }
                                    }),
                                    _vm._v(
                                      "\n              " +
                                        _vm._s(_vm.activeAccount) +
                                        "\n            "
                                    )
                                  ],
                                  1
                                )
                              : _vm._e()
                          ],
                          1
                        )
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _c(
                      "div",
                      {
                        staticClass:
                          "tree-search flex flex-grow-1 no-wrap order-sm-none order-xs-last"
                      },
                      [
                        _c("q-input", {
                          staticClass: "full-width",
                          attrs: {
                            placeholder: _vm.translate(
                              "JS_KB_SEARCH_PLACEHOLDER"
                            ),
                            rounded: "",
                            outlined: "",
                            type: "search",
                            autofocus: ""
                          },
                          on: { input: _vm.search },
                          scopedSlots: _vm._u([
                            {
                              key: "prepend",
                              fn: function() {
                                return [
                                  _c("q-icon", {
                                    attrs: { name: "mdi-magnify" }
                                  }),
                                  _vm._v(" "),
                                  _c(
                                    "q-tooltip",
                                    {
                                      attrs: {
                                        anchor: "top middle",
                                        self: "center middle"
                                      },
                                      model: {
                                        value: _vm.inputFocus,
                                        callback: function($$v) {
                                          _vm.inputFocus = $$v;
                                        },
                                        expression: "inputFocus"
                                      }
                                    },
                                    [
                                      _vm._v(
                                        _vm._s(
                                          _vm
                                            .translate("JS_INPUT_TOO_SHORT")
                                            .replace("_LENGTH_", "3")
                                        )
                                      )
                                    ]
                                  )
                                ]
                              },
                              proxy: true
                            },
                            {
                              key: "append",
                              fn: function() {
                                return [
                                  _vm.filter !== ""
                                    ? _c("q-icon", {
                                        staticClass: "cursor-pointer",
                                        attrs: { name: "mdi-close" },
                                        on: {
                                          click: function($event) {
                                            $event.stopPropagation();
                                            return _vm.clearSearch()
                                          }
                                        }
                                      })
                                    : _vm._e(),
                                  _vm._v(" "),
                                  _c(
                                    "div",
                                    { staticClass: "flex items-center q-ml-sm" },
                                    [
                                      _c(
                                        "icon-info",
                                        {
                                          attrs: {
                                            customOptions: { iconSize: "21px" }
                                          }
                                        },
                                        [
                                          _c("div", {
                                            staticStyle: {
                                              "white-space": "pre-line"
                                            },
                                            domProps: {
                                              innerHTML: _vm._s(
                                                _vm.translate(
                                                  "JS_FULL_TEXT_SEARCH_INFO"
                                                )
                                              )
                                            }
                                          })
                                        ]
                                      )
                                    ],
                                    1
                                  ),
                                  _vm._v(" "),
                                  _c(
                                    "div",
                                    {
                                      directives: [
                                        {
                                          name: "show",
                                          rawName: "v-show",
                                          value: _vm.activeCategory !== "",
                                          expression: "activeCategory !== ''"
                                        }
                                      ],
                                      staticClass: "flex"
                                    },
                                    [
                                      _c("q-toggle", {
                                        attrs: { icon: "mdi-file-tree" },
                                        model: {
                                          value: _vm.categorySearch,
                                          callback: function($$v) {
                                            _vm.categorySearch = $$v;
                                          },
                                          expression: "categorySearch"
                                        }
                                      }),
                                      _vm._v(" "),
                                      _c("q-tooltip", [
                                        _vm._v(
                                          _vm._s(
                                            _vm.translate(
                                              "JS_KB_SEARCH_CURRENT_CATEGORY"
                                            )
                                          )
                                        )
                                      ])
                                    ],
                                    1
                                  )
                                ]
                              },
                              proxy: true
                            }
                          ]),
                          model: {
                            value: _vm.filter,
                            callback: function($$v) {
                              _vm.filter = $$v;
                            },
                            expression: "filter"
                          }
                        })
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _c(
                      "div",
                      {
                        staticClass: "flex-md-grow-1 flex justify-end q-ml-sm-sm"
                      },
                      [
                        _c(
                          "q-btn",
                          {
                            attrs: {
                              round: "",
                              dense: "",
                              color: "white",
                              "text-color": "primary",
                              icon: "mdi-plus"
                            },
                            on: {
                              click: function($event) {
                                return _vm.openQuickCreateModal()
                              }
                            }
                          },
                          [
                            _c("q-tooltip", [
                              _vm._v(_vm._s(_vm.translate("JS_KB_QUICK_CREATE")))
                            ])
                          ],
                          1
                        )
                      ],
                      1
                    )
                  ]
                )
              ],
              1
            ),
            _vm._v(" "),
            _c(
              "q-drawer",
              {
                directives: [
                  {
                    name: "show",
                    rawName: "v-show",
                    value: !_vm.searchData,
                    expression: "!searchData"
                  }
                ],
                ref: "drawer",
                attrs: {
                  side: "left",
                  elevated: "",
                  mini: _vm.$q.platform.is.desktop ? _vm.miniState : false,
                  width: _vm.searchData ? 0 : 250,
                  breakpoint: 700,
                  "content-class": "bg-white text-black",
                  "content-style": "overflow: hidden !important"
                },
                model: {
                  value: _vm.left,
                  callback: function($$v) {
                    _vm.left = $$v;
                  },
                  expression: "left"
                }
              },
              [
                _vm.showAccounts
                  ? [
                      _c(
                        "q-tabs",
                        {
                          staticClass: "text-grey",
                          attrs: {
                            dense: "",
                            "active-color": "primary",
                            "indicator-color": "primary",
                            align: "justify",
                            "narrow-indicator": ""
                          },
                          on: { input: _vm.onTabChange },
                          model: {
                            value: _vm.tab,
                            callback: function($$v) {
                              _vm.tab = $$v;
                            },
                            expression: "tab"
                          }
                        },
                        [
                          _c("q-tab", {
                            attrs: {
                              name: "categories",
                              label: _vm.translate("JS_KB_CATEGORIES")
                            }
                          }),
                          _vm._v(" "),
                          _c("q-tab", {
                            attrs: {
                              name: "accounts",
                              label: _vm.translate("JS_KB_ACCOUNTS")
                            }
                          })
                        ],
                        1
                      ),
                      _vm._v(" "),
                      _c(
                        "q-tab-panels",
                        {
                          staticStyle: { height: "calc(100% - 36px)" },
                          attrs: { animated: "" },
                          model: {
                            value: _vm.tab,
                            callback: function($$v) {
                              _vm.tab = $$v;
                            },
                            expression: "tab"
                          }
                        },
                        [
                          _c(
                            "q-tab-panel",
                            { attrs: { name: "categories" } },
                            [
                              _c(
                                "q-scroll-area",
                                { staticClass: "fit" },
                                [
                                  _c("categories-list", {
                                    attrs: {
                                      data: _vm.data,
                                      activeCategory: _vm.activeCategory
                                    },
                                    on: { fetchData: _vm.fetchData }
                                  })
                                ],
                                1
                              )
                            ],
                            1
                          ),
                          _vm._v(" "),
                          _c(
                            "q-tab-panel",
                            { attrs: { name: "accounts" } },
                            [
                              _c(
                                "div",
                                { staticClass: "q-px-sm" },
                                [
                                  _c("q-input", {
                                    attrs: {
                                      placeholder: _vm.translate(
                                        "JS_KB_SEARCH_PLACEHOLDER"
                                      ),
                                      dense: ""
                                    },
                                    scopedSlots: _vm._u(
                                      [
                                        {
                                          key: "prepend",
                                          fn: function() {
                                            return [
                                              _c("q-icon", {
                                                attrs: {
                                                  name: "mdi-magnify",
                                                  size: "16px"
                                                }
                                              })
                                            ]
                                          },
                                          proxy: true
                                        },
                                        {
                                          key: "append",
                                          fn: function() {
                                            return [
                                              _c("q-icon", {
                                                directives: [
                                                  {
                                                    name: "show",
                                                    rawName: "v-show",
                                                    value:
                                                      _vm.accountSearch !== "",
                                                    expression:
                                                      "accountSearch !== ''"
                                                  }
                                                ],
                                                staticClass: "cursor-pointer",
                                                attrs: {
                                                  name: "mdi-close",
                                                  size: "16px"
                                                },
                                                on: {
                                                  click: function($event) {
                                                    _vm.accountSearch = "";
                                                  }
                                                }
                                              })
                                            ]
                                          },
                                          proxy: true
                                        }
                                      ],
                                      null,
                                      false,
                                      107416869
                                    ),
                                    model: {
                                      value: _vm.accountSearch,
                                      callback: function($$v) {
                                        _vm.accountSearch = $$v;
                                      },
                                      expression: "accountSearch"
                                    }
                                  })
                                ],
                                1
                              ),
                              _vm._v(" "),
                              _c(
                                "q-scroll-area",
                                { staticStyle: { height: "calc(100% - 56px)" } },
                                [
                                  _c(
                                    "q-list",
                                    _vm._l(_vm.accountsList, function(account) {
                                      return _c(
                                        "q-item",
                                        {
                                          key: account,
                                          attrs: {
                                            active:
                                              _vm.activeAccount === account.name,
                                            clickable: ""
                                          },
                                          on: {
                                            click: function($event) {
                                              _vm.fetchData(null, account.id);
                                              _vm.activeAccount = account.name;
                                            }
                                          }
                                        },
                                        [
                                          _c("q-item-section", [
                                            _vm._v(_vm._s(account.name))
                                          ]),
                                          _vm._v(" "),
                                          _c(
                                            "q-item-section",
                                            { attrs: { avatar: "" } },
                                            [
                                              _c(
                                                "a",
                                                {
                                                  staticClass:
                                                    "js-popover-tooltip--record ellipsis",
                                                  attrs: {
                                                    href:
                                                      "index.php?module=Accounts&view=Detail&record=" +
                                                      account.id
                                                  },
                                                  on: {
                                                    click: function($event) {
                                                      $event.preventDefault();
                                                    }
                                                  }
                                                },
                                                [
                                                  _c("q-icon", {
                                                    attrs: { name: "mdi-link" }
                                                  })
                                                ],
                                                1
                                              )
                                            ]
                                          )
                                        ],
                                        1
                                      )
                                    }),
                                    1
                                  )
                                ],
                                1
                              )
                            ],
                            1
                          )
                        ],
                        1
                      )
                    ]
                  : _c(
                      "q-scroll-area",
                      { staticClass: "fit" },
                      [
                        _c("categories-list", {
                          attrs: {
                            data: _vm.data,
                            activeCategory: _vm.activeCategory
                          },
                          on: { fetchData: _vm.fetchData }
                        })
                      ],
                      1
                    )
              ],
              2
            ),
            _vm._v(" "),
            _c(
              "q-page-container",
              [
                _c(
                  "q-page",
                  { staticClass: "q-pa-sm" },
                  [
                    _c(
                      "div",
                      {
                        directives: [
                          {
                            name: "show",
                            rawName: "v-show",
                            value: !_vm.searchData,
                            expression: "!searchData"
                          }
                        ]
                      },
                      [
                        _c("columns-grid", {
                          directives: [
                            {
                              name: "show",
                              rawName: "v-show",
                              value: _vm.featuredCategories.length,
                              expression: "featuredCategories.length"
                            }
                          ],
                          staticClass: "q-pa-sm",
                          attrs: { columnBlocks: _vm.featuredCategories },
                          scopedSlots: _vm._u([
                            {
                              key: "default",
                              fn: function(slotProps) {
                                return [
                                  _c(
                                    "q-list",
                                    {
                                      attrs: {
                                        bordered: "",
                                        padding: "",
                                        dense: ""
                                      }
                                    },
                                    [
                                      _c(
                                        "q-item",
                                        {
                                          staticClass: "text-black flex",
                                          attrs: { header: "", clickable: "" },
                                          on: {
                                            click: function($event) {
                                              return _vm.fetchData(
                                                slotProps.relatedBlock
                                              )
                                            }
                                          }
                                        },
                                        [
                                          _c("icon", {
                                            staticClass: "mr-2",
                                            attrs: {
                                              icon:
                                                _vm.tree.categories[
                                                  slotProps.relatedBlock
                                                ].icon,
                                              size: _vm.iconSize
                                            }
                                          }),
                                          _vm._v(
                                            "\n                  " +
                                              _vm._s(
                                                _vm.tree.categories[
                                                  slotProps.relatedBlock
                                                ].label
                                              ) +
                                              "\n                "
                                          )
                                        ],
                                        1
                                      ),
                                      _vm._v(" "),
                                      _vm._l(
                                        _vm.selectedTabData.featured[
                                          slotProps.relatedBlock
                                        ],
                                        function(featuredValue) {
                                          return _c(
                                            "q-item",
                                            {
                                              directives: [
                                                {
                                                  name: "ripple",
                                                  rawName: "v-ripple"
                                                }
                                              ],
                                              key: featuredValue.id,
                                              staticClass: "text-subtitle2",
                                              attrs: { clickable: "" },
                                              on: {
                                                click: function($event) {
                                                  $event.preventDefault();
                                                  return _vm.showArticlePreview(
                                                    featuredValue.id
                                                  )
                                                }
                                              }
                                            },
                                            [
                                              _c(
                                                "q-item-section",
                                                {
                                                  staticClass:
                                                    "align-items-center flex-row no-wrap justify-content-start"
                                                },
                                                [
                                                  _c("q-icon", {
                                                    staticClass: "mr-2",
                                                    attrs: {
                                                      name: "mdi-star",
                                                      size: _vm.iconSize
                                                    }
                                                  }),
                                                  _vm._v(" "),
                                                  _c(
                                                    "a",
                                                    {
                                                      staticClass:
                                                        "js-popover-tooltip--record ellipsis",
                                                      attrs: {
                                                        href:
                                                          "index.php?module=" +
                                                          _vm.moduleName +
                                                          "&view=Detail&record=" +
                                                          featuredValue.id
                                                      }
                                                    },
                                                    [
                                                      _vm._v(
                                                        _vm._s(
                                                          featuredValue.subject
                                                        )
                                                      )
                                                    ]
                                                  )
                                                ],
                                                1
                                              )
                                            ],
                                            1
                                          )
                                        }
                                      )
                                    ],
                                    2
                                  )
                                ]
                              }
                            }
                          ])
                        }),
                        _vm._v(" "),
                        _c(
                          "div",
                          {
                            directives: [
                              {
                                name: "show",
                                rawName: "v-show",
                                value:
                                  _vm.activeCategory !== "" ||
                                  _vm.tab === "accounts",
                                expression:
                                  "activeCategory !== '' || tab === 'accounts'"
                              }
                            ]
                          },
                          [
                            _c("q-separator", {
                              directives: [
                                {
                                  name: "show",
                                  rawName: "v-show",
                                  value: _vm.featuredCategories.length,
                                  expression: "featuredCategories.length"
                                }
                              ]
                            }),
                            _vm._v(" "),
                            _c("articles-list", {
                              attrs: {
                                data: _vm.selectedTabData.records,
                                title: _vm.translate("JS_KB_ARTICLES")
                              },
                              on: {
                                onClickRecord: function($event) {
                                  _vm.previewDialog = true;
                                }
                              }
                            })
                          ],
                          1
                        )
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _c("articles-list", {
                      directives: [
                        {
                          name: "show",
                          rawName: "v-show",
                          value: _vm.searchData,
                          expression: "searchData"
                        }
                      ],
                      attrs: {
                        data: _vm.searchDataArray,
                        title: _vm.translate("JS_KB_ARTICLES")
                      },
                      on: {
                        onClickRecord: function($event) {
                          _vm.previewDialog = true;
                        }
                      }
                    })
                  ],
                  1
                )
              ],
              1
            )
          ],
          1
        ),
        _vm._v(" "),
        _c("article-preview", {
          attrs: { isDragResize: true, previewDialog: _vm.previewDialog },
          on: { onDialogToggle: _vm.onDialogToggle }
        })
      ],
      1
    )
  };
  var __vue_staticRenderFns__$9 = [];
  __vue_render__$9._withStripped = true;

    /* style */
    const __vue_inject_styles__$9 = function (inject) {
      if (!inject) return
      inject("data-v-6a73befc_0", { source: "\n.tree-search {\r\n  min-width: 320px;\r\n  width: 50%;\n}\n.tree-search .q-field__control,\r\n.tree-search .q-field__marginal {\r\n  height: 40px;\n}\r\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$9 = undefined;
    /* module identifier */
    const __vue_module_identifier__$9 = undefined;
    /* functional template */
    const __vue_is_functional_template__$9 = false;
    /* style inject SSR */
    

    
    var KnowledgeBaseComponent = normalizeComponent_1(
      { render: __vue_render__$9, staticRenderFns: __vue_staticRenderFns__$9 },
      __vue_inject_styles__$9,
      __vue_script__$9,
      __vue_scope_id__$9,
      __vue_is_functional_template__$9,
      __vue_module_identifier__$9,
      browser,
      undefined
    );

  //
  const {
    mapGetters: mapGetters$7,
    mapActions: mapActions$4
  } = createNamespacedHelpers('KnowledgeBase');
  var script$a = {
    name: 'KnowledgeBaseModal',
    components: {
      KnowledgeBase: KnowledgeBaseComponent,
      DragResize
    },

    data() {
      return {
        coordinates: {
          width: Quasar.plugins.Screen.width - 100,
          height: Quasar.plugins.Screen.height - 100,
          top: 0,
          left: Quasar.plugins.Screen.width - (Quasar.plugins.Screen.width - 100 / 2)
        }
      };
    },

    computed: { ...mapGetters$7(['maximized', 'moduleName']),
      dialog: {
        set(val) {
          this.$store.commit('KnowledgeBase/setDialog', val);
        },

        get() {
          return this.$store.getters['KnowledgeBase/dialog'];
        }

      },
      maximized: {
        set(val) {
          this.$store.commit('KnowledgeBase/setMaximized', val);
        },

        get() {
          return this.$store.getters['KnowledgeBase/maximized'];
        }

      }
    },
    methods: { ...mapActions$4(['fetchCategories', 'initState']),
      onChangeCoordinates: function (coordinates) {
        this.coordinates = coordinates;
      }
    },

    async created() {
      await this.initState(this.$options.state);
    }

  };

  /* script */
  const __vue_script__$a = script$a;

  /* template */
  var __vue_render__$a = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "q-dialog",
      {
        attrs: {
          maximized: _vm.maximized,
          "transition-show": "slide-up",
          "transition-hide": "slide-down",
          "content-class": "quasar-reset"
        },
        model: {
          value: _vm.dialog,
          callback: function($$v) {
            _vm.dialog = $$v;
          },
          expression: "dialog"
        }
      },
      [
        _c(
          "drag-resize",
          {
            attrs: { coordinates: _vm.coordinates, maximized: _vm.maximized },
            on: { onChangeCoordinates: _vm.onChangeCoordinates }
          },
          [
            _c(
              "q-card",
              { staticClass: "KnowledgeBaseModal full-height" },
              [
                _c(
                  "q-bar",
                  {
                    staticClass: "bg-yeti text-white dialog-header",
                    attrs: { dark: "" }
                  },
                  [
                    _c("div", { staticClass: "flex items-center" }, [
                      _c(
                        "div",
                        {
                          staticClass:
                            "flex items-center no-wrap ellipsis q-mr-sm-sm"
                        },
                        [
                          _c("span", {
                            class: ["userIcon-" + _vm.moduleName, "q-mr-sm"]
                          }),
                          _vm._v(
                            "\n            " +
                              _vm._s(
                                _vm.translate(
                                  "JS_" + _vm.moduleName.toUpperCase()
                                )
                              ) +
                              "\n          "
                          )
                        ]
                      )
                    ]),
                    _vm._v(" "),
                    _c("q-space"),
                    _vm._v(" "),
                    _vm.$q.platform.is.desktop
                      ? [
                          _c(
                            "a",
                            {
                              directives: [
                                {
                                  name: "show",
                                  rawName: "v-show",
                                  value: !_vm.maximized,
                                  expression: "!maximized"
                                }
                              ],
                              staticClass:
                                "flex grabbable text-decoration-none text-white",
                              attrs: { href: "#" }
                            },
                            [
                              _c("q-icon", {
                                staticClass: "js-drag",
                                attrs: { name: "mdi-drag", size: "19px" }
                              })
                            ],
                            1
                          ),
                          _vm._v(" "),
                          _c(
                            "q-btn",
                            {
                              attrs: {
                                dense: "",
                                flat: "",
                                icon: _vm.maximized
                                  ? "mdi-window-restore"
                                  : "mdi-window-maximize"
                              },
                              on: {
                                click: function($event) {
                                  _vm.maximized = !_vm.maximized;
                                }
                              }
                            },
                            [
                              _c("q-tooltip", [
                                _vm._v(
                                  _vm._s(
                                    _vm.maximized
                                      ? _vm.translate("JS_MINIMIZE")
                                      : _vm.translate("JS_MAXIMIZE")
                                  )
                                )
                              ])
                            ],
                            1
                          )
                        ]
                      : _vm._e(),
                    _vm._v(" "),
                    _c(
                      "q-btn",
                      {
                        directives: [
                          { name: "close-popup", rawName: "v-close-popup" }
                        ],
                        attrs: { dense: "", flat: "", icon: "mdi-close" }
                      },
                      [
                        _c("q-tooltip", [
                          _vm._v(_vm._s(_vm.translate("JS_CLOSE")))
                        ])
                      ],
                      1
                    )
                  ],
                  2
                ),
                _vm._v(" "),
                _c(
                  "div",
                  [
                    _c("knowledge-base", {
                      attrs: { coordinates: _vm.coordinates }
                    })
                  ],
                  1
                )
              ],
              1
            )
          ],
          1
        )
      ],
      1
    )
  };
  var __vue_staticRenderFns__$a = [];
  __vue_render__$a._withStripped = true;

    /* style */
    const __vue_inject_styles__$a = function (inject) {
      if (!inject) return
      inject("data-v-185223bc_0", { source: "\n.dialog-header {\r\n  padding-top: 3px !important;\r\n  padding-bottom: 3px !important;\r\n  height: unset !important;\n}\n.modal-full-height {\r\n  max-height: calc(100vh - 31.14px) !important;\n}\n.grabbable:hover {\r\n  cursor: move;\r\n  cursor: grab;\r\n  cursor: -moz-grab;\r\n  cursor: -webkit-grab;\n}\n.grabbable:active {\r\n  cursor: grabbing;\r\n  cursor: -moz-grabbing;\r\n  cursor: -webkit-grabbing;\n}\n.contrast-50 {\r\n  filter: contrast(50%);\n}\r\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$a = undefined;
    /* module identifier */
    const __vue_module_identifier__$a = undefined;
    /* functional template */
    const __vue_is_functional_template__$a = false;
    /* style inject SSR */
    

    
    var KnowledgeBaseModal = normalizeComponent_1(
      { render: __vue_render__$a, staticRenderFns: __vue_staticRenderFns__$a },
      __vue_inject_styles__$a,
      __vue_script__$a,
      __vue_scope_id__$a,
      __vue_is_functional_template__$a,
      __vue_module_identifier__$a,
      browser,
      undefined
    );

  //
  const {
    mapGetters: mapGetters$8,
    mapActions: mapActions$5
  } = createNamespacedHelpers('KnowledgeBase');
  var script$b = {
    name: 'ArticlePreviewModal',
    components: {
      ArticlePreview
    },
    methods: {
      hideModal() {
        app.hideModalWindow();
        this.initState({
          record: false
        });
        this.$destroy();
      },

      ...mapActions$5(['fetchCategories', 'fetchRecord', 'initState'])
    },

    async created() {
      await this.initState(this.$options.state);
      await this.fetchCategories();
      await this.fetchRecord(this.$options.state.recordId);
      document.addEventListener('keyup', evt => {
        if (evt.keyCode === 27) {
          this.hideModal();
        }
      });
    }

  };

  /* script */
  const __vue_script__$b = script$b;

  /* template */
  var __vue_render__$b = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c(
      "article-preview",
      {
        attrs: { isDragResize: false, maximizedOnly: true, previewDialog: true }
      },
      [
        _c(
          "template",
          { slot: "header-right" },
          [
            _c(
              "q-btn",
              {
                attrs: { dense: "", flat: "", icon: "mdi-close" },
                on: {
                  click: function($event) {
                    return _vm.hideModal()
                  }
                }
              },
              [_c("q-tooltip", [_vm._v(_vm._s(_vm.translate("JS_CLOSE")))])],
              1
            )
          ],
          1
        )
      ],
      2
    )
  };
  var __vue_staticRenderFns__$b = [];
  __vue_render__$b._withStripped = true;

    /* style */
    const __vue_inject_styles__$b = function (inject) {
      if (!inject) return
      inject("data-v-0bca4218_0", { source: "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n", map: undefined, media: undefined });

    };
    /* scoped */
    const __vue_scope_id__$b = undefined;
    /* module identifier */
    const __vue_module_identifier__$b = undefined;
    /* functional template */
    const __vue_is_functional_template__$b = false;
    /* style inject SSR */
    

    
    var ArticlePreviewComponent = normalizeComponent_1(
      { render: __vue_render__$b, staticRenderFns: __vue_staticRenderFns__$b },
      __vue_inject_styles__$b,
      __vue_script__$b,
      __vue_scope_id__$b,
      __vue_is_functional_template__$b,
      __vue_module_identifier__$b,
      browser,
      undefined
    );

  Vue.use(index_esm);
  const debug = process.env.NODE_ENV !== 'production';

  if (window.vuexStore === undefined) {
    window.vuexStore = new index_esm.Store({
      strict: debug
    });
  }

  var store = window.vuexStore;

  /**
   * Knowledge base module
   *
   * @description Knowledge base vuex module
   * @license YetiForce Public License 3.0
   * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
   */
  const state = {
    defaultTreeIcon: 'mdi-subdirectory-arrow-right',
    record: false,
    dialog: false,
    maximized: true,
    moduleName: '',
    iconSize: '18px',
    tree: {
      topCategory: {
        icon: 'mdi-file-tree',
        label: 'JS_KB_MAIN_CATEGORIES'
      },
      categories: {}
    } // getters

  };
  const getters = {
    moduleName(state) {
      return state.moduleName;
    },

    record(state) {
      return state.record;
    },

    dialog(state) {
      return state.dialog;
    },

    maximized(state) {
      return state.maximized;
    },

    previewDialog(state) {
      return state.previewDialog;
    },

    previewMaximized(state) {
      return state.previewMaximized;
    },

    coordinates(state) {
      return state.coordinates;
    },

    iconSize(state) {
      return state.iconSize;
    },

    tree(state) {
      return state.tree;
    },

    defaultTreeIcon(state) {
      return state.defaultTreeIcon;
    }

  }; // actions

  const actions = {
    fetchRecord({
      state,
      commit,
      getters
    }, id) {
      const aDeferred = $.Deferred();
      const progressIndicatorElement = $.progressIndicator({
        blockInfo: {
          enabled: true
        }
      });
      return AppConnector.request({
        module: getters.moduleName,
        action: 'KnowledgeBaseAjax',
        mode: 'detail',
        record: id
      }).done(data => {
        let recordData = data.result;

        if (recordData.related.base.Articles) {
          recordData.related.base.Articles = Object.keys(recordData.related.base.Articles).map(function (key) {
            return { ...recordData.related.base.Articles[key],
              id: key
            };
          });
        }

        commit('setRecord', recordData);
        progressIndicatorElement.progressIndicator({
          mode: 'hide'
        });
        aDeferred.resolve(recordData);
      });
    },

    fetchCategories({
      state,
      commit,
      getters
    }) {
      const aDeferred = $.Deferred();
      return AppConnector.request({
        module: getters.moduleName,
        action: 'KnowledgeBaseAjax',
        mode: 'categories'
      }).done(data => {
        commit('setTreeCategories', data.result);
        aDeferred.resolve(data.result);
      });
    },

    initState({
      state,
      commit
    }, data) {
      commit('setState', data);
    }

  }; // mutations

  const mutations = {
    setState(state, payload) {
      state = Object.assign(state, payload);
    },

    setRecord(state, payload) {
      state.record = payload;
    },

    setDialog(state, payload) {
      state.dialog = payload;
    },

    setMaximized(state, payload) {
      state.maximized = payload;
    },

    setCoordinates(state, payload) {
      state.coordinates = payload;
    },

    setTreeCategories(state, payload) {
      state.tree.categories = payload;
    }

  };
  var moduleStore = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
  };

  /**
   * KnowledgeBase components initializations
   *
   * @description KnowledgeBase views' instances
   * @license YetiForce Public License 3.0
   * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
   */
  const {
    mapActions: mapActions$6
  } = createNamespacedHelpers('KnowledgeBase');
  store.registerModule('KnowledgeBase', moduleStore);
  Vue.mixin({
    methods: {
      translate(key) {
        return app.vtranslate(key);
      }

    }
  });
  window.KnowledgeBase = {
    component: KnowledgeBaseComponent,

    mount(config) {
      KnowledgeBaseComponent.state = config.state;
      return new Vue({
        store,
        render: h => h(KnowledgeBaseComponent),
        methods: { ...mapActions$6(['fetchCategories', 'initState'])
        },

        async created() {
          await this.initState(config.state);
        }

      }).$mount(config.el);
    }

  };
  window.ArticlePreviewVueComponent = {
    component: ArticlePreviewComponent,

    mount(config) {
      ArticlePreviewComponent.state = config.state;
      return new Vue({
        store,
        render: h => h(ArticlePreviewComponent)
      }).$mount(config.el);
    }

  };
  window.KnowledgeBaseModalVueComponent = {
    component: KnowledgeBaseModal,

    mount(config) {
      KnowledgeBaseModal.state = config.state;
      return new Vue({
        store,
        render: h => h(KnowledgeBaseModal)
      }).$mount(config.el);
    }

  };

}());
//# sourceMappingURL=KnowledgeBase.vue.js.map
