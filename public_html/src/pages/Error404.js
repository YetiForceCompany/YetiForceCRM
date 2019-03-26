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
  name: 'Error404'
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', {
    staticClass: "fixed-center text-center"
  }, [_vm._m(0), _vm._v(" "), _c('q-btn', {
    staticStyle: {
      "width": "200px"
    },
    attrs: {
      "color": "secondary"
    },
    on: {
      "click": function click($event) {
        return _vm.$router.push('/');
      }
    }
  }, [_vm._v("Go back")])], 1);
};
var staticRenderFns = [function () {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('p', {
    staticClass: "text-faded"
  }, [_vm._v("\n    Sorry, nothing here...\n    "), _c('strong', [_vm._v("(404)")])]);
}];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);