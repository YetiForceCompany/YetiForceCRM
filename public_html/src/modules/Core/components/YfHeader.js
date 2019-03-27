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

import YfGlobalSearch from './YfGlobalSearch.js';
import YfGlobalActions from './YfGlobalActions.js';
var __script__ = {
  name: 'YfHeader',
  components: { YfGlobalSearch: YfGlobalSearch, YfGlobalActions: YfGlobalActions },
  data: function data() {
    return {
      iconSize: '.75rem'
    };
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('q-header', {
    staticClass: "bg-white text-muted",
    attrs: {
      "elevated": ""
    }
  }, [_c('q-toolbar', [_vm._t("left"), _vm._v(" "), _c('yf-global-search'), _vm._v(" "), _c('yf-global-actions', {
    staticClass: "q-ml-auto"
  })], 2)], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);