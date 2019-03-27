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

var moduleName = 'Core.Menu.Items.RoutePush';
var __script__ = {
  name: moduleName,
  props: ['path', 'icon', 'label']
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('q-item', {
    attrs: {
      "clickable": ""
    },
    on: {
      "click": function click($event) {
        return _vm.$router.push(_vm.path);
      }
    }
  }, [_c('q-item-section', {
    attrs: {
      "avatar": ""
    }
  }, [_c('q-icon', {
    attrs: {
      "name": _vm.icon
    }
  })], 1), _vm._v(" "), _c('q-item-section', [_c('q-item-label', [_vm._v(_vm._s(_vm.label))])], 1)], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);