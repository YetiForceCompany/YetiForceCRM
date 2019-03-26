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

import actions from '/src/store/actions.js';
var __script__ = {
  name: 'YfGlobalActions',
  data: function data() {
    return {
      iconSize: '.75rem'
    };
  },

  methods: Object.assign({}, Vuex.mapActions({
    logout: actions.Core.Users.logout
  }))
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', [_c('q-btn', {
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-plus"
    }
  }), _vm._v(" "), _c('q-btn', {
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-email-outline"
    }
  }), _vm._v(" "), _c('q-btn', {
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-bell-ring-outline"
    }
  }), _vm._v(" "), _c('q-btn', {
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-calendar-multiselect"
    }
  }), _vm._v(" "), _c('q-btn', {
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-history"
    }
  }), _vm._v(" "), _c('q-btn', {
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-settings-outline"
    }
  }), _vm._v(" "), _c('q-btn', {
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-power-standby"
    },
    on: {
      "click": _vm.logout
    }
  })], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);