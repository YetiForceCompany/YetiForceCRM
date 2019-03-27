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

import getters from '/src/store/getters.js';
import LeftMenu from '/src/modules/Core/modules/Menu/components/LeftMenu.js';
import YfHeader from '/src/modules/Core/components/YfHeader.js';
import YfFooter from '/src/modules/Core/components/YfFooter.js';

var __script__ = {
  name: 'Basic',
  components: {
    LeftMenu: LeftMenu,
    YfHeader: YfHeader,
    YfFooter: YfFooter
  },
  data: function data() {
    return {
      leftDrawerOpen: false,
      miniState: this.$q.platform.is.desktop,
      menuEvents: true
    };
  },

  computed: Object.assign({}, Vuex.mapGetters({
    isLoggedIn: getters.Core.Users.isLoggedIn
  })),
  methods: {
    openURL: function openURL() {
      this.route.openURL;
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('q-layout', {
    attrs: {
      "view": "hHh lpR fFf"
    }
  }, [_vm.isLoggedIn ? [_c('yf-header', [_c('template', {
    slot: "left"
  }, [_c('q-btn', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: !_vm.$q.platform.is.desktop,
      expression: "!$q.platform.is.desktop"
    }],
    attrs: {
      "dense": "",
      "flat": "",
      "round": "",
      "icon": "mdi-menu"
    },
    on: {
      "click": function click($event) {
        _vm.leftDrawerOpen = !_vm.leftDrawerOpen;
      }
    }
  })], 1)], 2), _vm._v(" "), _c('q-drawer', {
    attrs: {
      "content-class": "bg-blue-grey-10 text-white",
      "mini": _vm.miniState ? _vm.miniState : false,
      "width": 200,
      "breakpoint": 500,
      "show-if-above": _vm.miniState
    },
    on: {
      "mouseover": function mouseover($event) {
        _vm.miniState = false && _vm.menuEvents;
      },
      "mouseout": function mouseout($event) {
        _vm.miniState = true && _vm.menuEvents;
      }
    },
    model: {
      value: _vm.leftDrawerOpen,
      callback: function callback($$v) {
        _vm.leftDrawerOpen = $$v;
      },
      expression: "leftDrawerOpen"
    }
  }, [_c('q-toggle', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: _vm.$q.platform.is.desktop,
      expression: "$q.platform.is.desktop"
    }],
    attrs: {
      "true-value": false,
      "false-value": true,
      "icon": "mdi-pin"
    },
    model: {
      value: _vm.menuEvents,
      callback: function callback($$v) {
        _vm.menuEvents = $$v;
      },
      expression: "menuEvents"
    }
  }), _vm._v(" "), _c('left-menu')], 1), _vm._v(" "), _c('yf-footer')] : _vm._e(), _vm._v(" "), _c('q-page-container', [_c('router-view')], 1)], 2);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);