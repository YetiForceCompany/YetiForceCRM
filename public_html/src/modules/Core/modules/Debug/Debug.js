/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

/* script */
import ModuleLoader from "/src/ModuleLoader.js";
import moduleStore from "./store/index.js";
import mutations from "./store/mutations.js";
import getters from "./store/getters.js";
var moduleName = 'Core.Debug';
export function initialize(_ref) {
  var store = _ref.store,
      router = _ref.router;
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore));
}
var __vue_script__ = {
  name: moduleName,
  props: {
    levels: {
      type: Array,
      validate: function validate(value) {
        var _iteratorNormalCompletion = true;
        var _didIteratorError = false;
        var _iteratorError = undefined;

        try {
          for (var _iterator = value[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
            var item = _step.value;

            if (['all', 'log', 'info', 'notice', 'warning', 'error'].indexOf(item) !== -1) {
              return false;
            }
          }
        } catch (err) {
          _didIteratorError = true;
          _iteratorError = err;
        } finally {
          try {
            if (!_iteratorNormalCompletion && _iterator.return != null) {
              _iterator.return();
            }
          } finally {
            if (_didIteratorError) {
              throw _iteratorError;
            }
          }
        }

        return true;
      }
    }
  },
  computed: {
    all: function all() {
      var moduleName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return this.$store.getters[getters.Core.Debug.get]('all', moduleName);
    },
    logs: function logs() {
      var moduleName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return this.$store.getters[getters.Core.Debug.get]('log', moduleName);
    },
    infos: function infos() {
      var moduleName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return this.$store.getters[getters.Core.Debug.get]('info', moduleName);
    },
    warnings: function warnings() {
      var moduleName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return this.$store.getters[getters.Core.Debug.get]('warning', moduleName);
    },
    notices: function notices() {
      var moduleName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return this.$store.getters[getters.Core.Debug.get]('notice', moduleName);
    },
    errors: function errors() {
      var moduleName = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      return this.$store.getters[getters.Core.Debug.get]('error', moduleName);
    }
  },
  methods: {
    log: function log(message, data) {
      this.$store.commit(mutations.Core.Debug.push, {
        type: 'log',
        message: message,
        data: data
      });
      this.$root.$emit('debug.log', {
        message: message,
        data: data
      });

      if (this.levels.indexOf('log') !== -1) {
        console.log(message, data);
      }
    },
    info: function info(message, data) {
      this.$store.commit(mutations.Core.Debug.push, {
        type: 'info',
        message: message,
        data: data
      });
      this.$root.$emit('debug.info', {
        message: message,
        data: data
      });

      if (this.levels.indexOf('info') !== -1) {
        console.info(message, data);
      }
    },
    notice: function notice(message, data) {
      this.$store.commit(mutations.Core.Debug.push, {
        type: 'notice',
        message: message,
        data: data
      });
      this.$root.$emit('debug.notice', {
        message: message,
        data: data
      });

      if (this.levels.indexOf('notice') !== -1) {
        console.log("%c ".concat(message), 'color: orange', data);
      }
    },
    warning: function warning(message, data) {
      this.$store.commit(mutations.Core.Debug.push, {
        type: 'warning',
        message: message,
        data: data
      });
      this.$root.$emit('debug.warning', {
        message: message,
        data: data
      });

      if (this.levels.indexOf('warning') !== -1) {
        console.warn(message, data);
      }
    },
    error: function error(message, data) {
      this.$store.commit(mutations.Core.Debug.push, {
        type: 'error',
        message: message,
        data: data
      });
      this.$root.$emit('debug.error', {
        message: message,
        data: data
      });

      if (this.levels.indexOf('error') !== -1) {
        console.error(message, data);
      }
    }
  }
  /* template */

};

var __vue_render__ = function __vue_render__() {
  var _vm = this;

  var _h = _vm.$createElement;

  var _c = _vm._self._c || _h;

  return _c("div");
};

var __vue_staticRenderFns__ = [];
/* style */

var __vue_inject_styles__ = undefined;
/* scoped */

var __vue_scope_id__ = undefined;
/* module identifier */

var __vue_module_identifier__ = undefined;
/* functional template */

var __vue_is_functional_template__ = false;
/* component normalizer */

function __vue_normalize__(template, style, script, scope, functional, moduleIdentifier, createInjector, createInjectorSSR) {
  var component = (typeof script === 'function' ? script.options : script) || {}; // For security concerns, we use only base name in production mode.

  component.__file = "Debug.vue";

  if (!component.render) {
    component.render = template.render;
    component.staticRenderFns = template.staticRenderFns;
    component._compiled = true;
    if (functional) component.functional = true;
  }

  component._scopeId = scope;

  if (false) {
    var hook;

    if (false) {
      // In SSR.
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


      component._ssrRegister = hook;
    } else if (style) {
      hook = function hook(context) {
        style.call(this, createInjector(context));
      };
    }

    if (hook !== undefined) {
      if (component.functional) {
        // register for functional component in vue file
        var originalRender = component.render;

        component.render = function renderWithStyleInjection(h, context) {
          hook.call(context);
          return originalRender(h, context);
        };
      } else {
        // inject component registration as beforeCreate hook
        var existing = component.beforeCreate;
        component.beforeCreate = existing ? [].concat(existing, hook) : [hook];
      }
    }
  }

  return component;
}
/* style inject */

/* style inject SSR */


export default __vue_normalize__({
  render: __vue_render__,
  staticRenderFns: __vue_staticRenderFns__
}, __vue_inject_styles__, __vue_script__, __vue_scope_id__, __vue_is_functional_template__, __vue_module_identifier__, undefined, undefined);