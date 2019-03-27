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

import actions from '/src/store/actions.js';
/**
 * @vue-data     {String} user - form data
 * @vue-data     {String} password - form data
 * @vue-data     {String} language - form data
 * @vue-data     {String} layout - form data
 * @vue-event    {Object} onSubmit - submit form event
 */
var __script__ = {
  name: 'Login',
  data: function data() {
    return {
      user: '',
      password: '',
      language: this.$store.state.Core.Language.defaultLanguage,
      layout: ''
    };
  },

  methods: {
    onSubmit: function onSubmit() {
      this.$refs.user.validate();
      this.$refs.password.validate();
      if (this.$refs.user.hasError || this.$refs.password.hasError) {
        this.formHasError = true;
      } else {
        this.$store.dispatch(actions.Core.Users.login, {
          formData: {
            username: this.user,
            password: this.password,
            fingerPrint: ''
          },
          vm: this
        });
      }
    }
  }
};

var render = function render() {
  var _vm = this;var _h = _vm.$createElement;var _c = _vm._self._c || _h;
  return _c('div', [_c('form', {
    staticClass: "col q-gutter-md q-mx-lg",
    attrs: {
      "autocomplete": _vm.$store.state.Core.Users.loginPageRememberCredentials ? 'on' : 'off'
    },
    on: {
      "submit": function submit($event) {
        $event.preventDefault();
        $event.stopPropagation();
        return _vm.onSubmit($event);
      }
    }
  }, [_c('q-input', {
    ref: "user",
    attrs: {
      "type": "text",
      "label": _vm.$t('LBL_USER'),
      "lazy-rules": "",
      "rules": [function (val) {
        return val && val.length > 0 || 'Please type something';
      }]
    },
    scopedSlots: _vm._u([{
      key: "prepend",
      fn: function fn() {
        return [_c('q-icon', {
          attrs: {
            "name": "mdi-account"
          }
        })];
      },
      proxy: true
    }]),
    model: {
      value: _vm.user,
      callback: function callback($$v) {
        _vm.user = $$v;
      },
      expression: "user"
    }
  }), _vm._v(" "), _c('q-input', {
    ref: "password",
    attrs: {
      "type": "password",
      "label": _vm.$t('Password'),
      "lazy-rules": "",
      "rules": [function (val) {
        return val && val.length > 0 || 'Please type something';
      }]
    },
    scopedSlots: _vm._u([{
      key: "prepend",
      fn: function fn() {
        return [_c('q-icon', {
          attrs: {
            "name": "mdi-lock"
          }
        })];
      },
      proxy: true
    }]),
    model: {
      value: _vm.password,
      callback: function callback($$v) {
        _vm.password = $$v;
      },
      expression: "password"
    }
  }), _vm._v(" "), _vm.$store.state.Core.Users.languageSelection ? _c('q-select', {
    attrs: {
      "options": _vm.$store.state.Core.Language.langs,
      "label": _vm.$t('LBL_CHOOSE_LANGUAGE')
    },
    scopedSlots: _vm._u([{
      key: "prepend",
      fn: function fn() {
        return [_c('q-icon', {
          attrs: {
            "name": "mdi-translate"
          }
        })];
      },
      proxy: true
    }], null, false, 1615289671),
    model: {
      value: _vm.language,
      callback: function callback($$v) {
        _vm.language = $$v;
      },
      expression: "language"
    }
  }) : _vm._e(), _vm._v(" "), _vm.$store.state.Core.Users.layoutSelection ? _c('q-select', {
    attrs: {
      "options": _vm.$store.state.Env.layouts,
      "label": _vm.$t('LBL_SELECT_LAYOUT')
    },
    scopedSlots: _vm._u([{
      key: "prepend",
      fn: function fn() {
        return [_c('q-icon', {
          attrs: {
            "name": "mdi-looks"
          }
        })];
      },
      proxy: true
    }], null, false, 2692984661),
    model: {
      value: _vm.layout,
      callback: function callback($$v) {
        _vm.layout = $$v;
      },
      expression: "layout"
    }
  }) : _vm._e(), _vm._v(" "), _c('q-btn', {
    staticClass: "full-width q-mt-lg",
    attrs: {
      "size": "lg",
      "label": _vm.$t('LBL_SIGN_IN'),
      "type": "submit",
      "color": "secondary"
    }
  }), _vm._v(" "), _vm.$store.state.Core.Users.forgotPassword ? _c('router-link', {
    staticClass: "text-secondary float-right",
    attrs: {
      "to": {
        name: 'Reminder'
      }
    }
  }, [_vm._v(_vm._s(_vm.$t('ForgotPassword')))]) : _vm._e()], 1)]);
};
var staticRenderFns = [];
var __template__ = { render: render, staticRenderFns: staticRenderFns };

export default Object.assign({}, __script__, __template__);