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

import MenuItem from './Items/Item.js';

var moduleName = 'Core.Left.Menu';
var __script__ = {
  name: moduleName,
  components: {
    MenuItem: MenuItem
  },
  data: function data() {
    return {
      userName: 'User Name',
      companyName: 'Company Name'
    };
  },

  computed: {
    items: function items() {
      return this.$store.state.Core.Menu.items;
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('q-list', [_c('q-item', {
    staticClass: "bg-black text-white q-toolbar q-px-md",
    attrs: {
      "header": ""
    }
  }, [_c('q-item-section', {
    attrs: {
      "avatar": ""
    }
  }, [_c('q-icon', {
    attrs: {
      "name": "mdi-account"
    }
  })], 1), _vm._v(" "), _c('q-item-section', [_c('q-item-label', [_vm._v(_vm._s(_vm.userName))])], 1)], 1), _vm._v(" "), _vm._l(_vm.items, function (item) {
    return _c('menu-item', {
      key: item.id,
      attrs: {
        "item": item
      }
    });
  })], 2);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);