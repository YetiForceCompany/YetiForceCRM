//
//

import moduleStore from './store/index.js';

var moduleName = 'Core';

export function initialize(_ref) {
  var store = _ref.store;

  store.registerModule(moduleName, ModuleLoader.prepareStoreNames(moduleName, moduleStore));
}

var __script__ = {
  name: moduleName,
  data: function data() {
    return {
      config: {
        debug: window.env.Debug.levels.map(function (level) {
          return level;
        })
      }
    };
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c("div");
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);