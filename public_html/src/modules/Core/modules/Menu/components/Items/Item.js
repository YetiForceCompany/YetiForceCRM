//
//
//
//

import RoutePush from './RoutePush.js';
import Expander from './Expander.js';

var moduleName = 'Core.Menu.Items.Item';
var __script__ = {
  name: moduleName,
  components: {
    RoutePush: RoutePush,
    Expander: Expander
  },
  props: ['item'],
  computed: {
    component: function component() {
      if (this.item.children.length) {
        return 'Expander';
      } else {
        return 'RoutePush';
      }
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c(_vm.component, {
    tag: "component",
    attrs: {
      "icon": _vm.item.icon,
      "label": _vm.item.label,
      "path": _vm.item.path,
      "children": _vm.item.children
    }
  });
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);