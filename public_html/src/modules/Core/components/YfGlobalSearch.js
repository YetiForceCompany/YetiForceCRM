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

var __script__ = {
  name: 'YfGlobalSearch',
  data: function data() {
    return {
      searchModule: 'All records',
      searchModules: ['All records', 'acc', 'con'],
      searchText: '',
      iconSize: '.75rem'
    };
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('hook-wrapper', {
    staticClass: "Core-Component-YfGlobalSearch"
  }, [_c('div', [_c('q-btn', {
    staticClass: "lt-md",
    attrs: {
      "round": "",
      "size": _vm.iconSize,
      "flat": "",
      "icon": "mdi-magnify",
      "dense": ""
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "flex gt-sm"
  }, [_c('div', {
    staticClass: "q-pl-sm headerField",
    staticStyle: {
      "min-width": "200px"
    }
  }, [_c('q-select', {
    attrs: {
      "options": _vm.searchModules,
      "placeholder": "Placeholder",
      "dense": ""
    },
    model: {
      value: _vm.searchModule,
      callback: function callback($$v) {
        _vm.searchModule = $$v;
      },
      expression: "searchModule"
    }
  })], 1), _vm._v(" "), _c('q-input', {
    staticClass: "q-pl-lg headerField",
    staticStyle: {
      "min-width": "200px"
    },
    attrs: {
      "placeholder": "Placeholder",
      "dense": ""
    },
    scopedSlots: _vm._u([{
      key: "after",
      fn: function fn() {
        return [_c('q-btn', {
          attrs: {
            "round": "",
            "size": _vm.iconSize,
            "flat": "",
            "icon": "mdi-magnify",
            "dense": ""
          }
        }), _vm._v(" "), _c('q-btn', {
          attrs: {
            "round": "",
            "size": _vm.iconSize,
            "flat": "",
            "icon": "mdi-format-text",
            "dense": ""
          }
        }), _vm._v(" "), _c('q-btn', {
          attrs: {
            "round": "",
            "size": _vm.iconSize,
            "flat": "",
            "icon": "mdi-feature-search-outline",
            "dense": ""
          }
        })];
      },
      proxy: true
    }]),
    model: {
      value: _vm.searchText,
      callback: function callback($$v) {
        _vm.searchText = $$v;
      },
      expression: "searchText"
    }
  })], 1)], 1)]);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);