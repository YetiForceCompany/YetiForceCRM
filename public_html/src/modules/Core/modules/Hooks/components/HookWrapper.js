//
//
//
//
//
//
//
//

import getters from '/src/store/getters.js';

var moduleName = 'Core.Hooks.HookWrapper';
var __script__ = {
  name: moduleName,
  props: { name: { type: String, required: false, default: 'default' } },
  computed: {
    fullName: function fullName() {
      return this.$parent.$options.name + '.' + (this.name !== 'default' ? this.name + '.' : '');
    },
    componentsBefore: function componentsBefore() {
      return this.$store.getters[getters.Core.Hooks.get](this.fullName + 'before');
    },
    componentsAfter: function componentsAfter() {
      return this.$store.getters[getters.Core.Hooks.get](this.fullName + 'after');
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', {
    staticClass: "Core-Hooks-HookWrapper"
  }, [_vm._l(_vm.componentsBefore, function (component) {
    return _c(component, {
      key: component.name,
      tag: "component"
    });
  }), _vm._v(" "), _vm._t("default"), _vm._v(" "), _vm._l(_vm.componentsAfter, function (component) {
    return _c(component, {
      key: component.name,
      tag: "component"
    });
  })], 2);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);