//
//

import ModuleLoader from '/src/ModuleLoader.js';
import moduleStore from './store/index.js';
import mutations from '/src/store/mutations.js';

var moduleName = 'Core.Language';

export function initialize(_ref) {
  var store = _ref.store,
      router = _ref.router;

  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore));
  store.commit(mutations.Core.Language.update, window.env.Language);
}

var __script__ = {
  name: moduleName
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c("div");
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);