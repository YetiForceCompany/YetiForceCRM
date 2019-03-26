//
//
//
//

import mutations from '/src/store/mutations.js';

var moduleName = 'Settings.ModuleExample';

export function initialize(_ref) {
  var store = _ref.store,
      router = _ref.router;

  store.commit(mutations.Core.Menu.addItem, {
    path: '',
    icon: 'mdi-settings',
    label: 'Settings',
    children: [{
      path: '/settings/module-example',
      icon: 'mdi-cube',
      label: 'Example',
      children: []
    }]
  });
  store.commit(mutations.Core.Hooks.add, [{
    hookName: 'Settings.ModuleExample.Pages.ModuleExample.before',
    component: {
      name: 'test-before',
      render: function render(createElement, context) {
        return createElement('div', null, ['Before hook works!']);
      }
    }
  }, {
    hookName: 'Settings.ModuleExample.Pages.ModuleExample.after',
    component: {
      name: 'test-after',
      render: function render(createElement, context) {
        return createElement('div', null, ['After hook works!']);
      }
    }
  }]);
}

var __script__ = {
  name: moduleName
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', {
    staticClass: "ModuleExample"
  });
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);