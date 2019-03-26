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
  name: 'YfBreadcrumbs',
  computed: {
    matched: function matched() {
      return this.$route.matched.filter(function (route) {
        return route.path && route.path.split('/').pop();
      });
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('q-breadcrumbs', {
    class: ['breadcrumbs', ''],
    attrs: {
      "active-color": "info"
    }
  }, [_c('q-breadcrumbs-el', {
    attrs: {
      "icon": "mdi-home",
      "to": '/'
    }
  }), _vm._v(" "), _vm._l(_vm.matched, function (route) {
    return _c('q-breadcrumbs-el', {
      key: route.name,
      attrs: {
        "label": route.path.split('/').pop(),
        "to": route.path
      }
    });
  })], 2);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);