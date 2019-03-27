//
//
//
//
//
//

var moduleName = 'App';

var __script__ = {
  name: moduleName,
  provide: function provide() {
    var provider = {};
    var self = this;
    Object.defineProperty(provider, 'App', {
      enumerable: true,
      get: function get() {
        return self;
      }
    });
    return provider;
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', {
    attrs: {
      "id": "app"
    }
  }, [_c('router-view')], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);