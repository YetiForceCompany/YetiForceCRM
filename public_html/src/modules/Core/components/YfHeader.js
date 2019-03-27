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

import YfGlobalSearch from './YfGlobalSearch.js';
import YfGlobalActions from './YfGlobalActions.js';
import getters from '/src/store/getters.js';
var __script__ = {
  name: 'YfHeader',
  components: { YfGlobalSearch: YfGlobalSearch, YfGlobalActions: YfGlobalActions },
  data: function data() {
    return {
      iconSize: '.75rem'
    };
  },

  computed: Object.assign({}, Vuex.mapGetters({
    env: getters.Core.Env.all
  }))
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('hook-wrapper', {
    staticClass: "Core-Component-YfHeader"
  }, [_c('q-header', {
    staticClass: "bg-white text-muted",
    attrs: {
      "elevated": ""
    }
  }, [_c('q-toolbar', [_c('q-toolbar-title', {
    staticClass: "col-auto"
  }, [_c('q-avatar', [_c('img', {
    attrs: {
      "src": "https://cdn.quasar-framework.org/logo/svg/quasar-logo.svg"
    }
  })])], 1), _vm._v(" "), _c('yf-global-search'), _vm._v(" "), _c('yf-global-actions', {
    staticClass: "q-ml-auto",
    staticStyle: {
      "color": "rgba(0,0,0,.54)"
    }
  })], 1)], 1)], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);