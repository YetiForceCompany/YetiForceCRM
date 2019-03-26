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
  return _c('q-list', _vm._l(_vm.items, function (item) {
    return _c('menu-item', {
      key: item.id,
      attrs: {
        "item": item
      }
    });
  }), 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);