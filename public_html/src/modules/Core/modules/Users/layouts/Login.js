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

import getters from '/src/store/getters.js';
/**
 * @vue-data     {String}    activeComponent - component name
 * @vue-data     {Boolean}   showReminderForm - form data
 * @vue-data     {Boolean}   showLoginForm - form data
 * @vue-computed {Object}    env - env variables
 * @vue-event    {Object}    openURL
 */
var moduleName = 'Core.Users.Layouts.Login';
var __script__ = {
  name: moduleName,
  data: function data() {
    return {
      showReminderForm: false,
      showLoginForm: true
    };
  },

  computed: Object.assign({}, Vuex.mapGetters({
    env: getters.Core.Env.all
  })),
  methods: {
    openURL: openURL
  },
  beforeRouteEnter: function beforeRouteEnter(to, from, next) {
    next(function (vm) {
      if (vm.$store.getters[getters.Core.Users.isLoggedIn]) {
        next('/');
      } else {
        next();
      }
    });
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('q-layout', [_c('q-page-container', [_c('q-page', {
    staticClass: "row"
  }, [_c('div', {
    staticClass: "col-xs-12 col-sm-6 col-md-4 col-lg-3 fixed-center"
  }, [_c('div', {
    staticClass: "card-shadow q-pa-xl column"
  }, [_c('div', {
    staticClass: "col-auto self-center q-pb-lg"
  }, [_c('img', {
    attrs: {
      "src": _vm.env.publicDir + '/statics/Logo/logo'
    }
  })]), _vm._v(" "), _c('keep-alive', [_c('router-view')], 1)], 1)])])], 1)], 1);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);