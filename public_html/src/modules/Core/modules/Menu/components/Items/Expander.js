//
//
//
//
//
//

var moduleName = 'Core.Menu.Items.Expander';
var __script__ = {
  name: moduleName,
  components: {
    MenuItem: function MenuItem() {
      return import('./Item.js');
    }
  },
  props: ['icon', 'label', 'children']
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('q-expansion-item', {
    attrs: {
      "icon": _vm.icon,
      "label": _vm.label,
      "content-inset-level": 0.5
    }
  }, _vm._l(_vm.children, function (child) {
    return _c('menu-item', {
      key: child.id,
      attrs: {
        "item": child
      }
    });
  }), 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);