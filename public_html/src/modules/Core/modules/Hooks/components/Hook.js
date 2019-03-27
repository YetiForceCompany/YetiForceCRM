//
//
//
//
//
//

import getters from '/src/store/getters.js';

var moduleName = 'Core.Hooks.Hook';
var __script__ = {
  name: moduleName,
  props: {
    name: { type: String, required: true }
  },
  computed: {
    fullName: function fullName() {
      return this.$parent.$options.name + '.' + this.name;
    },
    components: function components() {
      return this.$store.getters[getters.Core.Hooks.get](this.fullName);
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', {
    staticClass: "hooks"
  }, _vm._l(_vm.components, function (component) {
    return _c(component, {
      key: component.name,
      tag: "component"
    });
  }), 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);