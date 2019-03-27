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

import YfBreadcrumbs from './YfBreadcrumbs.js';
import YfCopyright from './YfCopyright.js';

var __script__ = {
  name: 'YfFooter',
  components: { YfBreadcrumbs: YfBreadcrumbs, YfCopyright: YfCopyright },
  data: function data() {
    return {
      searchModule: 'All records',
      searchModules: ['All records', 'acc', 'con'],
      searchText: '',
      iconSize: '.75rem'
    };
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('hook-wrapper', {
    staticClass: "Core-Component-YfFooter"
  }, [_c('q-footer', {
    staticClass: "bg-blue-grey-10 text-blue-grey-11 row no-wrap",
    attrs: {
      "elevated": ""
    }
  }, [_c('q-toolbar', [_c('div', {
    staticClass: "flex wrap full-width items-center justify-between"
  }, [_c('yf-breadcrumbs'), _vm._v(" "), _c('yf-copyright')], 1)])], 1)], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);