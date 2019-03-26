//
//

import mutations from '/src/store/mutations.js';

var moduleName = 'Base.Home';

export function initialize(_ref) {
  var store = _ref.store,
      router = _ref.router;

  store.commit(mutations.Core.Menu.addItem, {
    path: '/base/home',
    icon: 'mdi-home',
    label: 'Home',
    children: []
  });
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