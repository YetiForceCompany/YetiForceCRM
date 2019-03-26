//
//
//
//
//
//

import HookWrapper from '/src/modules/Core/modules/Hooks/components/HookWrapper.js';

var moduleName = 'Settings.ModuleExample.Pages.ModuleExample';
var __script__ = {
  name: moduleName,
  components: { HookWrapper: HookWrapper }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('hook-wrapper', [_vm._v("\n  Layout -> Settings -> ModuleExample page\n")]);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);