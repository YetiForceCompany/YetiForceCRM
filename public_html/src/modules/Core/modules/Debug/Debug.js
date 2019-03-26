//
//

import ModuleLoader from '/src/ModuleLoader.js';
import moduleStore from './store/index.js';
import mutations from './store/mutations.js';
import getters from './store/getters.js';

var moduleName = 'Core.Debug';

export function initialize(_ref) {
  var store = _ref.store,
      router = _ref.router;

  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore));
}

var __script__ = {
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
            if (!_iteratorNormalCompletion && _iterator.return) {
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
      this.$store.commit(mutations.Core.Debug.push, { type: 'log', message: message, data: data });
      this.$root.$emit('debug.log', { message: message, data: data });
      if (this.levels.indexOf('log') !== -1) {
        console.log(message, data);
      }
    },
    info: function info(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'info', message: message, data: data });
      this.$root.$emit('debug.info', { message: message, data: data });
      if (this.levels.indexOf('info') !== -1) {
        console.info(message, data);
      }
    },
    notice: function notice(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'notice', message: message, data: data });
      this.$root.$emit('debug.notice', { message: message, data: data });
      if (this.levels.indexOf('notice') !== -1) {
        console.log('%c ' + message, 'color: orange', data);
      }
    },
    warning: function warning(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'warning', message: message, data: data });
      this.$root.$emit('debug.warning', { message: message, data: data });
      if (this.levels.indexOf('warning') !== -1) {
        console.warn(message, data);
      }
    },
    error: function error(message, data) {
      this.$store.commit(mutations.Core.Debug.push, { type: 'error', message: message, data: data });
      this.$root.$emit('debug.error', { message: message, data: data });
      if (this.levels.indexOf('error') !== -1) {
        console.error(message, data);
      }
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c("div");
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);